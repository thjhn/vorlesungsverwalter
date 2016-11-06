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
 *  Contains the class Logger.
 */

/**
 * The class provides logging functionalities.
 *
 * The most important (and right now the only) feature of this class is the static function log which writes a message into a log file.
 * The importance of a log message is modelled using loglevels which are defined using constants.
 * 
 * @author Thomas Jahn vv3@t-und-j.de
 */
class Logger{
	/** This loglevel is mostly used for more or less important information. */
	const LOGLEVEL_VERBOSE = 0;
	/** This loglevel is used for warnings. */
	const LOGLEVEL_WARNING = 1;
	/** This loglevel is used whenever an error occured. */
	const LOGLEVEL_ERROR = 2;
	/** This loglevel is used whenever really strange things happen. */
	const LOGLEVEL_EMERGENCY = 3;
	/** Here the threshold for logging messages is defined. */
	const LOGLEVEL = 0;
	/** The file where the log messages should stored at. */
	const LOGFILE = "log/main.log";

	/**
	 * Writes a message into a log-file provided the message is important enough.
	 *
	 * The message $message is written into a log-file ("log/main.log") if $loglevel is greater than the constant Logger::LOGLEVEL.
	 * 
	 * @param string $message The message that should be logged.
	 * @param int $loglevel The loglevel of the message.
	 * 
	 * @return void
	 */
	public static function log($message,$loglevel){
		// only log if the loglevel is large enough
		if($loglevel >= Logger::LOGLEVEL){
			$lineToWrite = date("Y-m-d H:m:s")." -- level $loglevel -- $message\n";
			$fp = fopen(Logger::LOGFILE,"a");
			fwrite($fp,$lineToWrite);
			fclose($fp);
		}
	}


	/**
	 * Returns an array containing all logs.
	 * 
	 * @return array Returns an array of arrays: Each line of the logfile is represented as an associative array containing the fields 'loglevel', 'date' and 'message'.
	 */

	public static function getLogs(){
		$fp = fopen(Logger::LOGFILE,"r");
		while( !feof($fp) ){
			$line = explode(" -- ",fgets($fp));

			$entry["date"] = $line[0];
			$entry["loglevel"] = $line[1];
			$entry["message"] = $line[2];
			$ret[] = $entry;

		}
		fclose($fp);

		return $ret;
	}

}

?>
