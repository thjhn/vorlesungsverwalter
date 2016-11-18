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
include_once("server/logger/logger.php");

function generate_exams_template(){

	// if the users picks an exam, a dialog appears:
	$tmpl .= "<div id=\"exams_edit_dialog\" title=\"Klausur bearbeiten\"><div><form id=\"exams_edit_dialogform\"><input type='hidden' name='examid'><table><tr><td>Klausurname</td><td><input type='input' name='name'></td></tr><tr><td>Anmeldung</td><td><select name='registration'><option value=\"true\">erlaubt</option><option value=\"false\">gesperrt</option></select></td></tr><tr><td>Bewerten</td><td><select name='enterscores'><option value=\"true\">erlaubt</option><option value=\"false\">gesperrt</option></select></td></tr></table></form></div></div>";
	$tmpl .= "<div id=\"exams_error_dialog\" title=\"Fehler\"></div>";
	$tmpl .= "<div id=\"exams_changes_dialog\" title=\"Speicherung der &Auml;nderung\"></div>";

	// in the lower part of the panel there is a list of exams
	$tmpl .= "<h1>Klausuren</h1>";
	$tmpl .= "<table class=\"tablesorter\" id=\"exams_examlist\"><thead><tr><th class=\"header\" colspan='2'>Klausur</th><th class=\"header\">Anmeldung</th><th class=\"header\">Bewerten</th></thead><tbody></tbody></table>";
	$tmpl .= "<div><button id='exams_addexam'>Neue Klausur erstellen.</button></div>";

	return $tmpl;




	return $tmpl;
}
?>
