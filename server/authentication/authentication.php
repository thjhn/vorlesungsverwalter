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
 *  This file contains everything needed for authenticate users.
 */


class Authentication{
	var $logged_in, $username, $realname, $roles;

	// Here comes the constructur function
	function Authentication(){
		// First, check whether the user should be logged in.
		if($_SESSION['logged_in']){
			// Checking whether the authentication is valid.
			if($_SESSION['remote_addr'] == $_SERVER['REMOTE_ADDR']){
				$this->logged_in = true;
				$this->username = $_SESSION['username'];
				$this->realname = $_SESSION['realname'];
				$this->privkeykey = $_SESSION['privkeykey'];
				$this->roles = $_SESSION['roles'];
			}else{
				// User's IP has change => logout!
				$this->logged_in = false;
				$_SESSION['logged_in'] = false;
			}
		}else{
			// The users is not logged in.
			$this->logged_in = false;
		}
	}

	// Function says whether the users is logged in.
	function in(){
		return $this->logged_in;
	}

	// return an array containing all the user's roles
	function getRolesArray(){
		$tmp = explode(";",$this->roles);
		$tmp[] = 'anybody';
		return array_unique($tmp);
	}

	function hasRole($role){
		$roles = $this->getRolesArray();
		for($i=0;$i<count($roles);$i++){
			if($roles[$i]==$role){
				return true;
			}
		}
		return false;
	}

	// Trys to login the users.
	function login($username, $password){
		// load user and check for success:
		$user = new Users($username,False);
		if($user->username != $username){
			return "{\"success\":\"no1\"}";
		}

		// try to get the decrypted key.
		$decrypted_privkeykey = $user->getDecryptedPrivKeyKey($password);
		if($decrypted_privkeykey === False){
			return "{\"success\":\"no2\"}";
		}
		

		// now we passed the password barrier!
		$this->privkeykey = $decrypted_privkeykey;
		$this->logged_in = true;
		$this->username = $username;
		$this->realname = $user->getRealname();

		// load the user's roles
		$this->roles = $user->getRoleList();

		// store everything in SESSIONS.		
		$_SESSION['logged_in']=true;
		$_SESSION['remote_addr']=$_SERVER['REMOTE_ADDR'];
		$_SESSION['username']=$username;
		$_SESSION['realname']=$this->realname;
		$_SESSION['roles']=$this->roles;
		$_SESSION['privkeykey']=$this->privkeykey;

		return("{\"success\":\"yes\"}");
	}

	// Logout.
	function logout(){
		$_SESSION['logged_in']=false;
		return true;
	}

	/**
	 * Return the user's username or FALSE if the user is not logged in
	 *
	 * @return See description.
	 */
	function getUsername(){
		if($this->logged_in){
			return $this->username;
		}else{
			return FALSE;
		}
	}

	/**
	 * Return the user's realname or FALSE if the user is not logged in
	 *
	 * @return See description.
	 */
	function getRealname(){
		if($this->logged_in){
			return $this->realname;
		}else{
			return FALSE;
		}
	}


	// This function returns the current loginstate
	function loginstate(){
		if($this->logged_in){
			return "{\"success\":\"yes\",\"status\":\"in\",\"username\":\"".$this->username."\",\"realname\":\"".$this->realname."\",\"roles\":\"".$this->roles."\"}";
		}else{
			return "{\"success\":\"yes\",\"status\":\"out\"}";
		}
	}
/***---***/
	// Change the user's password (provided that she is logged in)
	function change_password($oldpasswd,$newpasswd){
		if($this->logged_in){
			// for password changes we need write access.
			$users = new Dataset("users",true);
			// we look for the user:
			foreach($users->dom->getElementsByTagName("user") as $cur_user){
				$namenodes = $cur_user->getElementsByTagName("username");
				if($namenodes->length == 1){
					if($namenodes->item(0)->nodeValue == $this->username){

						$passwordnodes = $cur_user->getElementsByTagName("privkeykey");
						if($passwordnodes->length == 1){
								// we encrypt the private key key with the password provided by the user.
								// if this is successfull the user will be logged in.
								$decrypted_privkeykey = Crypto::decrypt_privkeykey($passwordnodes->item(0)->nodeValue,$oldpasswd);
								if($decrypted_privkeykey === false){
									return ["PASSWORT_REJECTED"];
								}

								$newprivkeykey = Crypto::encrypt_privkeykey($newpasswd,$this);
								if($newprivkeykey === false){
									return ["INTERNAL"];
								}

								$passwordnodes->item(0)->nodeValue = $newprivkeykey;
								$users->save();
								return [];
						}
					}
				}
			}
		}
		return ["INTERNAL"];
	}
}

?>	
