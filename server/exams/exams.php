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
	 * Change certain values of that exams
	 * 
	 * @param array $changes A list of changes to be made. Each item is an associative array with fields 'field' and 'newvalue'
	 *
	 * @return boolean Was saving changes successful?
	 */
	/*function saveChanges($changes){
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
	}*/


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
			$item["registration"] = $exam->getAttribute("registration");
			$item["enterscores"] = $exam->getAttribute("enterscores");

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
		$nodeExam->setAttribute("id",$newid);
		$exams->dom->childNodes->item(0)->appendChild($nodeExam);
		$exams->save();
		return $newid;
	}
}


?>
