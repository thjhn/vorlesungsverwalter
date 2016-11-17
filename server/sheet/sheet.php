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
 *  Contains the class Sheet.
 */


include_once('server/crypto/crypto.php');

/**
 * Provides the necessary information about scores.
 *
 * @author Thomas Jahn vv3@t-und-j.de
 */
class Sheet{
      	var $sid, $editable,  $sheetnode, $dataset;
	/**
         * The constructor loads the sheet using the unique id.
	 *
	 * When using this class it is important to bare in mind that there may not be a sheet with the given id.
	 * 
	 * @param string $sid The sheets's unique id.
	 * @param boolean $editable If 'true', the sheets's values are changeable. If 'false' the sheet is read-only.
	*/

	function Sheet($sid,$editable){
		$this->sid = $sid;
		$this->editable = $editable;

		// load the sheet-dataset
		$this->dataset = new Dataset("sheets",$this->editable);
		if($this->dataset->isLoaded()){
			// find the sheet by sid.
			$matches = 0;
			foreach($this->dataset->dom->getElementsByTagName("score") as $cur_score){
				if($cur_score->getAttribute('sid') == $sid){
					// $cur_score is the student we were looking for
					$matches++;
					$this->sheetnode  = $cur_score;
					break;
				}
			}
			// if we haven't found that student
			if($matches == 0){
				$this->sid = "";
			}
		}else{
			// dataset was not loaded!
			Logger::log("sheet.php could not load dataset.",Logger::LOGLEVEL_ERROR);
			$this->editable = false;
			$this->sid = "";
		}
	}

	/**
	 * Update the Score of this sheet.
	 * 
	 * @param string $newScore The sheets's new Score
	 * @return true or false, depending on the success of the operation.
	*/
	function updateScore($newScore,$auth){
		if($this->sid != ""){
			// We check whether the format of $score is valid.
			if(preg_match('/^[0-9]+(.[0-9]+)?$/', $newScore)==0){
				Logger::log("sheet.php, updateScore: Called with invalid format score $newScore.",Logger::LOGLEVEL_VERBOSE);
				return false;
			}
			if($this->editable){
				$newScore = Crypto::encrypt_in_team($newScore,$auth);
				if($newScore === false){
					Logger::log("sheet.php: Was not able to encrypt new ScoreValues!",Logger::LOGLEVEL_ERROR);	
					return false;
				}
				$node = $this->sheetnode->setAttribute('score',$newScore);
				$this->dataset->save();
				Logger::log("sheet.php: Saved an updated score.",Logger::LOGLEVEL_VERBOSE);
				return true;
			}else{
				Logger::log("sheet.php: Tried to save changes in read-only mode!",Logger::LOGLEVEL_ERROR);
				return false;
			}
		}else{
			Logger::log("sheet.php: Tried to save changes in a non existing sheet!",Logger::LOGLEVEL_ERROR);
			return false;
		}
	}

	/**
	 * Get the Corrector of the sheet.
	 * 
	 * @return The corrector or false.
	*/
	function getCorrector(){
		if($this->sid != ""){
			$node = $this->sheetnode->getAttribute('corrector');
			return true;
		}else{
			Logger::log("sheet.php: Tried to save changes in a non existing sheet!",Logger::LOGLEVEL_ERROR);
			return false;
		}
	}

	/**
	 * Returns the score that the student with id $uid recieved on sheet $sheet.
	 *
	 * It returns a Json-object with a field 'success' indicating whether the request was sucessfully ('yes') or not ('no').
	 * In case of success there a the fields 'score' contaning the recieved score, 'corrector' containing the corrector's username and 'correctorreal' containing the corrector's real name.
	 * 
	 * @param string $sheet The no. of the sheet.
	 * @param string $uid The unique id of the student.
	 * 
	 * @return string A Json-object as described above.
	 *
	 * @todo Write this function.
	*/
	public static function getScore($sheet,$uid){

	}


	/**
	 * Returns all scores of all students.
	 *
	 * It returns an array of <A> with indices the UIDs of the
	 * score entries.
	 * <A> is an object with fields
	 *   'scores' which contains an array of type <B>
	 *   'familyname'
	 *   'givenname'
	 * <B> is an array of the scores indexed by sheet-no.
	 * 
	 * @param Auth $auth the authentication object used for decryption.
	 * 
	 * @return see above
	 */
	public static function getAllScores($auth){
		// here we need the student-stuff
		include_once("server/student.php");

		// First of all, get the number of sheets out of the configuration
		$config = new Dataset('config',false);
		foreach($config->dom->getElementsByTagName("sheets") as $nrofsheetsnode){
			$nrofsheets = $nrofsheetsnode->textContent;
		}

		// create a list of zero-scores
		$zeroScoreList = array();
		for($i=0; $i<$nrofsheets; $i++){
			$zeroScoreList[] = "--";
		}

		//load the sheets dataset in read-mode
		$sheets = new Dataset('sheets',false);

		// $list is the list we are going to return later
		$list = array();

		// iterate over all score-nodes.
		foreach($sheets->dom->getElementsByTagName("score") as $scorenode){
			// iterate over all included students
			foreach($scorenode->getElementsByTagName("student") as $studnode){	
				// add the scores
				$curUid = $studnode->getAttribute("uid");
				if(!array_key_exists($curUid,$list)){
					$list[$curUid]['scores'] = $zeroScoreList;

					// load the data of the corresponding student:
					$curStud = new Student($curUid);
					$list[$curUid]['familyname'] = $curStud->getField("familyname");
					$list[$curUid]['givenname'] = $curStud->getField("givenname");
				}
				$score_dec = Crypto::decrypt_in_team($scorenode->getAttribute("score"),$auth);
				if($score_dec === false){ return false; }
				$list[$curUid]['scores'][($scorenode->getAttribute("sheet"))-1] = $score_dec;
			}
		}
		return $list;
	}

	/**
	 * Returns a statistic of scores.
	 *
	 * ...
	 * 
	 * @param Auth $auth the authentication object used for decryption.
	 * 
	 * @return see above (not yet documented!)
	 */
	public static function getAllScoresStat($auth){
		// here we need the student-stuff
		include_once("server/student.php"); // not yet! later we want to include groups!

		// First of all, get the number of sheets out of the configuration
		$config = new Dataset('config',false);
		foreach($config->dom->getElementsByTagName("sheets") as $nrofsheetsnode){
			$nrofsheets = $nrofsheetsnode->textContent;
		}

		// Get a list of all groups available.
		include_once("groups/groups.php");
		$groups = Groups::getAllGroups();
		$superGroup['groupid'] = "allgroups";
		$groups[] = $superGroup;

		// $list is the list we are going to return later
		$list = array();

		// append an emtpy item for each sheet
		// the index 0 is for the sum over all sheets
		$emptyItem = array();
		$emptyItem['hist'] = array();
		$emptyItem['max'] = 0;
		$emptyItem['min'] = -1;
		$emptyItem['sum'] = 0;
		$emptyItem['count'] = 0;
		$zeroScoreList = array();
		for($i=0; $i<=$nrofsheets; $i++){
			$list[$i] = [];
			for($j=0; $j<count($groups); $j++){
				$list[$i][$groups[$j]['groupid']] = $emptyItem;
			}
		}


		//load the sheets dataset in read-mode
		$sheets = new Dataset('sheets',false);

		// iterate over all score-nodes.
		foreach($sheets->dom->getElementsByTagName("score") as $scorenode){
			// get the number of the current sheet
			$curSheet = $scorenode->getAttribute("sheet");
			// try to decode the score
			$curScore = Crypto::decrypt_in_team($scorenode->getAttribute("score"),$auth);
			if($score_dec === false){ return false; }
			// adjust maxima and minima if necessary:
			if($list[$curSheet]['allgroups']['max'] < $curScore){
				$list[$curSheet]['allgroups']['max'] = $curScore;
				if($list[0]['allgroups']['max'] < $curScore){
					$list[0]['allgroups']['max'] = $curScore;
				}
			}
			if($list[$curSheet]['allgroups']['min'] < 0 || $list[$curSheet]['allgroups']['min'] > $curScore){
				$list[$curSheet]['allgroups']['min'] = $curScore;
				if($list[0]['allgroups']['min'] < 0 || $list[0]['allgroups']['min'] > $curScore){
					$list[0]['allgroups']['min'] = $curScore;
				}
			}

			// we already have the scores but we want to take
			// multiple students into account accordingly.
			// iterate over all included students
			foreach($scorenode->getElementsByTagName("student") as $studnode){	
				// add the scores, first to the allgroups field.
				$list[$curSheet]['allgroups']['sum'] += $curScore;
				$list[0]['allgroups']['sum'] += $curScore;
				$list[$curSheet]['allgroups']['count']++;
				$list[0]['allgroups']['count']++;
				if(array_key_exists($curScore,$list[$curSheet]['allgroups']['hist'])){
					$list[$curSheet]['allgroups']['hist'][$curScore]++;
				}else{
					$list[$curSheet]['allgroups']['hist'][$curScore]=1;
				}
				if(array_key_exists($curScore,$list[0]['allgroups']['hist'])){
					$list[0]['allgroups']['hist'][$curScore]++;
				}else{
					$list[0]['allgroups']['hist'][$curScore]=1;
				}
				// now, we see in what group the student is in
				$curStud = new Student($studnode->getAttribute("uid"), false);
				if(!$curStud->loaded()){
					Logger::log("We found scores for a student with id ".$studnode->getAttribute("uid")." who does not exist.",LOGGER_ERROR);
				}else{
					$curGroup = $curStud->getGroup();
					if($curGroup === false){
						Logger::log("Could not get group of student ".$studnode->getAttribute("uid").".",LOGGER_ERROR);
					}else{
						if($list[$curSheet][$curGroup]['max'] < $curScore){
							$list[$curSheet][$curGroup]['max'] = $curScore;
						}
						if($list[$curSheet][$curGroup]['min'] < 0 || $list[$curSheet][$curGroup]['min'] > $curScore){
							$list[$curSheet]['allgroups']['min'] = $curScore;
						}
						$list[$curSheet][$curGroup]['sum'] += $curScore;
						$list[$curSheet][$curGroup]['count']++;
						if(array_key_exists($curScore,$list[$curSheet][$curGroup]['hist'])){
							$list[$curSheet][$curGroup]['hist'][$curScore]++;
						}else{
							$list[$curSheet][$curGroup]['hist'][$curScore]=1;
						}
						if(array_key_exists($curScore,$list[0][$curGroup]['hist'])){
							$list[0][$curGroup]['hist'][$curScore]++;
						}else{
							$list[0][$curGroup]['hist'][$curScore]=1;
						}

					}
				}
			}
		}
		return $list;
	}

	public static function getScores($corrector,$auth){
		// here we need the student-stuff
		include_once("server/student.php");

		//load the sheets dataset in read-mode
		$sheets = new Dataset('sheets',false);

		// $list is the list we are going to return later
		$list = array();

		// iterate over all score-nodes.
		foreach($sheets->dom->getElementsByTagName("score") as $scorenode){
			//was the current sheet corrected by $corrector
			if($scorenode->getAttribute("corrector") == $corrector){
				$item = array();
				$item["sid"] = $scorenode->getAttribute("sid");
				$item["sheet"] = $scorenode->getAttribute("sheet");
				$item["corrector"] = $scorenode->getAttribute("corrector");
				$item["score"] = Crypto::decrypt_in_team($scorenode->getAttribute("score"),$auth);
				if($item["score"] === false){
					Logger::log("sheet.php; Could not decrypt scores while scanning for scores given by corrector $corrector.",LOGGER_ERROR);
				}

				// append a list of the students
				$studlist = array();
				foreach($scorenode->getElementsByTagName("student") as $studnode){
					$s = new Student($studnode->getAttribute("uid"),false);
					$studlist[] = $s->getField("familyname").", ".$s->getField("givenname");
				}

				$item["students"] = $studlist;

				$list[] = $item;				
			}
		}
		// create the return value
		$ret = array();
		$ret['success'] = 'yes';
		$ret['list'] = $list;

		return json_encode($ret);
	}



	/**
	 * Returns the scores that the student with id $uid recieved.
	 *
	 * It returns a Json-object with a field 'success' indicating whether the request was sucessfully ('yes') or not ('no').
	 * The field 'list' contains the a list of items that in turn contain the fields 'score', 'sheet', 'corrector', 'correctorreal'.
	 * Note: If there are no sheets the return indicates 'success' (and a list of length zero).
	 * 
	 * @param string $uid The unique id of the student.
	 * 
	 * @return string A Json-object as described above.
	*/
	public static function getScoreByStudent($uid,$auth){
		//load the sheets dataset in read-mode
		$sheets = new Dataset('sheets',false);

		// $list is the list we are going to return later
		$list = array();
		
		// iterate over all score-nodes.
		foreach($sheets->dom->getElementsByTagName("score") as $scorenode){
			//is the student an author of that sheet? -- iterate over all student-nodes
			$matches = 0;
			foreach($scorenode->getElementsByTagName("student") as $studnode){
				if($studnode->getAttribute("uid") == $uid){
					// the student is an author of the current sheet => add to list
					$item = array();
					$item["sheet"] = $scorenode->getAttribute("sheet");
					$item["corrector"] = $scorenode->getAttribute("corrector");
					$item["score"] = Crypto::decrypt_in_team($scorenode->getAttribute("score"),$auth);
					if($item["score"] === false){
						Logger::log("sheet.php function getScoreByStudent(); Could not decrypt scores while scanning for scores given by corrector $corrector.",LOGGER_ERROR);
					}
					$list[] = $item;
					break;
				}
			}
		}

		// create the return value
		$ret = array();
		$ret['success'] = 'yes';
		$ret['list'] = $list;

		return json_encode($ret);

	}

	/**
	 * Stores a score.
	 * 
	 * @param Auth $auth The user's authentication object
	 * @param string $sheet The no. of the sheet.
	 * @param string[] $students An array of the students' ids
	 * $param string $score The recieved score
	 * $param string $corrector The id of the corrector entering that score
	 * 
	 * @return A Json-object with a field 'success'.
	*/
	public static function setScore($auth,$sheet,$students,$score,$corrector){
		// First, we check whether the students really exists.
		for($i=0; $i<count($students);$i++){
			$s = new Student($students[$i]);
			if(! $s->exists()){
				// The Requested Student does not exist.
				Logger::log("sheet.php, setScore: User $auth requested to setScore for student $student. That student does not exist.",Logger::LOGLEVEL_VERBOSE);
				return "{\"success\":\"no\",\"errormsg\":\"Student existiert nicht.\"}";			
			}
		}

		// Second, we check whether the format of $score is valid.
		if(preg_match('/^[0-9]+(\.[0-9]+)?$/', $score)==0){
			Logger::log("sheet.php, setScore: User $auth requested to setScore with score $score. The format is considered invalid.",Logger::LOGLEVEL_VERBOSE);
			return "{\"success\":\"no\",\"errormsg\":\"Das Format von 'Punkte' ist nicht valide.\"}";
		}

		// Now, we are sure that the students exists.
		//load the sheets dataset in write-mode
		$sheets = new Dataset('sheets',true);
		// was the dataset loaded?
		if(!$sheets->isLoaded()){
			Logger::log("sheet.php setScore(): Dataset not loaded.",Logger::LOGLEVEL_ERROR);
			return "{\"success\":\"no\",\"errormsg\":\"Es ist ein interner Fehler aufgetreten.\"}";
		}

		//we only allow one entry per sheet and per student
		$matches = 0;
		foreach($sheets->dom->getElementsByTagName("score") as $scorenode){
			if(strcmp($scorenode->getAttribute("sheet") , $sheet) == 0){
				// the current node contains information about the current sheet
				foreach($scorenode->getElementsByTagName("student") as $studnode){
					for($i=0; $i<count($students); $i++){
						if($studnode->getAttribute("uid") == $students[$i]){
							// we already have a score for that sheet and that student
							$matches++;
							break;
						}
					}
				}
			}
		}
		
		if($matches>0){
			// we already have a score for that sheet and that student
			return "{\"success\":\"no\",\"errormsg\":\"F&uuml;r mindestens einen der angegebenen Studenten waren für dieses Blatt bereits Punkte eingetragen.\"}";
		}

		// we do not have a score for that sheet and that student
		// create a new score-node and append that node to the dataset
		$nodeScore = $sheets->dom->createElement('score');
		// the new node gets the following attributes
		//	*	sheet -- the sheetno.
		//	*	corrector -- the corrector's username
		//	*	score -- the archieved score
		$nodeScore->setAttribute("sid",uniqid(true));
		$nodeScore->setAttribute("sheet",$sheet);
		$nodeScore->setAttribute("corrector",$corrector);
		$nodeScore->setAttribute("score",Crypto::encrypt_in_team($score,$auth));
		$sheets->dom->childNodes->item(0)->appendChild($nodeScore);
		// finally add for each student a studentnode
		for($i=0;$i<count($students);$i++){
			$nodeStud = $sheets->dom->createElement('student');
			$nodeStud->setAttribute("uid",$students[$i]);
			$nodeScore->appendChild($nodeStud);
		}
		
		$sheets->save();
		return "{\"success\":\"yes\"}";
 	}

	/**
	 * Changes a single score identified by $sheet and $student.
	 * 
	 * @param string $sheet no. of sheet
	 * @param string $student uid of student
	 * @param string $newscore The score that should be saved.
	 * @param $auth The user's authentication object.
	 * 
	 * @return int 0 on success and -1 otherwise.
	*/
	public static function changeScore($sheet,$student,$newscore,$auth){
		// First, we check whether this student really exists.
		$s = new Student($student);
		if(! $s->exists()){
			// The Requested Student does not exist.
			Logger::log("sheet.php, changeScore($sheet,$student,$newscore) was called. That student does not exist.",Logger::LOGLEVEL_VERBOSE);
			return -1;			
		}
		// Second, we check whether the format of $newscore is valid.
		if(preg_match('/^[0-9]+(.[0-9]+)?$/', $newscore)==0){
			Logger::log("sheet.php, changeScore($sheet,$student,$newscore) was called. The format of newscore is considered invalid.",Logger::LOGLEVEL_VERBOSE);
			return -1;
		}

		// Now, we are sure that the student exists.

		//load the sheets dataset in write-mode
		$sheets = new Dataset('sheets',true);
		//we only allow one entry per sheet and per student
		$matches = 0;
		foreach($sheets->dom->getElementsByTagName("score") as $scorenode){
			if(strcmp($scorenode->getAttribute("sheet") , $sheet) == 0){
				// the current node contains information about the current sheet
				foreach($scorenode->getElementsByTagName("student") as $studnode){
					if($studnode->getAttribute("uid") == $student){
						// we found the node we were looking for.
						$matches++;
						$newscore = Crypto::encrypt_in_team($newscore,$auth);
						if($newscore === false){
						Logger::log("Could not change score of student $student at sheet $sheet to $newscore: Crypto error.",Logger::LOGLEVEL_ERROR);
							return -1;
						}
						$scorenode->setAttribute("score",$newscore);
					}
				}
			}
		}

		if($matches==0){
			// We can't save the change; no such node.
			Logger::log("Could not change score of student $student at sheet $sheet to $newscore: No such node exists.",Logger::LOGLEVEL_WARNING);
			return -1;
		}
		$sheets->save();
		return 0;
	}

	/**
	 * Changes scores.
	 *
	 * Calls the function changeScore iteratively.
	 * 
	 * @param string $student uid of student
	 * @param array $newscore A list of items. Each item containing the fiels 'sheet' and 'newscore'.
	 * @param $auth The user's authentication object.
	 * 
	 * @return int Returns the number of failures (-n means n failures). Returns 0 when there where no issues at all.
	*/
	public static function changeScoreByList($student,$list,$auth){
		$failures = 0;
		for($i=0;$i<count($list);$i++){
			$failures += Sheet::changeScore($list[$i]['sheet'],$student,$list[$i]['newscore'],$auth);
		}
		return $failures;
	}
};
