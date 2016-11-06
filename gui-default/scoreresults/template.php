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
 */
function generate_scoreresults_template(){

	// Area for entering scores
	$tmpl = "<h1>Ergebnisse</h1>";

	// List of entered scores (by the current user)
	$tmpl .= "<h2>&Uuml;bersicht &uuml;ber die erreichten Punkte.</h2>";
	$tmpl .= "<div><table  class='colored' id=\"scoreresults_list\"><thead><tr><th>Student</th></thead><tbody></tbody></table></div>";

	return $tmpl;
}
?>
