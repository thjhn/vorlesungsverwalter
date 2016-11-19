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
 *  Contains the class Exams.
 */

/**
 * Provides the necessary information about an exam.
 *
 * @author Thomas Jahn vv3@t-und-j.de
 */
class Exams{
	var $exam,$editable,$examnode;

	/**
	 * The constructor loads the exam using its id.
	 * 
	 * @param string $exam The exam id
	 * @param boolean $editable If 'true', the values are changeable. If 'false' they are read-only.
	 */
	function Exams($exam,$editable=false){
		$this->exam = $exam;
		$this->editable = $editable;

		// load the exams-dataset
		$this->dataset = new Dataset("exams",$this->editable);
	
		// find the exam by id.
		$matches = 0;
		foreach($this->dataset->dom->getElementsByTagName("exam") as $cur_exam){
			if($cur_exam->getAttribute("id") == $this->exam){
				// $cur_exam is the exam we were looking for
				$matches++;
				$this->examnode  = $cur_exam;
				break;
			}
		}
		// just remove the exam field if we have not found the exam.
		if($matches == 0){
			Logger::log("Failed loading exam with id ".$exam.".",Logger::LOGLEVEL_WARNING);
			$this->exam = "";
		}
		// as we have just counted the number of nodes with this id, warn if the id
		// occurs more than once.
		if($matches > 1){
			Logger::log("The id ".$exam." occurs $matches times.",Logger::LOGLEVEL_ERROR);
		}
	}


	/**
	 * Check whether exam is loaded.
	 *
	 * @return bool loaded status
	 */
	function isLoaded(){
		return $this->exam != "";
	}


	/**
	 * Save changes permanently.
	 * 
	 * @return bool true on success.
	 */
	function save(){
		if(!$this->isLoaded()) return false;

		$this->dataset->save();
		return true;
	}


	/**
         * Provides the exam name
	 *
	 * @return name, or empty string on failure
	 */
	function getName(){
		if(!$this->isLoaded()) return "";
		return $this->examnode->getAttribute("name");
	}


	/**
         * Set the exam name
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $name the new value
	 *
	 * @return boolean true on success, false on failure
	 */
	function setName($name){
		if(!$this->isLoaded()) return False;

		$this->examnode->setAttribute("name",$name);
		return True;
	}

	/**
         * Is registration enabled?
	 *
	 * @param $stringoutput true if output is a true/false string
	 *
	 * @return boolean state or 'yes' and 'no' (see parameters)
	 */
	function getEnabled($stringoutput = False){
		if(!$this->isLoaded()) return False;

		$state = $this->examnode->getAttribute("registration");
		if($stringoutput){
			return $state;
		}
		if($state == 'true'){
			return true;
		}else{
			return false;
		}
	}


	/**
         * Enable or Disable registration to exam
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $state 'yes' or 'no'
	 *
	 * @return boolean true on success, false on failure
	 */
	function setEnabled($state){
		if(!$this->isLoaded()) return False;
		if($state == "true"){
			$this->examnode->setAttribute("registration","true");
		}else{
			$this->examnode->setAttribute("registration","false");
		}
		return True;
	}

	/**
         * Get the number of problems.
	 * Note that there is no corresponding set function as this field should never
	 * be changed!
	 *
	 * @return int number of problems, -1 on failure
	 */
	function getNoProblems(){
		if(!$this->isLoaded()) return -1;

		return $this->examnode->getAttribute("problems");
	}


	/**
         * Is storing scores enabled?
	 * @param $stringoutput true if output is a true/false string
	 * @return mixed state boolean or string (see params)
	 */
	function getEnterscores($stringoutput=false){
		if(!$this->isLoaded()) return False;

		$state = $this->examnode->getAttribute("enterscores");
		if($stringoutput){
			return $state;
		}
		if($state == 'true'){
			return true;
		}else{
			return false;
		}
	}

	/**
         * Enable or Disable storing scores
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $state 'yes' or 'no'
	 *
	 * @return boolean true on success, false on failure
	 */
	function setEnterscores($state){
		if(!$this->isLoaded()) return False;
		if($state == "true"){
			$this->examnode->setAttribute("enterscores","true");
		}else{
			$this->examnode->setAttribute("enterscores","false");
		}
		return True;
	}


	/**
         * Store scores for a given student.
	 *
	 * We assume that count($scores) equals the number of problems
	 * 
	 * @param $auth auth object for encryption
	 * @param $student student id
	 * @param string[] $scores Array of scores
	 * @param bool $overwrite allow overwriting existing values!
	 *
	 * @return JSON
	 */
	function setScore($auth, $student, $scores, $overwrite=false){
		if(!$this->isLoaded()){
			Logger::log("Tried to add scores but dataset was not loaded.",Logger::LOGLEVEL_WARNING);
			return "{\"success\":\"no\", \"errormsg\":\"Interner Fehler\"}";
		}
		if(!$this->editable){
			Logger::log("Tried to add scores but dataset was not loaded in write mode.",Logger::LOGLEVEL_ERROR);
			return "{\"success\":\"no\", \"errormsg\":\"Interner Fehler\"}";
		}
		// Find the students node.
		$matches = 0;
		foreach($this->examnode->getElementsByTagName("student") as $cur_stud){
			if($cur_stud->getAttribute("id") == $student){
				// $cur_stud this already to be registered.
				$matches++;
				break;
			}
		}
		if($matches == 0){
			Logger::log("Tried to add exam scores but student was not found.",Logger::LOGLEVEL_VERBOSE);
			return "{\"success\":\"no\", \"errormsg\":\"Student ist nicht zur Klausur angemeldet.\"}";
		}
		// create the score nodes
		if( (!$overwrite) && ($cur_stud->getAttribute("scores") != "") ){
			// We already have stored sth and we do not allow overwrite
			Logger::log("Tried to add exam scores but score was already set.",Logger::LOGLEVEL_VERBOSE);
			return "{\"success\":\"no\", \"errormsg\":\"Es sind bereits Punkte eingetragen.\"}";
		}
		$cur_stud->setAttribute("scores",Crypto::encrypt_in_team(json_encode($scores),$auth));
		$this->dataset->save();
		return "{\"success\":\"yes\"}";
		
	}



	/**
         * Get most of the data about the Exam as a Json-object.
	 * 
	 * The Json contains a field 'success' which is 'yes' if there is an exam loaded. It is 'no' else.
	 *
	 * @return string Returns the json object.
	 */
	function getDataJson(){
		if($this->exam != ""){
			$retstr  = "{";
			$retstr .= "\"success\":\"yes\",";
			$retstr .= "\"exam\":\"".$this->exam."\",";
			$retstr .= "\"name\":\"".$this->getName()."\",";
			$retstr .= "\"problems\":\"".$this->getNoProblems()."\",";
			$retstr .= "\"registration\":\"".$this->getEnabled(true)."\",";
			$retstr .= "\"enterscores\":\"".$this->getEnterscores(true)."\"";
			$retstr .= "}";
			return $retstr;
		}else{
			// there is no exam in this object
			Logger::log("Tried to get all Data from an exam that was not loaded properly.",Logger::LOGLEVEL_WARNING);
			return "{\"success\":\"no\"}";
		}
	}

	/**
         * Register a student to this exam.
	 *
	 * @param string $student the id of the student to be added
	 *
	 * @return json with fields success ('yes' or 'no') and an error
	 * 	message errormsg is success=='no'
	 */
	function registerStudent($student){
		// First try to verify that the student exists
		$stud = new Student($student);
		if(!$stud->loaded()){
			Logger::log("Tried adding student $student to exam but student does not exist.",Logger::LOGLEVEL_WARNING);
			return "{\"success\":\"no\",\"errormsg\":\"Student ist nicht zur Vorlesung angemeldet.\"}";
		}

		// Verify that the student is not yet registered to  the exam.
		$matches = 0;
		foreach($this->examnode->getElementsByTagName("student") as $cur_stud){
			if($cur_stud->getAttribute("id") == $student){
				// $cur_stud this already to be registered.
				$matches++;
				break;
			}
		}
		if($matches > 0){
			Logger::log("Tried adding student $student to exam but student is already registrered.",Logger::LOGLEVEL_VERBOSE);
			return "{\"success\":\"no\",\"errormsg\":\"Student ist bereits zur Klausur angemeldet.\"}";
		}

		$newNode = $this->dataset->dom->createElement("student");
		$newNode->setAttribute("id",$student);
		$this->examnode->appendChild($newNode);
		if($this->dataset->save()){
			return "{\"success\":\"yes\"}";
		}else{
			Logger::log("Tried adding student $student to exam but saving failed.",Logger::LOGLEVEL_VERBOSE);
			return "{\"success\":\"no\",\"errormsg\":\"Interner Fehler.\"}";
		}
	}


	/**
	 * Returns all scores of all students.
	 *
	 * It returns an array of <A> with indices the UIDs of the
	 * score entries.
	 * <A> is an array of objects with fields
	 *   <exam> id of an exam
	 *   <examname> name of an exam
	 *   <problems> number of problems in this exam
	 *   <scores> with value of type <B>
	 * <B> is an array of the scores indexed problem no.
	 * 
	 * @param Auth $auth the authentication object used for decryption.
	 * 
	 * @return see above
	 */
	public static function getAllScores($auth){
		//load the exams dataset in read-mode
		$exams = new Dataset('exams',false);

		// $list is the list we are going to return later
		$list = array();

		// iterate over all score-nodes.
		foreach($exams->dom->getElementsByTagName("exam") as $examnode){
			$entry = array();
			$entry["exam"] = $examnode->getAttribute("id");
			$entry["examname"] = $examnode->getAttribute("name");
			$entry["examname"] = $examnode->getAttribute("name");
			$entry["problems"] = $examnode->getAttribute("problems");
			$problems = (int)($entry["problems"]);
			// create a list of zero-scores
			$zeroScoreList = array();
			for($i=0; $i<$problems; $i++){
				$zeroScoreList[] = "--";
			}


			// iterate over all included students
			foreach($examnode->getElementsByTagName("student") as $studnode){
				// add the scores
				$curUid = $studnode->getAttribute("id");
				$scoreStr = $studnode->getAttribute("scores");
				$entry["scores"] = $zeroScoreList;
				if($scoreStr != ""){
					$entry["scores"] = json_decode(Crypto::decrypt_in_team($scoreStr,$auth));
				}
				$list[$curUid][ $examnode->getAttribute("id") ] = $entry;
			}
		}
		return $list;
	}


	/**
	 * Returns a json object containing some information about each exam
	 *
	 * @return the json
	 */
	public static function getAllExamsJson(){
		// Load the corresponding dataset (in read-mode)
		$exams = new Dataset('exams',false);

		$list = array();
		// iterate over exams
		foreach($exams->dom->getElementsByTagName("exam") as $exam){
			// TODO Error handling
			$item["exam"] = $exam->getAttribute("id");
			$item["examname"] = $exam->getAttribute("name");
			$item["problems"] = $exam->getAttribute("problems");
			$item["registration"] = $exam->getAttribute("registration");
			$item["enterscores"] = $exam->getAttribute("enterscores");

			$list[] = $item;
		}
		return json_encode($list);
	}

	/**
	 * Add an exam with a newly generated id.
	 * The values of the new exam are in no way specified. You sould store them afterwards.
	 * However, the field problems is set when adding an Exam. This field cannot be changed
	 * afterwards.
	 *
	 * @param int $problems number of problems in the exam
	 * 
	 * @return the id of the newly generated exam
	*/	
	public static function addExam($problems){
		//load the exams dataset in write-mode
		$exams = new Dataset('exams',true);
		// create a new score-node and append that node to the dataset
		$newid = uniqid(true);
		$nodeExam = $exams->dom->createElement('exam');
		$nodeExam->setAttribute("id",$newid);
		$nodeExam->setAttribute("problems",$problems);
		$exams->dom->childNodes->item(0)->appendChild($nodeExam);
		$exams->save();
		return $newid;
	}
}


?>
