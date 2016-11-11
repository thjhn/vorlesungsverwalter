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
 *  Contains the class Student.
 */

include_once("server/crypto/crypto.php");

/**
 * Provides the necessary information about a student.
 *
 * @author Thomas Jahn vv3@t-und-j.de
 */

class Student{
	var $uid, $editable, $studentnode, $dataset;

	/**
     * The constructor loads the student using the unique id.
	 *
	 * When using this class it is important to bare in mind that there may not be a student with the given id.
	 * 
	 * @param string $uid The student's unique id.
	 * @param boolean $editable If 'true', the student's values are changeable. If 'false' the student is read-only.
	*/
	function Student($uid,$editable=false){
		$this->uid = $uid;
		$this->editable = $editable;

		// load the students-dataset
		$this->dataset = new Dataset("students",$this->editable);
	
		// find the student by uid.
		$matches = 0;
		foreach($this->dataset->dom->getElementsByTagName("student") as $cur_stud){
			if($cur_stud->getAttribute('id') == $uid){
				// $cur_stud is the student we were looking for
				$matches++;
				$this->studentnode  = $cur_stud;
				break;
			}
		}
		// if we haven't found that student
		if($matches == 0){
			$this->uid = "";
		}
	}



	/**
	 * Check whether the Student within this object exists.
	 *
	 * @return true if the student exists, false otherwise
	 */
	function exists(){
		return ($this->uid != "");
	}

	/**
	 * Alias for exists();
	 */
	function loaded(){
		return $this->exists();
	}

	/**
	 * Get the student's group.
	 * 
	 * @return the group
	*/
	function getGroup(){
		if(!$this->loaded()){
			Logger::log("student.php, getGroup was called but student was not loaded properly.",Logger::LOGLEVEL_ERROR);
			return False;
		}
		return $this->studentnode->getAttribute("ingroup");
	}


	/**
     * Try to get the value of a field.
	 * 
	 * @param string $tagname The name of the field.
	 *
	 * @return mixed returns false if the required field is not present. It returns the value of the first node with that tagename else.
	 *
	 * @todo check whether loading a student was successfully before!
	 * @todo in the future, this function should be private or protected!
	*/
	function getField($fieldname){
		if($this->uid != ""){
			// todo: check existence of this 
			return $this->studentnode->getAttribute($fieldname);
		}else{
			Logger::log("student.php method getField was called for a nonexisting student.",Logger::LOGLEVEL_ERROR);
			return FALSE;
		}
	}



	/**
     * Get most of the data about the Studnet as a Json-object.
	 * 
	 * The Json contains a field 'success' which is 'yes' if there is a student loaded. It is 'no' else.
	 *
	 * @return string Returns the json object. If successfully, it currently contains the fields 'uid', 'familyname', 'givenname', 'matrnr', 'course', 'term' and 'email'.
	*/
	function getDataJson($auth){
		if($this->uid != ""){
			$retstr  = "{";
			$retstr .= "\"success\":\"yes\",";
			$retstr .= "\"uid\":\"".$this->uid."\",";
			$retstr .= "\"familyname\":\"".$this->getField("familyname")."\",";
			$retstr .= "\"givenname\":\"".$this->getField("givenname")."\",";

			$matrnr_dec = Crypto::decrypt_team($this->getField("matrnr"),$auth);
			if($matrnr_dec===false){
				Logger::log("Matrnr decryption failure",Logger::LOGLEVEL_ERROR);
				return "{\"success\":\"no\",\"errmsg\":\"data failure\"}";
			}
			$retstr .= "\"matrnr\":\"".$matrnr_dec."\",";
			$retstr .= "\"course\":\"".$this->getField("course")."\",";
			$retstr .= "\"term\":\"".$this->getField("term")."\",";

			//$retstr .= "\"email\":\"".$this->getField("email")."\",";

			$email_dec = Crypto::decrypt_team($this->getField("email"), $auth);
			if($email_dec===false){
				Logger::log("Failed decrypting email.",Logger::LOGLEVEL_ERROR);
				return "{\"success\":\"no\"}";
			}

			$retstr .= "\"email\":\"".$email_dec."\",";
			$retstr .= "\"ingroup\":\"".$this->getField("ingroup")."\"";
			$retstr .= "}";
			return $retstr;
		}else{
			// there is no student in this object
			return "{\"success\":\"no\"}";
		}
	}



	public static function getStudentsInGroupCount($groupid){
		$count = 0;

		// Load the corresponding dataset (in read-mode)
		$students = new Dataset('students',false);

		$list = array();
		// iterate over students
		foreach($students->dom->getElementsByTagName("student") as $student){
			if($student->getAttribute("ingroup")==$groupid){
				$count++;
			}
		}

		return $count;

	}



	/**
	 * Change the fields of that student
	 * 
	 * @param string $familyname The possibly new familyname
	 * @param string $givenname The possibly new givenyname
	 * @param string $matrnr The possibly new matrnr
	 * @param string $term The possibly new term
	 * @param string $course The possibly new course
	 * @param string $email The possibly new email
	 * @param string $ingroup The possibly new group
	 *
	 * @return array Returns an array of strings. On success the array is empty. Otherwise it contains a list of failures. Such failures currently include: the name of the field that could not been validated; DUPLICAT, if a student with the name familyname, givennahme combination already exists; INTERNAL if something very bad happend.
	 */
	function editStudent($familyname, $givenname, $matrnr, $term, $course, $email, $ingroup){
		if($this->editable){
			if($this->uid != ""){
				// We check the format of the given arguments.
				// Whenever the check of a field failed, 
				$inputFailures = array();
				if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
					$inputFailures[] = "email";
				}
				if(preg_match('/^[a-zA-ZÄÖÜäöüßÈèÉé-]+$/', $familyname)==0){
					$inputFailures[] = "familyname";
				}
				if(preg_match('/^[a-zA-ZÄÖÜäöüßÈèÉé-]+$/', $givenname)==0){
					$inputFailures[] = "givenname";
				}
				if(preg_match('/^[0-9]+$/', $matrnr)==0){
					$inputFailures[] = "matrnr";
				}
				if(preg_match('/^[0-9]+$/', $term)==0){
					$inputFailures[] = "term";
				}
				// TODO: Check validity of the course data
				/*if(preg_match('/^[a-zA-Z0-9ÄÖÜäöüßÈèÉé]+$/', $course)==0){
					$inputFailures[] = "course";
				}*/

				// try to load the group in order to verify its existence
				$group = new Groups($ingroup);
				if(!($group->loaded())){
					$inputFailures[] = "ingroup";
				}

				if(count($inputFailures) > 0){				
					return $inputFailures;
				}

				// next check, whether familyname or givenname are to be changed. If so, check whether the new names are already used.
				if(($this->getField("familyname") != $familyname)||($this->getField("givenname") != $givenname)){
					$matches = 0;
					foreach($this->dataset->dom->getElementsByTagName("student") as $student){
						$cur_givenname = $student->getAttribute("givenname");
						$cur_familyname = $student->getAttribute("familyname");
						if($givenname.$familyname == $cur_givenname.$cur_familyname){
							$matches++;
							break;
						}
					}
					if($matches > 0){
						$inputFailures[] = "DUPLICATE";
						return $inputFailures;
					}

					$this->studentnode->setAttribute("familyname",$familyname);
					$this->studentnode->setAttribute("givenname",$givenname);
				}

				// change the other fields
				$matrnr = Crypto::encrypt_for_team($matrnr);
				$this->studentnode->setAttribute("matrnr",$matrnr);
				// TODO: Check for errors
				$email = Crypto::encrypt_for_team($email);
				$this->studentnode->setAttribute("email",$email);
				// TODO: Check for errors
				$this->studentnode->setAttribute("term",$term);
				$this->studentnode->setAttribute("course",$course);
				$this->studentnode->setAttribute("ingroup",$ingroup);

				$this->dataset->save();
				return [];
			}else{
				Logger::log("In student.php: Tried to save changes for a not existing student!",Logger::LOGLEVEL_ERROR);
				return ["INTERNAL"];			
			}
		}else{
			Logger::log("In student.php: Tried to save changes for student ".$this->uid." in read-only mode!",Logger::LOGLEVEL_ERROR);
			return ["INTERNAL"];
		}
	}



	/* --- Below, the static functions follow. --- */



	/**
	 * Try to add a Student to the database.
	 *
	 * @param string $familyname The student's familyname
	 * @param string $givenname  The student's givenname
	 * @param string $matrnr  The student's matrikelnummer
	 * @param string $term  The student's Fachsemester
	 * @param string $email  The student's email
	 * @param string $ingroup  The student's group
	 *
	 * @return mixed in case of success an int (the student's id) is returned. Otherwise a list failures is returned.
	 *
	 * @todo The adding of scores relies only on the student's familyname and givenname. Thus, the rare case that two students share the same name pair is unacceptable. We therefore should check for this case and if necessary automatically change the second student's name by adding some numbering in order ok making the names unique.
     */
	public static function addStudent($familyname,$givenname,$matrnr,$term,$email,$course,$ingroup){
		// We check the format of the given arguments.
		// Whenever the check of a filed failed, 
		$inputFailures = array();
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$inputFailures[] = "email";
		}
		if(preg_match('/^[a-zA-ZÄÖÜäöüßÈèÉé-]+$/', $familyname)==0){
			$inputFailures[] = "familyname";
		}
		if(preg_match('/^[a-zA-ZÄÖÜäöüßÈèÉé-]+$/', $givenname)==0){
			$inputFailures[] = "givenname";
		}
		if(preg_match('/^[0-9]+$/', $matrnr)==0){
			$inputFailures[] = "matrnr";
		}
		if(preg_match('/^[0-9]+$/', $term)==0){
			$inputFailures[] = "term";
		}
		// TODO: Check validity of the course data
		/*if(!ereg('^[a-zA-Z0-9ÄÖÜäöüßÈèÉé]+$', $course)){
			$inputFailures[] = "course";
		}*/


		// check whether it is possible to register this student in this group:
		$group = new Groups($ingroup);
		$seats = $group->freeSeats();
		if($seats == 0){
			$inputFailures[] = "group";
		}

		if(count($inputFailures)>0){
			return $inputFailures;
		}

		// Load the corresponding dataset (in write-mode)
		$students = new Dataset('students',true);

		// Check whether the same names are already used
		$matches = 0;
		foreach($students->dom->getElementsByTagName("student") as $student){
			$cur_givenname = $student->getAttribute("givenname");
			$cur_familyname = $student->getAttribute("familyname");
			if($givenname.$familyname == $cur_givenname.$cur_familyname){
				$matches++;
				break;
			}
		}
		if($matches > 0){
			$inputFailures[] = "DUPLICATE";
			Logger::log("student.php addStudent(): Tried to register a duplicate of '$givenname $familyname'.",Logger::LOGLEVEL_WARNING);
			return $inputFailures;
		}

		// create a new student-node and append that node to the dataset
		$nodeStudent = $students->dom->createElement('student');
		// the new node gets a unique id as attribute 'id'
		$newid = uniqid(true);
		$nodeStudent->setAttribute("id",$newid);
		// add the information
		$nodeStudent->setAttribute("ingroup",$ingroup);
		$nodeStudent->setAttribute("familyname",$familyname);
		$nodeStudent->setAttribute("givenname",$givenname);

		$matrnr_enc = Crypto::encrypt_for_team($matrnr);
		if($matrnr_enc===false){
			return false;
		}
		$nodeStudent->setAttribute("matrnr",$matrnr_enc);

		$nodeStudent->setAttribute("term",$term);

		$email_enc = Crypto::encrypt_for_team($email);
		if($email_enc===false){
			return false;
		}
		$nodeStudent->setAttribute("email",$email_enc);

		$nodeStudent->setAttribute("course",$course);
		$students->dom->getElementsByTagName("students")->item(0)->appendChild($nodeStudent);

		// save the changes
		$students->save();

		// return the generated id.
		return $newid;				
	}



	/**
	 * Generate a Json-list of students filtered by $query.
	 *
	 * Each item contains the field id containing the student's unique id and a field 'label' formated according to $format.
	 * If $format=='FGM', the label is familyname, givenname (matrnr).  If $format=='FG', the label is familyname, givenname.
	 * 
	 * @param string $format Describes the formatation of the label-field as mentioned above.
	 * @param string $query Only those labels containing $query are pushed to the returned list.
	 *
	 * @return string The Json-list.
	 *
	 * @todo Error handling.
	*/
	public static function findStudentsJson($format,$query,$auth){
		// Load the corresponding dataset (in read-mode)
		$students = new Dataset('students',false);
	
		$list = array();
		
		// iterate over students
		foreach($students->dom->getElementsByTagName("student") as $student){
			// TODO Error handling
			$givenname = $student->getAttribute("givenname");
			$familyname = $student->getAttribute("familyname");

			$matrnr_dec = Crypto::decrypt_team($student->getAttribute("matrnr"),$auth);
			if($matrnr_dec===false){
				Logger::log("Matrnr decryption failure",Logger::LOGLEVEL_ERROR);
				return "{\"success\":\"no\",\"errmsg\":\"data failure\"}";
			}
			$matrnr = $matrnr_dec;
			$uid = $student->getAttribute("id");

			switch($format){
				case 'FGM':
					$querystring = "$familyname, $givenname ($matrnr)";
					break;
				case 'FG':
					$querystring = "$familyname, $givenname";
					break;
				default:
					$querystring = "NIL";
			}

			if((stripos($querystring,$query) !== FALSE)){
				$item['label'] = $querystring;
				$item['id'] = $uid;
				$list[] = $item;
			}
		}

		// we return a list in json-format
		return json_encode($list);
	}



	public static function getStudentsInGroup($groupid){
		// Load the corresponding dataset (in read-mode)
		$students = new Dataset('students',false);

		$list = array();
		// iterate over students
		foreach($students->dom->getElementsByTagName("student") as $student){
			if($student->getAttribute("ingroup")==$groupid){

				$cur_stud = new Student($student->getAttribute("id"));
				$item['familyname'] = $cur_stud->getField("familyname");
				$item['givenname'] = $cur_stud->getField("givenname");
				$item['email'] = $cur_stud->getField("email");

				$list[]=$item;
			}
		}

		$returner['success']='yes';
		$returner['list']=$list;

		// we return a list in json-format
		return json_encode($returner);

	}




	/**
     * Generate a Json-list containing all data of all students.
	 *
	 * @param auth the user's authentification object
	 * @return string The Json-list.
	 *
	 * @todo Error handling.
	*/
	public static function getAllStudentsJson($auth){
		// Get a list of all groups for later lookup.
		// TODO: What happpens, when this fails?
		$groups = Groups::getAllGroups();
		$groupLookUp = array();
		for($i = 0; $i<count($groups); $i++){
			$groupLookUp[$groups[$i]['groupid']] = $groups[$i]['name'];
		}

		// Load the corresponding dataset (in read-mode)
		$students = new Dataset('students',false);

		$list = array();
		// iterate over students
		foreach($students->dom->getElementsByTagName("student") as $student){
			$curStudID = $student->getAttribute("id");	
			$item["givenname"] = $student->getAttribute("givenname");
			$item["familyname"] = $student->getAttribute("familyname");

			$matrnr_dec = Crypto::decrypt_team($student->getAttribute("matrnr"),$auth);
			if($matrnr_dec===false){
				Logger::log("Matrnr decryption failure",Logger::LOGLEVEL_ERROR);
				return "{\"success\":\"no\",\"errmsg\":\"data failure\"}";
			}
			$item["matrnr"] = $matrnr_dec;

			$item["term"] = $student->getAttribute("term");
			$item["course"] = $student->getAttribute("course");

			$email_dec = Crypto::decrypt_team($student->getAttribute("email"),$auth);
			if($email_dec===false){
				Logger::log("Email decryption failure",Logger::LOGLEVEL_ERROR);
				return "{\"success\":\"no\",\"errmsg\":\"data failure\"}";
			}
			$item["email"] = $email_dec;

			$item["id"] = $student->getAttribute("id");
			$item["ingroupid"] = $student->getAttribute("ingroup");
			$item["ingroup"] = $groupLookUp[$student->getAttribute("ingroup")];

			// get the sheets related information
			// Load the corresponding dataset (in read-mode)
			$sheets = new Dataset('sheets',false);
			$item["totalscore"] = 0;
			$item["nrhandedin"] = 0;
			foreach($sheets->dom->getElementsByTagName("student") as $curSheetsStud){
				if( $curSheetsStud->getAttribute("uid") == $curStudID ){
					$curSheetNode = $curSheetsStud->parentNode;
					$curScoreDec = Crypto::decrypt_in_team($curSheetNode->getAttribute("score"),$auth);
					if( $curScoreDec===false ){
						Logger::log("Score decryption of sheet ".$curSheetNode->getAttribute("score")." failed.",Logger::LOGLEVEL_ERROR);
						return "{\"success\":\"no\",\"errmsg\":\"data failure\"}";
					}
					$item["totalscore"] += $curScoreDec;
					$item["nrhandedin"]++;
				}
			}

			$list[] = $item;
		}
		return json_encode($list);
	}

}

?>
