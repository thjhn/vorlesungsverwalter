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
class Dataset{

	var $dataset, $xml_file, $lock_file, $dom;

	// Constructor. Load the dataset $dataset. If $writetable is true, it is possible to save changes in the DOM.
	function Dataset($dataset,$writeable){
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
					$lock = LOCK_EX;
				}else{
					$lock = LOCK_SH;
				}

				if(flock($this->lock_file, $lock)){
					// OK; We got the Shared-lock
					// Let's load the DOM
					$this->dom = new DOMDocument();
					$this->dom->load($this->xml_file);
				}else{
					// We didn't get the lock; TODO: ERROR
					Logger::log("dataset.php tried to get the lock for ".$this->xml_file." which failed.",Logger::LOGLEVEL_WARNING);
					echo "sad";
				}
			}else{
				// no, the requested lock does not exist
				// TODO: ERROR				
			}
		}else{
			// no, the requested dataset does not exist
			// TODO: ERROR
		}
	}

	// The descructor gives back the lock
	function __destruct(){
		flock($this->lock_file, LOCK_UN);
		fclose($this->lock_file);
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
