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
	 * Check whether the object contains data of an user
	 *
	 * @return bool true if user was loaded correctly. else false
	 */
	public function loaded(){
		return ($this->username != "");
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
         * Set the user's realname
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $realname the new value
	 *
	 * @return boolean true on success, false on failure
	 */
	function setRealname($realname){
		if(!$this->loaded()) return False;
		$this->usernode->setAttribute("realname",$realname);
		return True;
	}



	/**
         * Is the user enabled?
	 *
	 * @param $yesnoformated returns 'yes' and 'no' instead of bool val.
	 *
	 * @return boolean state or 'yes' and 'no' (see parameters)
	 */
	function getEnabled($yesnoformated = False){
		if(!$this->loaded()) return False;

		$state = $this->usernode->getAttribute("enabled");
		if($yesnoformated){
			if($state == 'true'){
				return 'yes';
			}else{
				return 'no';
			}
		}else{
			if($state == 'true'){
				return true;
			}else{
				return false;
			}
		}

	}

	/**
         * Set the user's enabled state
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $state 'yes' or 'no'
	 *
	 * @return boolean true on success, false on failure
	 */
	function setEnabled($state){
		if(!$this->loaded()) return False;
		if($state == 'yes'){
			$this->usernode->setAttribute("enabled","true");
		}else{
			$this->usernode->setAttribute("enabled","false");
		}
		return True;
	}



	/**
         * Is the user a corrector?
	 *
	 * @param $yesnoformated returns 'yes' and 'no' instead of bool val.
	 *
	 * @return boolean state or 'yes' and 'no' (see parameters)
	 */
	function getIsCorrector($yesnoformated = False){
		if(!$this->loaded()) return False;

		$state = $this->usernode->getAttribute("is_corrector");
		if($yesnoformated){
			if($state == 'true'){
				return 'yes';
			}else{
				return 'no';
			}
		}else{
			if($state == 'true'){
				return true;
			}else{
				return false;
			}
		}
	}

	/**
         * Set the user's corrector state
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $state 'yes' or 'no'
	 *
	 * @return boolean true on success, false on failure
	 */
	function setIsCorrector($state){
		if(!$this->loaded()) return False;
		if($state == 'yes'){
			$this->usernode->setAttribute("is_corrector","true");
		}else{
			$this->usernode->setAttribute("is_corrector","false");
		}
		return True;
	}



	/**
         * Is the user a developer?
	 *
	 * @param $yesnoformated returns 'yes' and 'no' instead of bool val.
	 *
	 * @return boolean state or 'yes' and 'no' (see parameters)
	 */
	function getIsDev($yesnoformated = False){
		if(!$this->loaded()) return False;

		$state = $this->usernode->getAttribute("is_dev");
		if($yesnoformated){
			if($state == 'true'){
				return 'yes';
			}else{
				return 'no';
			}
		}else{
			if($state == 'true'){
				return true;
			}else{
				return false;
			}
		}
	}



	/**
         * Is the user an admin?
	 *
	 * @param $yesnoformated returns 'yes' and 'no' instead of bool val.
	 *
	 * @return boolean state or 'yes' and 'no' (see parameters)
	 */
	function getIsAdmin($yesnoformated = False){
		if(!$this->loaded()) return False;

		$state = $this->usernode->getAttribute("is_admin");
		if($yesnoformated){
			if($state == 'true'){
				return 'yes';
			}else{
				return 'no';
			}
		}else{
			if($state == 'true'){
				return true;
			}else{
				return false;
			}
		}
	}

	/**
         * Set the user's admin state
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $state 'yes' or 'no'
	 *
	 * @return boolean true on success, false on failure
	 */
	function setIsAdmin($state){
		if(!$this->loaded()) return False;
		if($state == 'yes'){
			$this->usernode->setAttribute("is_admin","true");
		}else{
			$this->usernode->setAttribute("is_admin","false");
		}
		return True;
	}


	/**
         * Set the user's privkeykey
	 * The changes are not stored permanently. Call save() to
	 * actually store changes.
	 * @param $key the key
	 * @param $auth an Authentication object to get the common key
	 *
	 * @return boolean true on success, false on failure
	 */
	function setPassword($key,$auth){
		if(!$this->loaded()) return False;
		$this->usernode->setAttribute('privkeykey',
			Users::generatePrivKeyKey($key,$auth)
		);
		return true;
	}



	/**
         * Provides the semicolon separated list of the user's roles
	 *
	 * @return semicolon separated list of roles
	 */
	function getRoleList(){
		if(!$this->loaded()) return "";

		$rolelist = "";
		if($this->getIsAdmin()){
			$rolelist .= "admin;";
		}
		if($this->getIsCorrector()){
			$rolelist .= "corrector;";
		}
		if($this->getIsDev()){
			$rolelist .= "dev;";
		}
		return $rolelist;
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
			$retstr .= "\"realname\":\"".$this->getRealname()."\",";
			$retstr .= "\"is_corrector\":\"".$this->getIsCorrector(True)."\",";
			$retstr .= "\"is_admin\":\"".$this->getIsAdmin(True)."\",";
			$retstr .= "\"enabled\":\"".$this->getEnabled(True)."\"";
			$retstr .= "}";
			return $retstr;

		}else{
			// there is no student in this object
			return "{\"success\":\"no\"}";
		}
	}



	/**
	 * Generate a privkeykey.
	 * Encrypt the common key (which is taken from $auth)
	 * with the given password.
	 * 
	 * @param $password the password to be used
	 * @param $auth the authentication object
	 *
	 * @return the generated privkeykey
	 */
	public static function generatePrivKeyKey($password,$auth){
		return Crypto::encrypt_privkeykey($password,$auth);
	}



	/**
	 * Adds a new user.
	 * 
	 * @param string $username the new user's username
	 * @param string $password the new user's password
	 * @param string $realname the new user's real name
	 * @param string $is_corrector yes/no indicates a corrector.
	 * @param string $is_admin yes/no indicates an admin.
	 * @param Auth $auth the current user's auth object.
	 *
	 * @todo add roles 
	 */
	public static function addNewUser($username, $password, $realname, $enabled, $is_corrector, $is_admin, $auth){
		// before adding a new user we try to load it
		$user = new Users($username,false);
		if($user->loaded()){
			return false;
		}
		unset($user);

		// load the users-dataset
		$users = new Dataset("users",true);
		$nodeUser = $users->dom->createElement('user');
		$nodeUser->setAttribute('username',$username);
		$nodeUser->setAttribute('realname',$realname);
		$nodeUser->setAttribute('privkeykey',Users::generatePrivKeyKey($password,$auth));
		if($enabled == 'yes'){
			$nodeUser->setAttribute('enabled',"true");
		}else{
			$nodeUser->setAttribute('enabled',"false");
		}
		if($is_admin == 'yes'){
			$nodeUser->setAttribute('is_admin',"true");
		}else{
			$nodeUser->setAttribute('is_admin',"false");
		}
		if($is_corrector == 'yes'){
			$nodeUser->setAttribute('is_corrector',"true");
		}else{
			$nodeUser->setAttribute('is_corrector',"false");
		}

		$users->dom->childNodes->item(0)->appendChild($nodeUser);
		$users->save();
                Logger::log("Added a node for user $username to the users dataset.",Logger::LOGLEVEL_VERBOSE);
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
			$item["username"] = $user->getAttribute("username");
			$item["realname"] = $user->getAttribute("realname");
			if($user->getAttribute("is_corrector")=="true"){
				$item["is_corrector"] = "yes";
			}else{
				$item["is_corrector"] = "no";
			}
			if($user->getAttribute("is_admin")=="true"){
				$item["is_admin"] = "yes";
			}else{
				$item["is_admin"] = "no";
			}
			if($user->getAttribute("enabled")=="true"){
				$item["enabled"] = "yes";
			}else{
				$item["enabled"] = "no";
			}

			$list[] = $item;
		}
		return json_encode($list);
	}
}


?>
