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
 *  Contains class Dataset
 */

include_once('server/logger/logger.php');
/**
 * The class Dataset is responsible for loading and strogin the
 * XML-files which are in the data directory.
 * 
 * Each Dataset object loads a single XML-file and performs its
 * operations on it.
 * A $writable-flag allows to load the file in read-only or in 
 * read/write mode.
 *
 * @author Thomas Jahn
 *
 */
class Dataset{
	var $dataset, $xml_file, $lock_file, $dom, $loaded;

	// Constructor. Load the dataset $dataset. If $writetable is true, it is possible to save changes in the DOM.
	// It is recommended to check whether the $loaded variable was set to True after initialisation.	
	function Dataset($dataset,$writeable){
		// $dataset indicates the filename of the XML-file.
		// we impose here that this filename (with the exception
		// of the ending .xml) only contains lower-case letters
		
		$this->loaded = False; // indicates whether loading the dataset was successfull.
		$this->dataset = $dataset;
		$this->writeable = $writeable;
		$this->xml_file = "data/".$dataset.".xml";
		$lock_filename = "locks/".$dataset.".lock";

		// first of all, we check whether there exists such a dataset
		if(file_exists($this->xml_file)){
			// at least the dataset file exists
			if(file_exists($lock_filename)){
				// yes, xml and lock exist!
				// now, we open the lockfile and try to get a shared lock
				$this->lock_file = fopen($lock_filename,"w");

				// The required lock depends on the value of writeable.
				if($this->writeable){
					// check whether the file is actualy writable
					if(is_writable($this->xml_file)){
						// try to get the lock
						if(flock($this->lock_file, LOCK_EX)){
							// OK; We got the Shared-lock
							// Let's load the DOM
							$this->dom = new DOMDocument();
							$this->dom->load($this->xml_file);
							$this->loaded = True;
						}else{
							Logger::log("dataset.php tried to get the lock for ".$this->xml_file." which failed.",Logger::LOGLEVEL_WARNING);
						}
					}else{
						Logger::log("dataset.php tried to open ".$this->xml_file." in write mode but the file is not writable.",Logger::LOGLEVEL_WARNING);
					}
				}else{
					if(flock($this->lock_file, LOCK_SH)){
						// OK; We got the Shared-lock
						// Let's load the DOM
						$this->dom = new DOMDocument();
						$this->dom->load($this->xml_file);
						$this->loaded = True;
					}else{
						Logger::log("dataset.php tried to get the lock for ".$this->xml_file." which failed.",Logger::LOGLEVEL_WARNING);
					}
				}
			}else{
				// no, the requested lock does not exist
				Logger::log("dataset.php tried to get the lock for ".$this->xml_file." but lockfile does not exist.",Logger::LOGLEVEL_ERROR);
			}
		}else{
			// no, the requested dataset does not exist
			Logger::log("dataset.php tried to get the data from ".$this->xml_file." but this file does not exist.",Logger::LOGLEVEL_ERROR);
		}
	}

	// The descructor gives back the lock
	function __destruct(){
		flock($this->lock_file, LOCK_UN);
		fclose($this->lock_file);
	}

	/**
	 * Check whether loading the Dataset was successful.
	 *
	 * @return bool loaded state
	 */
	function isLoaded(){
		return $this->loaded;
	}


	/**
         * Find all $nodename nodes in the dom variable of the dataset that have
         * the attribute $attribute_name set to $attribute_value
         *
         * @param $nodename The name of the nodes to find.
         * @param $attribute_name the name of the attribute to look at
         * @param attribute_value the required value of this attribute
         *
         * @return an array of matching nodes. In case of an error we return
	 *   an empty array.
	 */
	function getNodeByAttribute($nodename, $attribute_name, $attribute_value){
		$arr_to_return = array();
		foreach($this->dom->getElementsByTagName($nodename) as $cur_node){
			if($cur_node->getAttribute($attribute_name) == $attribute_value){
				$arr_to_return[] = $cur_node;
				break;
			}
		}
		return $arr_to_return;
	}

	function save(){
		// check whether we opened the dataset writable.
		if($this->writeable){
			$this->dom->save($this->xml_file);
		}else{
			Logger::log("dataset.php tried to save changes in file ".$this->xml_file." which was opend read-only.",Logger::LOGLEVEL_WARNING);
			return false;
		}
	}


}

?>
