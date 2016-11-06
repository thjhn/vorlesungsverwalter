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
function generate_enterscores_template(){
	// Area for entering scores
	$tmpl = "<h1>Punkte eintragen</h1><p>W&auml;hlen Sie das Blatt f&uuml;r welches Punkte eingetragen werden sollen. Geben Sie dann den Namen des Studenten und die Punktezahl ein. Zugelassen sind Punkte welche nicht negative ganze Zahlen sind (z.B. 0, 1, 2 etc.) oder Punkte mit Dezimal<em>punkt</em> (z.B. 0.5, 1.34 etc.)</p>";
	$tmpl .= "<div><form class='inputForm'>";
	$tmpl .= "<div class='inputbox sm'><span class='label'>Blatt:</span><select size=\"1\" id=\"enterscores_sheet\" class='ui-corner-all'></select></div>";
	$tmpl .= "<div class='inputbox' id='enterscores_thenameboxes'><span class='label'>Namen der Studenten</span><div class='enterscores_anamebox'><input type=\"text\" value=\"\" class=\"enterscores_name\" class='ui-corner-all'/><input type=\"hidden\" value=\"\" class=\"enterscores_select_id\"/><button class='enterscores_removestudent'><span class='button_label'>Diesen Studenten entfernen</span></button><button class='enterscores_morestudents'><span class='button_label'>Weiteren Studenten hinzuf&uuml;gen</span></button></div></div>";
	$tmpl .= "<div class='inputbox sm'><span class='label'>Punkte</span><input type=\"text\" value=\"\" id=\"enterscores_credits\" class='ui-corner-all'/></div>";
	$tmpl .= "<button id='enterscores_enter'><span class='button_label'>Eintragen</span></button>";
	$tmpl.="</form></div>";

	$tmpl .= "<h2></h2>";

	return $tmpl;
}
?>
