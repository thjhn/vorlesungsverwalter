<?php
/*
 *  Copyright (c) 2016, Thomas Jahn <vv3@t-und-j.de>
 *
 *  This file is part of VV3.
 *
 *  VV3 is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  VV3 is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with VV3.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  ----
 *  In this file we implement the server's interface to the user's requests.
 *  Any request is send by a users using this file.
 */

// We need to include some other files...
include_once('server/dataset/dataset.php');
include_once('server/authentication/authentication.php');
include_once('server/users/users.php');
include_once('server/groups/groups.php');
include_once('server/exams/exams.php');
include_once('server/tools/tool.php');
include_once('server/student/student.php');

include_once('server/sheet/sheet.php');
include_once('server/logger/logger.php');

// Firstly, we try to authenticate the user. Therefore we need to start the session and create the Authentication object.
session_start();
$AUTH = new Authentication();

// Secondly, we decode the user's request.
$cmd = $_POST['cmd'];
$data = $_POST['data'];
switch($cmd){

	///////////////////////////////////////////////////////////////
	// Log In.
	// Try to log in the user with the given username and password.
	// Note that this command does not (yet) use the data variable.
	//
	// Roles required: none
	case 'LOGIN':
		print($AUTH->login($_POST['username'],$_POST['password']));
		break;


	///////////////////////////////////////////////////////////////
	// Get the user's login status.
	// Returns a JSON object that inter alia contains the field
	// 'status' which is either 'in' or 'out'.
	// If the user is loged in (i.e. status==in), then also her
	// username and his realname are contained.
	// Note that this command does not (yet) use the data variable.
	//
	// Roles required: none
	case 'GET_LOGIN':
		echo $AUTH->loginstate();
		break;


	///////////////////////////////////////////////////////////////
	// Log Out.
	// Log the current user out.
	//
	// Roles required: none
	case 'LOGOUT':
		$AUTH->logout();
		break;


	///////////////////////////////////////////////////////////////
	// Change Password.
	// The password of the currently loged in user is set to
	// data->newpassword. To verify that the real user sent this
	// request, also the current password has to be sent in
	// data->oldpassword.
	// To not thwart this verification the GUI should ask the user
	// for her old password and not user a somewhere stored copy.
	//
	// Roles required: none
	case 'CHANGE_PASSWORD':
		Logger::log("Interface got 'CHANGE_PASSWORD' for current user.",Logger::LOGLEVEL_VERBOSE);
		$rt = $AUTH->change_password($data['oldpassword'],$data['newpassword']);
		if(count($rt)>0){
			print("{\"success\":\"no\"}");
		}else{
			print("{\"success\":\"yes\"}");
		}
		break;


	///////////////////////////////////////////////////////////////
	// Get basic information about the lecture.
	// Returns a JSON object containing the lectures title, term
	// and lecturer.
	//
	// Roles required: none
	case 'GET_BASIC_INFO':
		$conf = new Dataset("config",false);
		if(!$conf->isLoaded()){
			Logger::log("Interface could not handle 'GET_BASIC_INFO': Config not available.",Logger::LOGLEVEL_ERROR);
			echo "{\"success\":\"no\",\"errmsg\":\"Could not load server configuration.\"}";
		}else{
			$lecture = $conf->dom->getElementsByTagName("lecture")->item(0)->nodeValue;
			$term = $conf->dom->getElementsByTagName("term")->item(0)->nodeValue;
			$lecturer = $conf->dom->getElementsByTagName("lecturer")->item(0)->nodeValue;
			echo "{\"success\":\"yes\",\"lecture\":\"$lecture\", \"term\":\"$term\", \"lecturer\":\"$lecturer\"}";
		}
		break;


	///////////////////////////////////////////////////////////////
	// Get a list of all slots for registration.
	// Returns a JSON object containing an array of all slots.
	// Each item contains the fields id, start and end.
	//
	// Roles required: none
	case 'LIST_REGISTRATIONSLOTS':
		// get a list of all slots
		$conf = new Dataset("config",false);
		if(!$conf->isLoaded()){
			Logger::log("Interface could not handle 'LIST_REGISTRATIONSLOT': Config not available.",Logger::LOGLEVEL_ERROR);
			echo "{\"success\":\"no\",\"errmsg\":\"Could not load server configuration.\"}";
		}else{
			$ret = array();
			$registrationslots = $conf->dom->getElementsByTagName("registrationslot");
			foreach($registrationslots as $slot){
				$slotitem = array();
				$slotitem['id'] = $slot->getAttribute("id");
				$slotitem['start'] = $slot->getAttribute("start");
				$slotitem['end'] = $slot->getAttribute("end");
				$ret[] = $slotitem;
			}
			print(json_encode(array('success'=>'yes','slots'=>$ret)));
		}
		break;


	///////////////////////////////////////////////////////////////
	// Edit an registration slot.
	// The slot with id data->id should be changed. Its starttime
	// is set to data->start, its endtime to data->end.
	// 
	// Roles required: admin
	case 'EDIT_REGISTRATIONSLOT':
		Logger::log("Interface got 'EDIT_REGISTRATIONSLOT'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'EDIT_REGISTRATIONSLOT' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}

		// check data integrity.
		if(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $data['start'])==0){
			Logger::log("While handling 'EDIT_REGISTRATIONSLOT': ".$data['start']." is invalid.",Logger::LOGLEVEL_WARNING);
			print("{\"success\":\"no\",\"errormsg\":\"Invalid data.\"}");
			break;
		}if(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $data['end'])==0){
			Logger::log("While handling 'EDIT_REGISTRATIONSLOT': ".$data['end']." is invalid.",Logger::LOGLEVEL_WARNING);
			print("{\"success\":\"no\",\"errormsg\":\"Invalid data.\"}");
			break;
		}

		$conf = new Dataset("config",true);
		if(!$conf->isLoaded()){
			Logger::log("Interface could not handle 'EDIT_REGISTRATIONSLOT': Config not available.",Logger::LOGLEVEL_ERROR);
			echo "{\"success\":\"no\",\"errmsg\":\"Could not load server configuration.\"}";
			break;
		}

		if($data['slotid'] == "_new"){
			$newNode = $conf->dom->createElement('registrationslot');
			$newNode->setAttribute("id",uniqid(true));
			$newNode->setAttribute("start",$data['start']);
			$newNode->setAttribute("end",$data['end']);
			$conf->dom->getElementsByTagName("registrationslots")->item(0)->appendChild($newNode);
			$conf->save();
			$matches = 1;
		}else{
			$registrationslots = $conf->dom->getElementsByTagName("registrationslot");
			$matches = 0;
			foreach($registrationslots as $slot){
				if($slot->getAttribute("id") == $data['slotid']){
					$slot->setAttribute("start",$data['start']);
					$slot->setAttribute("end",$data['end']);
					$conf->save();
					$matches++;
					break;
				}
			}
		}

		if($matches>0){
			print("{\"success\":\"yes\"}");
		}else{
			print("{\"success\":\"no\",\"errormsg\":\"Slot not found.\"}");
		}

		break;


	///////////////////////////////////////////////////////////////
	// Get a JSON list of all groups.
	// 
	// Roles required: none
	case 'LIST_ALL_GROUPS':
		print(Groups::getAllGroupsJson());
		break;


	///////////////////////////////////////////////////////////////
	// Get a JSON list of all exams.
	// 
	// Roles required: none
	case 'GET_ALL_EXAMS':
		Logger::log("Interface got 'GET_ALL_EXAMS'.",Logger::LOGLEVEL_VERBOSE);
		print(Exams::getAllExamsJson());
		break;

	///////////////////////////////////////////////////////////////
	// Get a JSON with the data of the exam whose id is given
	// as data.
	// 
	// Roles required: corrector, admin
	case 'GET_EXAM':
		Logger::log("Interface got 'GET_EXAM'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("corrector") && !$AUTH->hasRole("admin")){
			Logger::log("Interface got 'GET_EXAM' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}

		$exam = new Exams($data);				
		print($exam->getDataJson());
		break;

	///////////////////////////////////////////////////////////////
	// Edit a specified exam.
	// The exam and the new data are given in the data object.
	// If the given exam-id is _new we actually create a new
	// exam (and create our own exam-id).
	// If a new exam is added the $data field 'problems' indicates
	// how many problems the exam will contain. This field cannot be
	// changed afterwards. If it is provided for an existing exam
	// it will be ignored.
	// 
	// Roles required: admin
	case 'EDIT_EXAM':
		Logger::log("Interface got 'EDIT_EXAM'.",Logger::LOGLEVEL_VERBOSE);
		if($AUTH->hasRole("admin")){
			// ex ante we assume that the following operations will be successfull
			$success = true;
			$errormsg = "";
			// if the examid equals _new, we are asked to add a new group.
			if($data['exam'] == "_new"){
				$newid = Exams::addExam($data["problems"]);
				$exam = new Exams($newid,true);
			}else{
				$exam = new Exams($data['exam'],true);
			}
			// now, (try to) save changes
			$exam->setName($data["name"]);
			$exam->setEnabled($data["registration"]);
			$exam->setEnterscores($data["enterscores"]);
			

			// now its time to return sth.
			if( $exam->save() ){
				print("{\"success\":\"yes\"}");
			}else{
				print("{\"success\":\"no\",\"errormsg\":\"".$errormsg."\"}");
			}
		}else{
			Logger::log("Interface got 'EDIT_EXAM' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler. Keine Eintragungen wurden gespeichert!\"}");
		}
		break;



	///////////////////////////////////////////////////////////////
	// Register a student to a certain exam.
	// Admins can register users at any time. Anybody else is only
	// allowed to call this command at certain times.
	// Currently, only admins can call this command!
	// 
	// Roles required: admin
	case 'REGISTER_STUDENT_TO_EXAM':
		Logger::log("Interface got 'REGISTER_STUDENT_TO_EXAM'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'REGISTER_STUDENT_TO_EXAM' from a not admin. But currently registration is not allowed.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Interner Fehler.\"}");
			break;
		}

		// Check validity of input
		if(preg_match('/^[0-9a-zA-Z]+$/', $data['exam'])==0){
			Logger::log("Interface got 'REGISTER_STUDENT_TO_EXAM' got an invalid exam id ".$data['exam'].".",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Interner Fehler.\"}");
			break;
		}
		if(preg_match('/^[0-9a-zA-Z]+$/', $data['student'])==0){				
			Logger::log("Interface got 'REGISTER_STUDENT_TO_EXAM' got an invalid student id ".$data['exam'].".",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Interner Fehler.\"}");
			break;
		}

		$exam = new Exams($data['exam'],true);
		if(!$exam->isLoaded()){
			Logger::log("Interface got 'REGISTER_STUDENT_TO_EXAM' but could not load exam with id ".$data['exam'].".",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Interner Fehler.\"}");
			break;
		}

		print($exam->registerStudent($data['student']));
		break;



	///////////////////////////////////////////////////////////////
	// Add a new student to the database.
	// The following fields are required: familyname, givenname,
	// matrnr, term, email, course, ingroup
	// 
	// Roles required: none
	// Note: a user with role admin is allowed to perform this
	// request even if there is no open registration slot.
	case 'REGISTER_NEW_STUDENT':
		Logger::log("Interface got 'REGISTER_NEW_STUDENT'.",Logger::LOGLEVEL_VERBOSE);
		// Check whether a registrations is currently possible.
		$conf = new Dataset("config",false);
		if(!$conf->isLoaded()){
			Logger::log("Interface could not handle 'REGISTER_NEW_STUDENT': Config not available.",Logger::LOGLEVEL_ERROR);
			echo "{\"success\":\"no\",\"errmsg\":\"Could not load server configuration.\"}";
			break;
		}
		$registrationslots = $conf->dom->getElementsByTagName("registrationslot");
		$matches = 0;
		foreach($registrationslots as $slot){
			$now = date("Y-m-d\TH:i:s");
			$start = $slot->getAttribute("start");
			$end = $slot->getAttribute("end");
			if($now >= $start && $now <= $end){
				$matches++;
				break;
			}
		}
		if($AUTH->hasRole("admin")){
			// if the user is an admin, allow registration any time!
			$matches++;
		}
		if($matches == 0){
			// registration is currently not allowed.
			Logger::log("interface.php of tool register recieved a REGISTER command from IP ".$_SERVER['REMOTE_ADDR']." while registration was not allowed.",Logger::LOGLEVEL_WARNING);
			print("{\"success\":\"no\",\"errormsg\":\"Die Anmeldung ist gerade nicht freigeschalten.\"}");
			break;
		}

		// registration is currently allowed
		$data = json_decode($data);
		$studReturn = Student::addStudent($data->familyname,$data->givenname,$data->matrnr,$data->term,$data->email,$data->course,$data->ingroup);
		// if $studReturn is an array, an error occured:
		$toReturn = array();

		if($studReturn===false){
			$toReturn['success'] = "no";
			$toReturn['failures'] = "CryptoError";
		}else{
			if(is_array($studReturn)){
				$toReturn['success'] = "no";
				$toReturn['failures'] = $studReturn;
			}else{
				$toReturn['success'] = "yes";
			}
		}
		print(json_encode($toReturn));
		break;


	///////////////////////////////////////////////////////////////
	// Get a list of the family- and givennames of all students.
	// 
	// Roles required: corrector, admin
	case 'LIST_ALL_STUDENTS_FG':
		Logger::log("Interface got 'LIST_ALL_STUDENTS_FG'.",Logger::LOGLEVEL_VERBOSE);
		if($AUTH->hasRole("corrector") || $AUTH->hasRole("admin")){
			print(Student::findStudentsJson("FG",$data,$AUTH));
		}else{
			Logger::log("Interface got 'LIST_ALL_STUDENTS_FG' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Zugriff verweigert.\"}");
		}
		break;


	///////////////////////////////////////////////////////////////
	// Get a list of the family- and givennames and matrnrs of all
	// students.
	// 
	// Roles required: admin
	case 'LIST_ALL_STUDENTS_FGM':
		Logger::log("Interface got 'LIST_ALL_STUDENTS_FGM'.",Logger::LOGLEVEL_VERBOSE);
		if($AUTH->hasRole("admin")){
			print(Student::findStudentsJson("FGM",$data,$AUTH));
		}else{
			Logger::log("Interface got 'LIST_ALL_STUDENTS_FGM'. But user was not allowed to call it.",Logger::LOGLEVEL_WARNING);
			print("{\"success\":\"no\",\"errormsg\":\"Zugriff verweigert.\"}");
		}
		break;


	///////////////////////////////////////////////////////////////
	// Add a new score entry.
	// The following fields are required: sheet, student, score
	// 
	// Roles required: corrector
	case 'ENTER_SCORE':
		// user wants to enter some scores
		Logger::log("Interface got 'ENTER_SCORE'.",Logger::LOGLEVEL_VERBOSE);
		if($AUTH->hasRole("corrector")){
			$theData = json_decode($data,true);
			$username = $AUTH->getUsername();
			if($username === FALSE){
				Logger::log("interface.php of tool enterscores could not get username while handling ENTER_SCORE.",Logger::LOGLEVEL_ERROR);
				print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler. Keine Eintragungen wurden gespeichert!\"}");
				break;
			}else{
				print(Sheet::setScore($AUTH,$theData["sheet"],$theData["student"],$theData["score"],$username));
				break;
			}
		}else{
			Logger::log("Interface got 'ENTER_SCORE' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler. Keine Eintragungen wurden gespeichert!\"}");
		}
		break;



	///////////////////////////////////////////////////////////////
	// Add a new score entry to an exam
	// The following fields are required: exam, student, scores, overwrite
	// 
	// Roles required: admin
	case 'ENTER_SCORE_EXAM':
		// user wants to enter some scores
		Logger::log("Interface got 'ENTER_SCORE_EXAM'.",Logger::LOGLEVEL_VERBOSE);
		if($AUTH->hasRole("admin")){
			$theData = json_decode($data,true);
			$exam = new Exams($theData['exam'], true);
			if(!$exam->isLoaded()){
				Logger::log("Interface got 'ENTER_SCORE_EXAM' but could not load exam ".$theData['exam'].".",Logger::LOGLEVEL_ERROR);
				print("{\"success\":\"no\",\"errormsg\":\"Interner Fehler. Keine Eintragungen wurden gespeichert!\"}");
				break;
			}
			$failures = false;
			for($i = 0; $i<count($theData["scores"]); $i++){
				if( preg_match('/^[0-9]+(\.[0-9]+)?$/', $theData["scores"][$i])==0 ){
					$failures = true;
				}
			}
			if($failures){
				Logger::log("A score entry was not well formated while handling command ENTER_SCORE_EXAM.",Logger::LOGLEVEL_VERBOSE);
				print("{\"success\":\"no\",\"errormsg\":\"Eine Punktezahl ist nicht korrekt formatiert!\"}");
				break;
			}
			
			if($theData["overwrite"] == "true"){
				$theData["overwrite"] = true;
			}else{
				$theData["overwrite"] = false;
			}

			print($exam->setScore($AUTH,$theData["student"],$theData["scores"],$theData["overwrite"]));

		}else{
			Logger::log("Interface got 'ENTER_SCORE_EXAM' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler. Keine Eintragungen wurden gespeichert!\"}");
		}
		break;


	///////////////////////////////////////////////////////////////
	// Get the scores of a student in a given exam.
	// The following data fields are required: exam, student
	// 
	// Roles required: admin
	case 'GET_SCORE_EXAM':
		// user wants to enter some scores
		Logger::log("Interface got 'GET_SCORE_EXAM'.",Logger::LOGLEVEL_VERBOSE);
		if($AUTH->hasRole("admin")){
			$exam = new Exams($data['exam'], false);
			if(!$exam->isLoaded()){
				Logger::log("Interface got 'GET_SCORE_EXAM' but could not load exam ".$data['exam'].".",Logger::LOGLEVEL_ERROR);
				print("{\"success\":\"no\",\"errormsg\":\"Interner Fehler.\"}");
				break;
			}
			print($exam->getStudentScores($AUTH,$data["student"]));
		}else{
			Logger::log("Interface got 'GET_SCORE_EXAM' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
		}
		break;


	///////////////////////////////////////////////////////////////
	// Get information about a group.
	// $data contains the id of the group in question.
	// 
	// Roles required: none
	case 'GET_GROUP_JSON':
		Logger::log("Interface got 'GET_GROUP_JSON'.",Logger::LOGLEVEL_VERBOSE);
		$curGroup = new Groups($data);
		print($curGroup->getGroupJson());
		break;


	///////////////////////////////////////////////////////////////
	// Edit a specific group.
	// If groupid equals _new a new group is generated.
	// 
	// data must be a JSON of the following format:
	//   {"groupid":<groupid>, "name":<name>,
	//    "description":<description>, "seats":<seats>}
	// where
	//   <groupid> is the id of the group
	//   <name> is the groups's name
	//   <description> is a description of this group
	//   <seats> is the number of seats in this group
	//
	// Roles required: admin
	case 'EDIT_GROUP':
		Logger::log("Interface got 'EDIT_GROUP'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'EDIT_USER' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}

		if($data['groupid'] == "_new"){
			Groups::addGroup($data['name'], $data['description'], $data['seats']);
			print("{\"success\":\"yes\"}");
			break;
		}

		// load user's current data
		$group = new Groups($data['groupid'],true);
		if( !$group->loaded() ){ // check whether data was loaded
			Logger::log("Group ".$data['groupid']." not found. Interface could not handle 'EDIT_GROUP'.".Logger::LOGLEVEL_WARNING);
			print("{\"success\":\"no\",\"errormsg\":\"Gruppe existiert nicht.\"}");
			break;
		}

		$group->setName($data['name']);
		$group->setDescription($data['description']);
		$group->setSeats($data['seats']);		

		// now its time to return sth.
		if( $group->save() ){
			print("{\"success\":\"yes\"}");
		}else{
			print("{\"success\":\"no\",\"errormsg\":\"".$errormsg."\"}");
		}
		break;

	
	///////////////////////////////////////////////////////////////
	// Get statistics on scores.
	// 
	// data must be a JSON of the following format:
	//   {"sheet":<sheet>}
	// where
	//   <sheet> is the sheet we want the statistics for.
	//	if == 0 we return the global statistic.
	//
	// Roles required: admin
	case 'GET_SCORESTATS':
		Logger::log("Interface got 'GET_SCORESTATS'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'GET_SCORESTATS' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		$scoreStat = Sheet::getAllScoresStat($AUTH);
		if($scoreStat === False){
			Logger::log("Interface got 'GET_SCORESTATS' but getAllScoreStat() returned false.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}

		print("{\"success\":\"yes\",\"stat\":".json_encode($scoreStat[$data['sheet']])."}");
		
		break;


	///////////////////////////////////////////////////////////////
	// Get statistics on sheets.
	// 
	// data must be a JSON of the following format:         *
	//   {"sheet":<sheet>}                                  *
	// where                                                **
	//   <sheet> is the sheet we want the statistics for.   *
	//	if == 0 we return the global statistic.         *
	//
	// Roles required: admin
	case 'GET_SHEETSTATS':
		Logger::log("Interface got 'GET_SHEETSTATS'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'GET_SHEETSTATS' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		$scoreStat = Sheet::getAllScoresStat($AUTH);
		if($scoreStat === False){
			Logger::log("Interface got 'GET_SCORESTATS' but getAllScoreStat() returned false.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}

		$stat = [];
		for($i = 0; $i < count($scoreStat); $i++){
			foreach(array_keys($scoreStat[$i]) as $groupkey){
				$stat[$i][$groupkey]['sum'] = $scoreStat[$i][$groupkey]['sum'];
				$stat[$i][$groupkey]['sqsum'] = $scoreStat[$i][$groupkey]['sqsum'];
				$stat[$i][$groupkey]['count'] = $scoreStat[$i][$groupkey]['count'];
				$stat[$i][$groupkey]['max'] = $scoreStat[$i][$groupkey]['max'];
				$stat[$i][$groupkey]['min'] = $scoreStat[$i][$groupkey]['min'];
			}			
		}

		print("{\"success\":\"yes\",\"stat\":".json_encode($stat)."}");
		
		break;


	///////////////////////////////////////////////////////////////
	// Get a JSON of the scores of all students.
	//
	// Roles required: admin
	case 'LIST_ALL_STUDENTS_SCORES':
		Logger::log("Interface got 'LIST_ALL_STUDENTS_SCORES'.",Logger::LOGLEVEL_VERBOSE);
		if($AUTH->hasRole("corrector")){
			$studentlist = Student::getAllStudents($AUTH);
			$scorelist = Sheet::getAllScores($AUTH);
			if($AUTH->hasRole("admin")) $examlist = Exams::getAllScores($AUTH);
			$list = array();
			for($i = 0; $i<count($studentlist); $i++){
				$item = array();
				$item['familyname'] = $studentlist[$i]['familyname'];
				$item['givenname'] = $studentlist[$i]['givenname'];
				if($AUTH->hasRole("admin")) $item['matrnr'] = $studentlist[$i]['matrnr'];
				$item['scores'] = $scorelist[ $studentlist[$i]['id'] ]['scores'];
				if($AUTH->hasRole("admin"))  $item['exams'] = $examlist[ $studentlist[$i]['id'] ];
				$list[ $studentlist[$i]['id'] ] = $item;
			}
			print(json_encode($list));
		}else{
			Logger::log("Interface got 'LIST_ALL_STUDENTS_SCORES' with but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
		}
		break;


	///////////////////////////////////////////////////////////////
	// Get a JSON of the scores of all students of corrector
	// the currently logged in.
	//
	// Roles required: corrector
	case 'LIST_ALL_CORRECTORS_SCORES':
		Logger::log("Interface got 'LIST_ALL_CORRECTORS_SCORES'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("corrector")){
			Logger::log("Interface got 'LIST_ALL_CORRECTORS_SCORES' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		$username = $AUTH->getUsername($AUTH);
		if($username === FALSE){
			Logger::log("i.php could not get username while handling LIST_ALL_CORRECTORS_SCORES.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}else{
			$ret = Sheet::getScores($username,$AUTH);
			if($ret === false){
				return "{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}";
			}
			print($ret);
			break;
		}



	///////////////////////////////////////////////////////////////
	// Edit a single score.
	// 
	//
	// Roles required: corrector
	case 'EDIT_SCORE':
		// user wants to edit some scores
		Logger::log("Interface got 'EDIT_SCORE'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("corrector")){
			Logger::log("Interface got 'EDIT_SCORE' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}

		$sheet = new Sheet($data['sid'],true);
		$corrector = $sheet->getCorrector();

		// check whether the user is allowed to do this
		$username = $AUTH->getUsername();

		if($corrector == $username){
			// We check whether the format of $score is valid.
			// Actually, this is also done within the updateScore function but we want to explicitely know whether this error occurred!
			if( preg_match('/^[0-9]+(\.[0-9]+)?$/', $data['newscore'])==0 ){
				print("{\"success\":\"no\",\"errormsg\":\"Die Punkteangabe hat nicht das korrekte Format.\"}");
				break;
			}
			if($sheet->updateScore($data['newscore'],$AUTH)){
				print("{\"success\":\"yes\"}");
			}else{
				Logger::log("While handling 'EDIT_SCORE': username $username does not match corrector $corrector on sheet ".$data['sid'].".",Logger::LOGLEVEL_WARNING);
				print("{\"success\":\"no\",\"errormsg\":\"Es war nicht möglich, die Änderung zu speichern!\"}");
			}

		}else{
			print("{\"success\":\"no\",\"errormsg\":\"Es war nicht möglich, die Änderung zu speichern! Es liegt dazu keine Berechtigung vor.\"}");
		}

		break;


	///////////////////////////////////////////////////////////////
	// Get a list of all students with some infos about them.
	// 
	//
	// Roles required: admin
	case 'LIST_OF_ALL_STUDENTS_WITH_INFO':
		Logger::log("Interface got 'LIST_OF_ALL_STUDENTS_WITH_INFO'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'LIST_OF_ALL_STUDENTS_WITH_INFO' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		print(json_encode(Student::getAllStudents($AUTH)));
		break;



	///////////////////////////////////////////////////////////////
	// Get a json with info about a specific student.
	// The ID is given as data.
	// 
	//
	// Roles required: admin
	case 'GET_STUDENT':
		// user wants information about a specific student
		Logger::log("Interface got 'GET_STUDENT' with data $data.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'GET_STUDENT' with data $data but the user was not allowed to call this 	command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		$student = new Student($data);				
		print($student->getDataJson($AUTH));
		break;


	////////////////////////////////////////////////////
///////////
	// Get a json with the scores of a specific student.
	// The ID is given as data.
	// 
	//
	// Roles required: admin
	case 'GET_A_STUDENTS_SCORES':
		// Get the scores of a specific student.
		Logger::log("Interface got 'GET_A_STUDENTS_SCORES' with data $data.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'GET_A_STUDENTS_SCORES' with data $data but the user was not allowed to call this 	command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		// returns the scores of a single student
		print(Sheet::getScoreByStudent($data,$AUTH));
		break;



	///////////////////////////////////////////////////////////////
	// Edit a specific student.
	// 
	// data must be a JSON of the following format:
	//   {"uid":<id>, "personal":<personal>,
	//    "description":<description>, "seats":<seats>}
	// where
	//   <uid> is the student's id
	//   <personal> is a JSON with the fields
	//     course, email, familyname, givenname, ingroup, matrnr,
	//     term
	//
	// Roles required: admin
	case 'EDIT_STUDENT':
		// Edit data of a specific student
		Logger::log("Interface got 'EDIT_STUDENT' with data $data.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'EDIT_STUDENT' with data $data but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		// ex ante we assume that the following operations will be successfull
		$success = true;
		$errormsg = "";

		// first, change the scores
		$scoreFailures = Sheet::changeScoreByList($data['uid'],$data['newscores'],$AUTH);

		if($scoreFailures<0){
			// at least one score change failed
			$success = false;
			$errormsg .= "Mindestens eine Punktkorrektur konnte nicht gespeichert werden. ";
		}

		// second, change personal values
		$stud = new Student($data['uid'],true);
		$fails = $stud->editStudent($data["personal"]["familyname"], $data["personal"]["givenname"], $data["personal"]["matrnr"], $data["personal"]["term"], $data["personal"]["course"], $data["personal"]["email"], $data["personal"]["ingroup"] );
		if( count($fails) > 0 ){
			// at least one change failed
			$success = false;
			if(in_array("DUPLICATE",$fails)) $errormsg .= "Es existiert bereits ein Student mit diesem Namen. ";
			if(in_array("familyname",$fails)) $errormsg .= "Der Name ist nicht valide. ";
			if(in_array("givenname",$fails)) $errormsg .= "Der Vorname ist nicht valide. ";
			if(in_array("matrnr",$fails)) $errormsg .= "Die Matrikelnummer ist nicht valide. ";
			if(in_array("email",$fails)) $errormsg .= "Die Emailadresse ist nicht valide. ";
			if(in_array("term",$fails)) $errormsg .= "Das Fachsemester ist nicht valide. ";
			if(in_array("course",$fails)) $errormsg .= "Der Studiengang ist nicht valide. ";
			if(in_array("ingroup",$fails)) $errormsg .= "Das Tutorium wurde nicht gefunden. ";
			if(in_array("INTERNAL",$fails)) $errormsg .= "Es ist ein interner Fehler aufgetreten. ";
		}

		// now its time to return sth.
		if( $success ){
			print("{\"success\":\"yes\"}");
		}else{
			print("{\"success\":\"no\",\"errormsg\":\"".$errormsg."\"}");
		}
		break;


	///////////////////////////////////////////////////////////////
	// Get information on all users.
	// 
	// Roles required: admin
	case 'LIST_USERS':
		// Get a list of all users
		Logger::log("Interface got 'LIST_USERS'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'LIST_USERS' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		print(Users::getAllUsersJson());
		break;

	///////////////////////////////////////////////////////////////
	// Get information on a single users.
	// data contains the id.
	// 
	// Roles required: admin
	case 'GET_USER':
		// Get data of a specific student
		Logger::log("Interface got 'GET_USER' with data $data.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'GET_USER' with data $data but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		$user = new Users($data);				
		print($user->getDataJson());
		break;

	///////////////////////////////////////////////////////////////
	// Add a new user.
	// 
	// Roles required: admin
	case 'ADD_USER':
		// Get data of a specific student
		Logger::log("Interface got 'ADD_USER'.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'ADD_USER' but the user was not allowed to call this command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}

		$fb = Users::addNewUser(
			$data['username'],
			$data['password'],
			$data['realname'],
			$data['enabled'],
			$data['is_corrector'],
			$data['is_admin'],
			$AUTH);

		if($fb === true){
			print("{\"success\":\"yes\"}");
		}else{
			print("{\"success\":\"no\",\"errormsg\":\"\"}");
		}

		break;

	///////////////////////////////////////////////////////////////
	// Edit an user.
	// 
	// data must be a JSON of the following format:
	//   {"username":<username>, "realname":<realname>,
	//    "password":<new_password>, "enabled":<enabled>,
	//    "is_corrector":<is_corrector>, "is_admin":<is_admin>"}
	// where
	//   <username> is the id of the user to be edited
	//   <realname> is the user's realname
	//   <password> is the user's password. If empty the current password
	//       will be kept.
	//   <enabled> indicates whether the user can login (expected
	//       values are 'yes' or 'no')
	//   <is_corrector> indicates whether the user os a corrector
	//       (expected values are 'yes' or 'no')
	//   <is_admin> indicates whether the user os an admin (expected
	//       values are 'yes' or 'no')
	//
	// Roles required: admin
	case 'EDIT_USER':
		// Edit data of a specific user
		Logger::log("Interface got 'EDIT_USER' for user ".$data['username'],Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("admin")){
			Logger::log("Interface got 'EDIT_USER' but the user was not allowed to call this 	command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		// ex ante we assume that the following operations will be successfull
		$success = true;
		$errormsg = "";

		// load user's current data
		$user = new Users($data['username'],true);
		if( !$user->loaded() ){ // check whether data was loaded
			Logger::log("User ".$data['username']." not found. Interface could not handle 'EDIT_USER'.".Logger::LOGLEVEL_WARNING);
			print("{\"success\":\"no\",\"errormsg\":\"Benutzer existiert nicht.\"}");
			break;
		}

		$user->setIsCorrector($data['is_corrector']);
		$user->setIsAdmin($data['is_admin']);
		$user->setEnabled($data['enabled']);
		$user->setRealname($data['realname']);
		if($data['password'] != ""){
			$user->setPassword($data['password'],$AUTH);
		}
		$user->save(); // we should test for success.

		// now its time to return sth.
		if( $success ){
			print("{\"success\":\"yes\"}");
		}else{
			print("{\"success\":\"no\",\"errormsg\":\"".$errormsg."\"}");
		}
		break;


	case 'LIST_LOGS':
		// List all main logs
		Logger::log("Interface got 'LIST_LOGS' with data $data.",Logger::LOGLEVEL_VERBOSE);
		if(!$AUTH->hasRole("dev")){
			Logger::log("Interface got 'LIST_LOGS' with data $data but the user was not allowed to call this 	command.",Logger::LOGLEVEL_ERROR);
			print("{\"success\":\"no\",\"errormsg\":\"Schwerwiegender interner Fehler.\"}");
			break;
		}
		$events = LOGGER::getLogs();
		$tmpl = "<ul>";
		for($i=count($events);$i>0;$i--){
			$tmpl .= "<li><b>[".$events[$i-1]["loglevel"]."]</b> (".$events[$i-1]["date"].") ".$events[$i-1]["message"]."</li>";
		}
		$tmpl .= "</ul>";
		print("{\"success\":\"yes\",\"logs\":\"".base64_encode($tmpl)."\"}");
		break;

	case 'LIST_ALL_EXAMS':
		// get a list of all exams
		print(Exams::getAllExamsJson());
		break;

	///////////////////////////////////////////////////////////////
	// Check whether anonymous users can register.
	// 
	case 'IS_REGISTRATION_ALLOWED':
		// check whether registration is allowed an if so get information about the current slot.
		$conf = new Dataset("config",false);
		$registrationslots = $conf->dom->getElementsByTagName("registrationslot");
		$matches = 0;
		foreach($registrationslots as $slot){
			$now = date("Y-m-d\TH:i:s");
			$start = $slot->getAttribute("start");
			$end = $slot->getAttribute("end");
			if($now >= $start && $now <= $end){
				$matches++;
				break;
			}
		}
		if($matches>0){
			print("{\"success\":\"yes\",\"ends\":\"$end\"}");
		}else{
			print("{\"success\":\"no\"}");
		}
		break;

	///////////////////////////////////////////////////////////////
	// Show a list of all available courses.
	// 
	case 'LIST_COURSES':
		$conf = new Dataset("config",false);
		$studylist = array();
		foreach($conf->dom->getElementsByTagName("course") as $course){
			$studylist[] = $course->nodeValue;
		}
		print(json_encode(array('success'=>'yes','courses'=>$studylist)));
		break;

	case 'GET_NR_OF_SHEETS':
		// returns the number of sheets from the configuration file
		$conf = new Dataset("config",false);
		$sheets = (int)($conf->dom->getElementsByTagName("sheets")->item(0)->nodeValue);
		print(json_encode(array('success'=>'yes','sheets'=>$sheets)));
		break;

	default:
		echo "UNKNOWN";
		Logger::log("i.php recieved an unknown command, namely: $cmd",Logger::LOGLEVEL_WARNING);
}


?>
