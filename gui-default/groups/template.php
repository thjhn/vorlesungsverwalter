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
function generate_groups_template(){
	// The following dialog allows changes on a single group (or adding a new one)
	$tmpl .= "<div id=\"groups_edit_dialog\" title=\"Gruppe bearbeiten\"><div><form id=\"groups_edit_single_dialog\"><input type='hidden' name='groupid'><table><tr><td>Name</td><td><input type='hidden' name='groupid'><input type='input' name='name'></td></tr><tr><td>Beschreibung</td><td><input type='input' name='description'></td></tr><tr><td>Pl&auml;tze</td><td><input type='input' name='seats'></td></tr></table></form></div></div>";

	// The following dialog informs about the success of a previous change
	$tmpl .= "<div id=\"groups_changes_dialog\" title=\"&Auml;nderungen\"></div>";

	// in the lower part of the panel there is a list of all groups
	$tmpl .= "<h1>Liste der Gruppen</h1>";
	$tmpl .= "<table class=\"tablesorter\" id=\"groups_grouplist\"><thead><tr><th class=\"header\">Name</th><th class=\"header\">Beschreibung</th><th class=\"header\">Belegung</th><th class=\"header\">Funktionen</th></tr></thead><tbody></tbody></table>";
	$tmpl .= "<div><button id='groups_addgroup'>Neue Gruppe erstellen.</button></div>";

	$tmpl .= "<h2>H&auml;ufige Fragen</h2><div><strong>Warum kann ich eine Gruppe nicht entfernen?</strong> Da evtl. Studenten in dieser Gruppe eingetragen sind, ist ein nachtr&auml;gliches Entfernen einer Gruppe nicht vorgesehen. Allerdings k&ouml;nnen Sie bestehende Gruppen umbenennen oder die Gruppe 'schlie&szlig;en', indem Sie die Teilnehmerzahl auf 0 setzen.</div>";

	return $tmpl;
}
?>
