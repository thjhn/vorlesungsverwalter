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
 *  Here we implement the server's interface to the user's requests.
 *  Any request is send by a users using this file.
 */

// We need to include some other files...
include_once('../server/dataset/dataset.php');
include_once('../server/authentication/authentication.php');
include_once('../server/users/users.php');
include_once('../server/groups/groups.php');
include_once('../server/exams/exams.php');
include_once('tool.php');
include_once('../server/student/student.php');
include_once('../server/sheet/sheet.php');

include_once('../server/logger/logger.php');

// Firstly, we try to authenticate the user. Therefore we need to start the session and create the Authentication object.
session_start();
$AUTH = new Authentication();

// Secondly, we decode the user's request.
$cmd = $_POST['cmd'];
$data = $_POST['data'];
switch($cmd){
	case 'GET_TOOL':	// will later be moved to gui server
	 	$TOOL = new Tool($_POST['data'],$AUTH);	
		print($TOOL->getInfo());
		break;
	case 'GET_TOOL_SCRIPT':	// will later be moved to gui server
	 	$TOOL = new Tool($_POST['data'],$AUTH);
		print($TOOL->getScript());
		break;
	case 'GET_LIST_OF_TOOLS':	// will later be moved to gui server
		print(json_encode(Tool::getAvailableTools($AUTH)));
		break;
	case 'GET_CONTAINER':	// will later be moved to gui server
		$filename = $_POST['data']."/template.tmpl";
		$fp = fopen($filename,"r");
		$container = fread($fp,filesize($filename));
		fclose($fp);
		print($container);		
		break;
	case 'GET_MENU':	// will later be moved to gui server
		$usersroles = $AUTH->getRolesArray();

		// Load Config
		$config_file = "../data/config.xml";
		$config_dom = new DOMDocument();

		if( $config_dom->load($config_file) === FALSE){
			Logger::log("gi.php: Got the GET_MENU cmd but was no able to load config.xml!",Logger::LOGLEVEL_ERROR);
			echo "{success='false',errmsg='Fataler Serverfehler. Bitte informiere den Administrator.'}";
			break;
		}
		// load the menu tags and post an error msg if there is none
		$menu = $config_dom->getElementsByTagName("menu");
		if( $menu->length <= 0 ){
			Logger::log("gi.php: There is no menu-tag in config.xml",Logger::LOGLEVEL_WARNING);
			echo "{success='false',errmsg='Menu not available.'}";
			break;
		}

		// iterate through all box-tags
		$boxes = array();
		foreach($menu->item(0)->getElementsByTagName("box") as $cur_box){
			$box["title"] = $cur_box->getAttribute("title");
			$box["modules"] = array();
			// the following counter counts the number of modules in the current box that are available
			$modulesInThisBox = 0;

			foreach($cur_box->getElementsByTagName("module") as $cur_module){
				// 'create' this module to get more information
				$thisModule = new Tool($cur_module->getAttribute("key"),$AUTH);
				$module = array();
				$module['key'] = $cur_module->getAttribute("key");
				$module['title'] = $thisModule->getTitle();

				// only add this module if it is available for the current user
				if($thisModule->available()){
					$box["modules"][] = $module;
					$modulesInThisBox++;
				}
			}
			// The box is only shown when there is at least one module in it
			if($modulesInThisBox > 0){
				$boxes[] = $box;
			}
		}
		// before returning the generated list, we add some success-status
		$ret = array();
		$ret['success'] = "yes";
		$ret['menu'] = $boxes;
		// we return a list in json-format
		print(json_encode($ret));

		break;

	default:
		echo "UNKNOWN";
		Logger::log("gi.php recieved an unknown command, namely: $cmd",Logger::LOGLEVEL_WARNING);
		// TODO: ANY ANSWER?	
}


?>
