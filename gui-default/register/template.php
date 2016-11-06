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
function generate_register_template(){
	$tmpl = "<h1>Anmeldung zum &Uuml;bungsbetrieb</h1>";

	$tmpl .= "<form class='register_registerform inputForm'>";
	$tmpl .= "<h2>Einige pers&ouml;nliche Daten &hellip;</h2>";
	$tmpl .= "<p>Die folgenden Daten werden wir ggf. benutzen, um Ihre Pr&uuml;fungsergebnisse an das Pr&uuml;fungsamt zu melden. Stellen Sie daher bitte sicher, sie korrekt einzugeben.</p>";
	$tmpl .= "<div class='inputbox'><span class='label'>Nachname:</span><input type='input' name='familyname' class='ui-corner-all'/></div>";
	$tmpl .= "<div class='inputbox'><span class='label'>Vorname:</span><input type='input' name='givenname' class='ui-corner-all'/></div>";
	$tmpl .= "<div class='inputbox'><span class='label'>Matrikelnummer:</span><input type='input' name='matrnr' class='ui-corner-all'/></div>";
	$tmpl .= "<div class='inputbox'><span class='label'>Fachsemester:</span><input type='input' name='term' class='ui-corner-all'/></div>";
	$tmpl .= "<div class='inputbox'><span class='label'>Emailadresse:</span><input type='input' name='email' class='ui-corner-all'/></div>";
	$tmpl .= "<div class='inputbox'><span class='label'>Studiengang:</span><select name='course'></select></div>";

	$tmpl .= "<h2>W&auml;hlen Sie eine &Uuml;bungsgruppe</h2>";
	$tmpl .= "<p>W&auml;hlen Sie bitte eine freie &Uuml;bungsgruppe, die Sie besuchen m&ouml;chten.</p>";
	$tmpl .= "<table id=\"register_groups\"><thead><tr><th></th><th>Gruppe</th><th>Beschreibung</th><th>Freie Pl&auml;tze</th></tr></thead><tbody></tbody></table>";

	$tmpl .= "<h2>Fast fertig</h2>";
	$tmpl .= "<p>Kontrollieren Sie bitte nochmals die Korrektheit Ihrer Angaben. Klicken Sie anschlie&szlig;end auf Eintragen, um sich anzumelden.</p>";
	$tmpl .= "<div><button id='register_btn_submit'>Eintragen</button></div>";

	$tmpl .= "</form>";
	$tmpl .= "<div id=\"register_dialog\" title=\"Status ihrer Anmeldung.\"></div>";

	return $tmpl;
}
?>
