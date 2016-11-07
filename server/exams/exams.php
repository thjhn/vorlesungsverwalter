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
			if($cur_exam->getElementsByTagName("id")->item(0)->nodeValue == $this->exam){
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
	 * Try to get the value of a field.
	 * 
	 * @param string $tagname The name of the field.
	 *
	 * @return mixed returns false if the required field is not present. It returns the value of the first node with that tagename else.
	 */
	function getField($tagname){
		if($this->exam != ""){ // check whether loading an exam was successfully before
			$nodes = $this->examnode->getElementsByTagName($tagname);
			if($nodes->length > 0){
				if($nodes->length > 1) Logger::log("There are more than one field ".$tagname." for exam".$this->exam,Logger::LOGLEVEL_WARNING);
				return $nodes->item(0)->nodeValue;
			}else{
				return FALSE;
			}
		}else{ // exam was not loaded properly before
			Logger::log("Tried to read field ".$tagname." from an exam that was not loaded properly.",Logger::LOGLEVEL_WARNING);
			return FALSE;
		}
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
			$retstr .= "\"name\":\"".$this->getField("name")."\",";
			$retstr .= "\"registration\":\"".$this->getField("registration")."\",";
			$retstr .= "\"enterscores\":\"".$this->getField("enterscores")."\"";
			$retstr .= "}";
			return $retstr;
		}else{
			// there is no exam in this object
			Logger::log("Tried to get all Data from an exam that was not loaded properly.",Logger::LOGLEVEL_WARNING);
			return "{\"success\":\"no\"}";
		}
	}

	/**
	 * Change certain values of that exams
	 * 
	 * @param array $changes A list of changes to be made. Each item is an associative array with fields 'field' and 'newvalue'
	 *
	 * @return boolean Was saving changes successful?
	 */
	function saveChanges($changes){
		if($this->editable){
			if($this->exam != ""){
				for($i=0;$i<count($changes);$i++){
					$node = $this->examnode->getElementsByTagName($changes[$i]['field']);
					if($node->length > 0){
						if($node->length > 1){
							Logger::log("There are more than one ".$changes[$i]['field']."-nodes for exam ".$this->exam,Logger::LOGLEVEL_WARNING);
						}

						$node->item(0)->nodeValue = $changes[$i]['newvalue'];
					}else{
						$newnode = $this->dataset->dom->createElement($changes[$i]['field'],$changes[$i]['newvalue']);
						$this->examnode->appendChild($newnode);
					}
				}

				$this->dataset->save();
				return true;
			}else{
				Logger::log("Tried to save changes for a not existing exam!",Logger::LOGLEVEL_ERROR);
				return false;
			}
		}else{
			Logger::log("Tried to save changes in read-only mode!",Logger::LOGLEVEL_ERROR);
				return false;
		}
	}


	/**
	 * Returns a json object containing some information about each exam
	 *
	 * @return the json
	 */
	public static function getAllExamsJson(){
		// Load the corresponding dataset (in read-mode)
		$users = new Dataset('exams',false);

		$list = array();
		// iterate over exams
		foreach($users->dom->getElementsByTagName("exam") as $exam){
			// TODO Error handling
			$item["exam"] = $exam->getElementsByTagName("id")->item(0)->nodeValue;
			$item["name"] = $exam->getElementsByTagName("name")->item(0)->nodeValue;
			$item["registration"] = $exam->getElementsByTagName("registration")->item(0)->nodeValue;
			$item["enterscores"] = $exam->getElementsByTagName("enterscores")->item(0)->nodeValue;

			$list[] = $item;
		}
		return json_encode($list);
	}

	/**
	 * Add an exam with a newly generated id.
	 * The values of the new exam are in no way specified. You sould call saveChanges afterwards!
	 *
	 * @return the id of the newly generated exam
	*/	
	public static function addExam(){
		//load the exams dataset in write-mode
		$exams = new Dataset('exams',true);
		// create a new score-node and append that node to the dataset
		$newid = uniqid(true);
		$nodeExam = $exams->dom->createElement('exam');
		$nodeExamID = $exams->dom->createElement('id');
		$nodeExamID->nodeValue=$newid;
		$nodeExam->appendChild($nodeExamID);
		$exams->dom->childNodes->item(0)->appendChild($nodeExam);
		$exams->save();
		return $newid;
	}
}


?>
