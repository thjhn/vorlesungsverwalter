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
function generate_enterscoresexam_template(){
	// Area for entering scores
	$tmpl = "<h1>Punkte eintragen</h1><p>W&auml;hlen Sie die Klausur f&uuml;r die Punkte eingetragen werden sollen.</p>";
	$tmpl .= "<div><form class='inputForm'>";
	$tmpl .= "<div class='inputbox sm'><span class='label'>Klausur:</span><select size=\"1\" id=\"enterscoresexam_exam\" class='ui-corner-all'></select></div>";
	$tmpl .= "<div class='inputbox' id='enterscoresexam_thenameboxes'><span class='label'>Namen des Students</span><div class='enterscoresexam_namebox'><input type=\"text\" value=\"\" id=\"enterscoresexam_name\" class='ui-corner-all'/><input type=\"hidden\" value=\"\" id=\"enterscoresexam_select_id\"/></div></div>";
	$tmpl .= "<div id='enterscoresexam_points'></div>";
	$tmpl .= "<div class='inputbox'><span class='label'>&Uuml;berschreiben:</span><input type=\"checkbox\" id=\"enterscoresexam_overwrite\" caption=\"ladida\"/> Bestehende Daten &uuml;berschreiben.</div>";
	$tmpl .= "<button id='enterscoresexam_enter'><span class='button_label'>Eintragen</span></button>";
	$tmpl.="</form></div>";

	$tmpl .= "<h2></h2>";

	return $tmpl;
}
?>
