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
function generate_registrationslots_template(){
	// if the users picks a slot (or creates a new one), a dialog appears:
	$tmpl .= "<div id=\"registrationslots_edit_dialog\" title=\"Benutzer bearbeiten\"><div><form><input type='hidden' id=\"registrationslots_edit_dialog_id\"><table><tr><td>Start</td><td><input type='input' id='registrationslots_edit_dialog_start' size='10'><input type='input' id='registrationslots_edit_dialog_starttime' size='5'></td></tr><tr><td>Ende</td><td><input type='input' id='registrationslots_edit_dialog_end' size='10'><input type='input' id='registrationslots_edit_dialog_endtime' size='5'></td></tr></table></form></div></div>";

	// The feedback dialogs
	$tmpl .= "<div id=\"registrationslots_error_dialog\" title=\"Fehler\"></div>";
	$tmpl .= "<div id=\"registrationslots_changes_dialog\" title=\"Speicherung der &Auml;nderung\"></div>";

	// in panel there is a list of all slots
	$tmpl .= "<h1>Liste der Anmeldezeitr&auml;ume</h1>";
	$tmpl .= "<table class=\"tablesorter\" id=\"registrationslots_slotlist\"><thead><tr><th class=\"header\">Start</th><th class=\"header\">Ende</th><th class=\"header\"></th></tr></thead><tbody></tbody></table>";
	$tmpl .= "<div><button id='registrationslot_addslot'>Neuen Zeitraum erstellen.</button></div>";

	//$tmpl .= "<h2>H&auml;ufige Fragen</h2><div><strong>Warum kann ich einen Nutzer nicht entfernen?</strong> Evtl. hat der Nutzer bereits Leistungen eingetragen, daher ist das L&ouml;schen von Nutzern nicht m&ouml;glich. Allerdings k&ouml;nnen Sie Benutzer deaktivieren, um ihnen in Zukunft keinen Zugriff mehr zu gestatten.</div>";

	return $tmpl;
}
?>
