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
 *  Contains the class groups.
 */

/**
 * Provides the necessary information about a group.
 * @author Thomas Jahn vv3@t-und-j.de
 */

class Groups{
	var $groupid,$editable,$groupnode;

	/**
     * The constructor loads the group using its unique id.
	 * 
	 * @param string $groupid The groups's id
	 * @param boolean $editable If 'true', the values are changeable. If 'false' they are read-only.
	*/
	function Groups($groupid,$editable=false){
		$this->groupid = $groupid;
		$this->editable = $editable;

		// load the users-dataset
		$this->dataset = new Dataset("groups",$this->editable);
	
		// find the group by id.
		$matches = 0;
		foreach($this->dataset->dom->getElementsByTagName("group") as $cur_group){
			if($cur_group->getAttribute('id') == $groupid){
				// $cur_group is the group we were looking for
				$matches++;
				$this->groupnode  = $cur_group;
				break;
			}
		}
		// if we haven't found that user
		if($matches == 0){
			$this->groupid = "";
		}
	}

	public function isLoaded(){
		if($this->groupid == ''){
			return false;
		}
		return true;
	}

	/**
	 * Add a group with a newly generated id.
	 * The values of the new group are in no way specified. You sould call saveChanges afterwards!
	 *
	 * @return the id of the newly generated group
	*/	
	public static function addGroup(){
		//load the sheets dataset in write-mode
		$groups = new Dataset('groups',true);

		// create a new score-node and append that node to the dataset
		$newid = uniqid(true);
		$nodeGroup = $groups->dom->createElement('group');		
		$nodeGroup->setAttribute("id",$newid);
		$groups->dom->childNodes->item(0)->appendChild($nodeGroup);
		$groups->save();
		return $newid;
	}

	/**
     * Try to get the value of a field.
	 * 
	 * @param string $fieldname The name of the field.
	 *
	 * @return mixed returns false if the required field is not present. It returns the value of the first node with that tagename else.
	 *
	 * @todo check whether loading a group was successfully before!
	*/
	function getField($fieldname){
		if($this->groupid != ""){
			// Check for existence?
			return $this->groupnode->getAttribute($fieldname);
		}else{
			Logger::log("groups.php method getField was called for a nonexisting group.",Logger::LOGLEVEL_ERROR);
			return FALSE;
		}
	}

	public function getGroupJson(){
		if($this->groupid != ""){
			$returner['success']='yes';
			$returner['groupid']=$this->groupid;
			$returner['name']=$this->getField("name");			
			$returner['description']=$this->getField("description");
			$returner['seats']=$this->getField("seats");
		}else{
			$returner['success']='no';
			$returner['errormsg']="The given group is illegal.";
		}
		return json_encode($returner);
	}


	public function getStudentsJson(){
		return Student::getStudentsInGroup($this->groupid);
	}

	/**
	 * Change certain values of that group
	 * 
	 * @param array $changes A list of changes to be made. Each item is an associative array with fields 'field' and 'newvalue'
	 *
	 * @return boolean Was saving changes successful?
	 */
	function saveChanges($changes){
		if($this->editable){
			if($this->groupid != ""){
				for($i=0;$i<count($changes);$i++){
					if($changes[$i]['field'] != 'groupid'){
						$node = $this->groupnode->setAttribute($changes[$i]['field'],$changes[$i]['newvalue']);
					}
				}
				$this->dataset->save();
				return true;
			}else{
				Logger::log("Tried to save changes for a not existing group!",Logger::LOGLEVEL_ERROR);
				return false;
			}
		}else{
			Logger::log("Tried to save changes in read-only mode!",Logger::LOGLEVEL_ERROR);
				return false;
		}
	}

	public function freeSeats(){
		if($this->groupid != ""){
			$seats = $this->getField("seats");

			if($seats == "inf"){
				return -1;
			}

			$seats = $seats - Student::getStudentsInGroupCount($this->groupid);
			if($seats < 0){
				return 0;
			}

			return $seats;
		}else{
			return 0;
		}
	}

	/**
	 * Returns a json object containing some information about each group
	 *
	 * @return the json
	 */
	public static function getAllGroupsJson(){
		// Load the corresponding dataset (in read-mode)
		$groups = new Dataset('groups',false);
		$list = array();
		// iterate over group
		foreach($groups->dom->getElementsByTagName("group") as $group){
			// TODO Error handling
			$item["groupid"] = $group->getAttribute("id");
			$item["name"] = $group->getAttribute("name");
			$item["description"] = $group->getAttribute("description");
			$item["seats"] = $group->getAttribute("seats");
			$item["students"] = Student::getStudentsInGroupCount($item["groupid"]);

			/*$rolelist = array();
			foreach($user->getElementsByTagName("role") as $role){
				$rolelist[] = $role->nodeValue;
			}
			$item["rolelist"] = $rolelist;*/
			$list[] = $item;
		}
		return json_encode($list);
	}

	/**
	 * Returns an array containing some information about each group
	 *
	 * @return the array
	 */
	public static function getAllGroups(){
		// Load the corresponding dataset (in read-mode)
		$groups = new Dataset('groups',false);

		$list = array();
		// iterate over group
		foreach($groups->dom->getElementsByTagName("group") as $group){
			// TODO Error handling
			$item["groupid"] = $group->getAttribute("id");
			$item["name"] = $group->getAttribute("name");
			$item["description"] = $group->getAttribute("description");
			$item["seats"] = $group->getAttribute("seats");
			$item["students"] = Student::getStudentsInGroupCount($item["groupid"]);

			/*$rolelist = array();
			foreach($user->getElementsByTagName("role") as $role){
				$rolelist[] = $role->nodeValue;
			}
			$item["rolelist"] = $rolelist;*/

			$list[] = $item;
		}

		return $list;
	}
}


?>
