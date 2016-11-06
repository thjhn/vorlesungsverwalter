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
 *  ---
 *  Contains the class Tool.
 */

/**
 * Provides the necessary information about a tool.
 *
 * @author Thomas Jahn vv3@t-und-j.de
 */
class Tool{
	var $available,$key,$template,$title,$auth;

	/**
     * The constructor loads the necessary information.
	 * 
	 * @param string $key The key of the desired tool.
	 * @param Auth $auth The user's authentication object.
	 *
	 * @todo check whether there exists a directory for this key...
	*/
	function Tool($key,$auth){
		// TODO:check whether there exists a directory for this key.
		$this->key = $key;
		$this->auth = $auth;

		// Load Config
		$config_file = $this->key."/config.xml";
		$config_dom = new DOMDocument();
		$ret = $config_dom->load($config_file);
		$this->title = $config_dom->getElementsByTagName("title")->item(0)->nodeValue;

		// Check, whether this tool is available for the current user
		$usersroles = $auth->getRolesArray();
		$matches = 0;
		foreach($config_dom->getElementsByTagName("role") as $rolenode){
			if( in_array($rolenode->nodeValue, $usersroles) ){
				$matches++;
				break;
			}
		}

		if($matches > 0){
			$this->available = true;

			// Load the template
			include_once($this->key."/template.php");
			$call = "generate_".$this->key."_template";
			$this->template = $call();

		}else{
			$this->available = false;
		}
		

	}

	/**
     * Return the javascript file of the current tool
	 * 
	 * @todo check file existence
	 * 
	 * @return string A string containing the javascript files
	*/
	function getScript(){
		if($this->available){
			// TODO: Check file existence
			$file = $this->key."/scripts.js";
			$fp = fopen($file,"r");
			$ret = fread($fp,filesize($file));
			fclose($fp);
			return $ret;
		}
	}

	/**
     * 	Returns all keys of tools available for the requestes user.
	 * 
	 * @param Auth $auth The user's authentication object
	 * @todo ERRORhandling
	 * 
	 * @return string[] List of keys.
	*/
	public static function getAvailableTools($auth){
		$usersroles = $auth->getRolesArray();
		$ret = "";

		// Load Config
		$config_file = "../data/config.xml";
		$config_dom = new DOMDocument();
		$config_dom->load($config_file);

		$modules = "";
		foreach($config_dom->getElementsByTagName("module") as $mod){
			$d[] = $mod->getAttribute("order");
			$e[] = $mod->getAttribute("key");
		}

		array_multisort($d,$e);

		for($i=0;$i<count($d);$i++){
		//	$tmpl .= $e[$i]."-<br>";

			// Load Config
			$config2_file = $e[$i]."/config.xml";
//echo $config2_file;
			$config2_dom = new DOMDocument();
			// TODO: ERRORhandling
			$config2_dom->load($config2_file);
			$matches = 0;
			foreach($config2_dom->getElementsByTagName("role") as $role){
				if(in_array($role->nodeValue,$usersroles)){
		            $ret[]= $e[$i];							
					break;
				}
			}			


		}



	/*	if ($handle = opendir('server/tools/')) {
			while (false !== ($entry = readdir($handle))) {
	    	    if ($entry != "." && $entry != ".." && is_dir('server/tools/'.$entry)) {
					// Load Config
					$config_file = "server/tools/".$entry."/config.xml";
					$config_dom = new DOMDocument();
					// TODO: ERRORhandling
					$config_dom->load($config_file);
					$matches = 0;
					foreach($config_dom->getElementsByTagName("role") as $role){
						if(in_array($role->nodeValue,$usersroles)){
		    		        $ret[]= $entry;							
							break;
						}
					}
    		    }
			}
	    }*/
    	closedir($handle);
		return $ret;
	}
 
	/**
     * Check whether this tool is available for the current user.
	 * 
	 * @return bool Boolean value whether the tool is available.
	*/
	function available(){
		return $this->available;
	}

	function getTitle(){
		if($this->available){
			return $this->title;
		}else{
			return FALSE;
		}
	}

	/**
     * Get Information about this tool.
	 *
	 * Returns a Json-Object containing information about this tool.
	 * 
	 * @return string A Json-object containing the field status which contains the values 'ok' or 'prohbited' depending on the avaibility of this tool. If the tool is available, there is a field 'title' containing the title of the tool and there is a field 'template' containing the template of this tool (the template ist base64 encoded).
	*/
	function getInfo(){
		if($this->available){
			return "{\"status\":\"ok\",\"title\":\"".$this->title."\",\"template\":\"".base64_encode($this->template)."\"}";
		}else{
			return "{\"status\":\"prohibited\"}";
		}
	}
};
