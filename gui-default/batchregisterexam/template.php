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
function generate_batchregisterexam_template(){
	// Area for entering scores
	$tmpl = "<h1>Klausuranmeldung</h1>";

	// List of entered scores (by the current user)
	$tmpl .= "<h2>Liste der Studenten</h2>";
	$tmpl .= "<div><table id=\"batchregisterexam_list\"><thead><tr><th>Nachname</th><th>Vorname</th><th class='batchregisterexam_list_idcol' >ID</th><th></th></tr></thead><tbody></tbody></table></div>";
	$tmpl .= "<div id='batchregisterexam_checkall'><a href=''>Alle Studenten markieren.</a></div>";
	$tmpl .= "<h2>Markierte Studenten</h2>";
	$tmpl .= "<div id='batchregisterexam_exams'></div>";
	return $tmpl;
}
?>
