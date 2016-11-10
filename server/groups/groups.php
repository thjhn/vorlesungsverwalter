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

		// load the groups-dataset and then check whether this was successful.
		$this->dataset = new Dataset("groups",$this->editable);
		if(!$this->dataset->loaded){
			$this->groupid = ""; // an empty groupid indicates that no data was loaded.
			Logger::log("groups.php tried to load group $groupid but dataset was not loaded properly. Giving up.",Logger::LOGLEVEL_ERROR);
		}else{
			$nodes_array = $this->dataset->getNodeByAttribute("group", "id", $groupid);
			if( count($nodes_array)==0 ){
				// we haven't found a matching node
				$this->groupid = "";
				Logger::log("groups.php tried to load group $groupid but no matching node was found.",Logger::LOGLEVEL_VERBOSE);
			}else{
				// we assume that the array contains only one node. Thus we
				// simply take the first one.
				$this->groupnode = $nodes_array[0];
				
				// since we already have loaded all matching nodes, we check
				// whether there are more than one matching nodes:
				if( count($nodes_array) > 1 ){
					Logger::log("While loading group $gropuid we noticed that there are multiple matching nodes.",Logger::LOGLEVEL_FATAL);
				}
			}
		}
	}



	/**
	 * Check whether the object contains data of a group
	 *
	 * @return bool true if data was loaded correctly. else false
	 */
	public function loaded(){
		if($this->groupid == ''){
			return false;
		}
		return true;
	}
	/**
	 * Synonym for loaded().
	 * For historic reasons.
	 */
	public function isLoaded(){
		return loaded();
	}



	/**
	 * Save changes permanently.
	 * 
	 * @return bool true on success.
	 */
	function save(){
		if(!$this->loaded()) return false;

		$this->dataset->save();
		return true;
	}



	/**
         * Provides the group's name
	 *
	 * @return name
	 */
	function getName(){
		if(!$this->loaded()) return "";
		return $this->groupnode->getAttribute("name");
	}

	/**
         * Set the groups's name
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $name the new value
	 *
	 * @return boolean true on success, false on failure
	 */
	function setName($name){
		if(!$this->loaded()) return False;

		$this->groupnode->setAttribute("name",$name);
		return True;
	}



	/**
         * Provides the group's description
	 *
	 * @return description
	 */
	function getDescription(){
		if(!$this->loaded()) return "";
		return $this->groupnode->getAttribute("description");
	}

	/**
         * Set the groups's description
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $description the new value
	 *
	 * @return boolean true on success, false on failure
	 */
	function setDescription($description){
		if(!$this->loaded()) return False;

		$this->groupnode->setAttribute("description",$description);
		return True;
	}



	/**
         * Provides the nr of seats
	 *
	 * @return seats
	 */
	function getSeats(){
		if(!$this->loaded()) return "";
		return $this->groupnode->getAttribute("seats");
	}

	/**
         * Set the nr of seats
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $seats the new value
	 *
	 * @return boolean true on success, false on failure
	 */
	function setSeats($seats){
		if(!$this->loaded()) return False;

		$this->groupnode->setAttribute("seats",$seats);
		return True;
	}



	/**
	 * Add a group with a newly generated id.
	 *
	 * @param $groupname name of the group
	 * @param $description the description of the group
	 * @param $seats no of seats
	 *
	 * @return the id of the newly generated group
	 */	
	public static function addGroup($groupname, $description, $seats){
		//load the sheets dataset in write-mode
		$groups = new Dataset('groups',true);

		// create a new score-node and append that node to the dataset
		$newid = uniqid(true);
		$nodeGroup = $groups->dom->createElement('group');		
		$nodeGroup->setAttribute("id",$newid);
		$nodeGroup->setAttribute("name",$groupname);
		$nodeGroup->setAttribute("description",$description);
		$nodeGroup->setAttribute("seats",$seats); // TODO: Validate field
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
	 * DEPRECATED
	*/
	function getField($fieldname){
		Logger::log("Deprecated function getField in groups.php called.",Logger::LOGLEVEL_WARNING);
		if($this->groupid != ""){
			// Check for existence?
			return $this->groupnode->getAttribute($fieldname);
		}else{
			Logger::log("groups.php method getField was called for a nonexisting group.",Logger::LOGLEVEL_ERROR);
			return FALSE;
		}
	}


	
	/**
	 * get a JSON with information about the group
	 *
	 * @return an json-object
	 */
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

			$list[] = $item;
		}

		return $list;
	}
}


?>
