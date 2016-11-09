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
 *  Contains the class Users.
 */


/**
 * Provides the necessary information about a user.
 *
 * @author Thomas Jahn vv3@t-und-j.de
 */
class Users{
	var $username,$editable,$usernode;

	/**
         * The constructor loads the users using their username.
	 * 
	 * @param string $username The user's name
	 * @param boolean $editable If 'true', the values are changeable. If 'false' they are read-only.
	*/
	function Users($username,$editable=false){
		$this->username = $username;
		$this->editable = $editable;

		// load the users-dataset and then check whether this was successful.
		$this->dataset = new Dataset("users",$this->editable);
		if(!$this->dataset->loaded){
			$this->username = ""; // an empty username indicates that no data was loaded.
			Logger::log("user.php tried to load user $username but dataset was not loaded properly. Giving up.",Logger::LOGLEVEL_ERROR);
		}else{
			$nodes_array = $this->dataset->getNodeByAttribute("user", "username", $username);
			if( count($nodes_array)==0 ){
				// we haven't found a matching node
				$this->username = "";
				Logger::log("user.php tried to load user $username but no matching node was found.",Logger::LOGLEVEL_VERBOSE);
			}else{
				// we assume that the array contains only one node. Thus we
				// simply take the first one.
				$this->usernode = $nodes_array[0];
				
				// since we already have loaded all matching nodes, we check
				// whether there are more than one matching nodes:
				if( count($nodes_array) > 1 ){
					Logger::log("While loading user $username we noticed that there are multiple matching nodes.",Logger::LOGLEVEL_FATAL);
				}

			}
		}

	}

	/**
     * Try to get the value of a field.
	 * 
	 * @param string $tagname The name of the field.
	 *
	 * @return mixed returns false if the required field is not present. It returns the value of the first node with that tagename else.
	 *
	 * @todo check whether loading a user was successfully before!
	 * DEPRECATED.
	*/
	function getField($tagname){
		$nodes = $this->usernode->getElementsByTagName($tagname);
		if($nodes->length > 0){
			return $nodes->item(0)->nodeValue;
		}else{
			return FALSE;
		}
	}

	/**
         * Provides the user's decrypted privkey.
	 *
	 * @return the key or false if any error occurs.
	 */
	function getDecryptedPrivKeyKey($password){
		if(!$this->loaded()) return false;
		return Crypto::decrypt_privkeykey(
			$this->usernode->getAttribute("privkeykey"),
			$password
		);
	}


	/**
         * Provides the user's realname
	 *
	 * @return realname
	 */
	function getRealname(){
		if(!$this->loaded()) return "";
		return $this->usernode->getAttribute("realname");
	}


	/**
         * Provides the semicolon separated list of the user's roles
	 *
	 * @return semicolon separated list of roles
	 */
	function getRoleList(){
		if(!$this->loaded()) return "";
		$rolelist = "";
		foreach($this->usernode->getElementsByTagName("role") as $role){
			$rolelist .= $role->getAttribute('rolename').";";
		}
		return $rolelist;
	}


	/**
	 * Check whether the object contains data of an user
	 *
	 * @return bool
	*/
	public function loaded(){
		return ($this->username != "");
	}

	/**
	 * Generates an array containing all the user's rolse.
	 *
	 * @return the generated array or false on error
	 * @todo needs rewriting.
	*/
	private function getRoleListArray(){
		if($this->username != ""){
			$rolelist = array();
			foreach($this->usernode->getElementsByTagName("role") as $role){
				$rolelist[] = $role->nodeValue;
			}	
			return $rolelist;
		}else{
			return false;
		}
	}

	/**
     * Get most of the data about the User as a Json-object.
	 * 
	 * The Json contains a field 'success' which is 'yes' if there is a student loaded. It is 'no' else.
	 *
	 * @return string Returns the json object. If successfully, it currently contains the fields 'username', 'realname', ....
	*/
	function getDataJson(){
		if($this->username != ""){
			$rolelist = $this->getRoleList();

			$retstr  = "{";
			$retstr .= "\"success\":\"yes\",";
			$retstr .= "\"username\":\"".$this->username."\",";
			$retstr .= "\"realname\":\"".$this->getField("realname")."\",";
			$retstr .= "\"rolelist\":".json_encode($rolelist).",";
			$retstr .= "\"enabled\":\"".$this->getField("enabled")."\"";
			$retstr .= "}";
			return $retstr;
		}else{
			// there is no student in this object
			return "{\"success\":\"no\"}";
		}
	}

	/**
     * Check whether the current user has a specific role.
	 * 
	 * @param $role a string containing the role's name.
	 *
	 * @return true or false.
	 * @todo needs rewriting.
	*/
	function hasRole($role){
		if($this->username != ""){
			$rolelist = $this->getRoleList();
			
			for($i = 0; $i<count($rolelist); $i++){
				if($rolelist[$i] == $role){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Change certain values of that user
	 * 
	 * @param array $changes A list of changes to be made. Each item is an associative array with fields 'field' and 'newvalue'
	 * @param $auth The current user's authentication object
	 *
	 * @return boolean Was saving changes successful?
	 */
	function saveChanges($changes,$auth){
		Logger::log("users.php: saveChanges() called.",Logger::LOGLEVEL_VERBOSE);
		if($this->editable){
			if($this->username != ""){
				for($i=0;$i<count($changes);$i++){
					// if the password is the field that is to be changed, we do not save the password itself.
					if($changes[$i]['field'] == 'password'){
						Logger::log("users.php: saveChanges() was called for a password.",Logger::LOGLEVEL_VERBOSE);
						$changes[$i]['field'] = 'privkeykey';
						$changes[$i]['newvalue'] = Crypto::encrypt_privkeykey($changes[$i]['newvalue'],$auth);
						if($changes[$i]['newvalue'] === false){
							Logger::log("users.php; Failed encrypting privkeykey with a user's password",Logger::LOGLEVEL_ERROR);
							return false;
						}
					}

					$node = $this->usernode->getElementsByTagName($changes[$i]['field']);

					if($node->length > 0){
						if($node->length > 1){
							Logger::log("There are more than one ".$changes[$i]['field']."-nodes for student ".$this->uid,Logger::LOGLEVEL_WARNING);
						}

						$node->item(0)->nodeValue = $changes[$i]['newvalue'];
					}else{
/*						$newnode = $this->dataset->dom->createElement($changes[$i]['field'],$changes[$i]['newvalue']);
						$this->studentnode->appendChild($newnode);*/
					}
				}

				$this->dataset->save();
				return true;
			}else{
				Logger::log("Tried to save changes for a not existing student!",Logger::LOGLEVEL_ERROR);
				return false;
			}
		}else{
			Logger::log("Tried to save changes in read-only mode!",Logger::LOGLEVEL_ERROR);
				return false;
		}
	}

	/**
	 * Assign the given roles to the user and remove all roles assigned previously..
	 * 
	 * @param array $roles A list of roles the user should be assigned to.
	 *
	 * @return boolean Was saving changes successful?
	 */
	function changeRoles($roles){
		if($this->editable){
			if($this->username != ""){
				// Remove the old roles-node(s) and add a new one.
				foreach($this->usernode->getElementsByTagName("roles") as $rolenode){
					$rolenode->parentNode->removeChild($rolenode);
				}
				$newnode = $this->dataset->dom->createElement("roles");
				$this->usernode->appendChild($newnode);

				// Add the roles listed in $roles
				// TODO: Check whether the entries are 'legal'
				for($i=0;$i<count($roles);$i++){
					$newrole = $this->dataset->dom->createElement("role",$roles[$i]);
					$newnode->appendChild($newrole);
				}


					/*if($rolenode->length > 0){
						if($node->length > 1){
							Logger::log("There are more than one ".$changes[$i]['field']."-nodes for student ".$this->uid,Logger::LOGLEVEL_WARNING);
						}

						$node->item(0)->nodeValue = $changes[$i]['newvalue'];
					}else{
						$newnode = $this->dataset->dom->createElement($changes[$i]['field'],$changes[$i]['newvalue']);
						$this->studentnode->appendChild($newnode);
					}*/
				

				$this->dataset->save();
				return true;
			}else{
				Logger::log("Tried to save changes for a not existing student!",Logger::LOGLEVEL_ERROR);
				return false;
			}
		}else{
			Logger::log("Tried to save changes in read-only mode!",Logger::LOGLEVEL_ERROR);
				return false;
		}
	}

	/**
	 * Adds a new user.
	 * 
	 * @param string $username the new user's username
	 * @param string $password the new user's password
	 * @param string $realname the new user's real name
	 * @param array $roles the roles of the new user
	 * @param Auth $auth the current user's auth object.
	 * 
	*/
	public static function addNewUser($username, $password, $realname, $enabled, $roles, $auth){
		// before adding a new user we try to load it
		$user = new Users($username,false);
		if($user->loaded()){
			return false;
		}
		unset($user);

		// load the users-dataset
		$users = new Dataset("users",true);
		$nodeUser = $users->dom->createElement('user');
		$nodeUserName = $users->dom->createElement('username');
		$nodeUserName->nodeValue = $username;
		$nodeUser->appendChild($nodeUserName);
		$nodeUserName = $users->dom->createElement('realname');
		$nodeUserName->nodeValue = $realname;
		$nodeUser->appendChild($nodeUserName);
		$nodeEnabled = $users->dom->createElement('enabled');
		$nodeEnabled->nodeValue = $enabled;
		$nodeUser->appendChild($nodeEnabled);
		$nodeUserName = $users->dom->createElement('privkeykey');
		$nodeUser->appendChild($nodeUserName);
		$nodeUserName = $users->dom->createElement('roles');
		$nodeUser->appendChild($nodeUserName);
		
		$users->dom->childNodes->item(0)->appendChild($nodeUser);
		$users->save();
                Logger::log("Added a node for user $username to the users dataset.",Logger::LOGLEVEL_VERBOSE);

		// before loading the user again we have to free the lock!
		unset($users);

		$user = new Users($username,true);
		$user->changeRoles($roles);
		$user->saveChanges([array("field"=>"password", "newvalue"=>$password)],$auth);

		return true;
	}


	/**
	 * Returns a json object containing some information about each user
	 *
	 * @return the json
	 */
	public static function getAllUsersJson(){
		// Load the corresponding dataset (in read-mode)
		$users = new Dataset('users',false);

		$list = array();
		// iterate over students
		foreach($users->dom->getElementsByTagName("user") as $user){
			// TODO Error handling
			$item["username"] = $user->getElementsByTagName("username")->item(0)->nodeValue;
			$item["enabled"] = $user->getElementsByTagName("enabled")->item(0)->nodeValue;
			$item["realname"] = $user->getElementsByTagName("realname")->item(0)->nodeValue;

			$rolelist = array();
			foreach($user->getElementsByTagName("role") as $role){
				$rolelist[] = $role->nodeValue;
			}
			$item["rolelist"] = $rolelist;

			$list[] = $item;
		}
		return json_encode($list);
	}
}


?>
