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
	$tmpl .= "<div>Spalten anzeigen:<br/>";
	$tmpl .= "<form id='scoreresults_cols'><p><input type='checkbox' name='scoreresults_cols_names' checked='checked'/>Namen<br/>";
	$tmpl .= "<input type='checkbox' name='scoreresults_cols_matrnr' checked='checked'/>Matrikelnummern<br/>";
	$tmpl .= "<input type='checkbox' name='scoreresults_cols_scores' checked='checked'/>&Uuml;bungspunkte<br/>";
	$tmpl .= "<input type='checkbox' name='scoreresults_cols_exams' checked='checked'/>Klausuren</form></div>";
	$tmpl .= "<div><table  class='colored' id=\"scoreresults_list\"><thead></thead><tbody></tbody></table></div>";

	return $tmpl;
}
?>
