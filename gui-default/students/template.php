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
function generate_students_template(){
	$tmpl  = "<h1>Studenten</h1>";
	// in the upper part of the panel the user may look for a single student
	$tmpl .= "<h2>Student Einzelansicht</h2>";
	$tmpl .= "<div>W&auml;hlen Sie einen Studenten aus, indem Sie seine Matrikelnummer oder seinen Namen eingeben:</div>";
	$tmpl .= "<div id=\"mod_students_select\"><form class=\"inputForm\">";
	$tmpl .= "<div class='inputbox'><span class='label'>Nachname, Vorname (Matrikelnummer):</span><input type=\"text\" value=\"\" id=\"mod_students_name\" class='ui-corner-all'><input id=\"mod_students_select_id\" type='hidden' name='uid'></div>";
$tmpl .= "<div><button id='students_btn_find'>Ausw&auml;hlen</button></div>";

	// if the users picks a student, a dialog appears:
	$tmpl .= "<div id=\"mod_students_edit_dialog\" title=\"Student bearbeiten\"><h1>Pers&ouml;nliche Daten</h1><div id=\"students_edit_personal_dialog\"><form ><input type='hidden' name='uid'><table><tr><td>Nachname</td><td><input type='input' name='familyname'></td></tr><tr><td>Vorname</td><td><input type='input' name='givenname'></td></tr><tr><td>MatrNr.</td><td><input type='input' name='matrnr'></td></tr><tr><td>Semester</td><td><input type='input' name='term'></td></tr><tr><td>Studiengang</td><td><input type='input' name='course'></td></tr><tr><td>Email</td><td><input type='input' name='email'></td></tr><tr><td>Gruppe</td><td><input type='hidden' name='ingrouphidden'><select name='ingroup'></select></td></tr></table></form></div><h1>&Uuml;bungsbl&auml;tter</h1><div><div>Beachten Sie, dass sich bei Doppelabgaben &Auml;nderungen der Punktezahlen auch auf andere Studenten auswirken kann.</div><div id='students_scores'></div></div><h1>Klausuren</h1><div><div>Zu folgenden Klausuren k&ouml;nenen Sie diesen Studenten anmelden.</div><div id='students_exams'></div></div></div>";

	$tmpl .= "<div id=\"students_error_dialog\" title=\"Fehler\"></div>";
	$tmpl .= "<div id=\"students_changes_dialog\" title=\"Speicherung der &Auml;nderung\"></div>";

	// in the lower part of the panel you may generate a list of students
	$tmpl .= "<h2>Teilnehmerliste erzeugen</h2>";
	$tmpl .= "<form class='inputForm'>";
	$tmpl .= "<div class='inputbox' id='students_generatelist_fields'><span class='label'>Welche Spalten sollen angezeigt werden? <a href='' id='students_generatelist_selectallfields'>Alle ausw&auml;hlen.</a></span>";
	$tmpl .= "<div><input type='checkbox' name='lastname'>Nachname</input></div>";
	$tmpl .= "<div><input type='checkbox' name='firstname'>Vorname</input></div>";
	$tmpl .= "<div><input type='checkbox' name='matrnr'>Matrikelnummer</input></div>";
	$tmpl .= "<div><input type='checkbox' name='term'>Semester</input></div>";
	$tmpl .= "<div><input type='checkbox' name='course'>Studiengang</input></div>";
	$tmpl .= "<div><input type='checkbox' name='email'>Email</input></div>";
	$tmpl .= "<div><input type='checkbox' name='group'>Gruppe</input></div>";
	$tmpl .= "<div><input type='checkbox' name='totalscore'>Ges.pkt.</input></div>";
	$tmpl .= "<div><input type='checkbox' name='nrhandedin'>Abg. Bl&auml;tter</input></div>";
	$tmpl .= "</div>";
	$tmpl .= "<div class='inputbox' id='students_generatelist_groups'><span class='label'>Welche Gruppen sollen angezeigt werden? <a href='' id='students_generatelist_selectallgroups'>Alle ausw&auml;hlen.</a></span>";
	$tmpl .= "</div>";
	$tmpl .= "<div><button id='students_btn_generatelist'>Liste erstellen</button></div>";
	$tmpl .= "</form>";

	// the list is put into a new dialog
	$tmpl .= "<div id=\"students_list_dialog\" title=\"Studenten\"></div>";

	return $tmpl;
}
?>
