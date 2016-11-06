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
function generate_users_template(){
	// if the users picks a user, a dialog appears:
	$tmpl .= "<div id=\"users_edit_dialog\" title=\"Benutzer bearbeiten\"><div><form><input type='hidden' name='username'><table><tr><td>Realname</td><td><input type='input' name='realname'></td></tr><tr><td>Neues Passwort</td><td><input type='password' name='password'></td></tr><tr><td>Aktiv</td><td><select name='enabled'><option value=\"yes\">ja</option><option value=\"no\">nein</option></select></td></tr><tr><td>Korrektor</td><td><select name='corrector'><option value=\"yes\">ja</option><option value=\"no\">nein</option></select></td></tr><tr><td>Admin</td><td><select name='admin'><option value=\"yes\">ja</option><option value=\"no\">nein</option></select></td></tr></table></form></div></div>";

	// The feedback dialogs
	$tmpl .= "<div id=\"users_error_dialog\" title=\"Fehler\"></div>";
	$tmpl .= "<div id=\"users_changes_dialog\" title=\"Speicherung der &Auml;nderung\"></div>";

	// if the users adds a mew user, a dialog appears:
	$tmpl .= "<div id=\"users_add_dialog\" title=\"Benutzer hinzuf&uuml;gen\"><div><form><table><tr><td>Benutzername</td><td><input type='input' name='username'></td></tr><tr><td>Realname</td><td><input type='input' name='realname'></td></tr><tr><td>Passwort</td><td><input type='password' name='password'></td></tr><tr><td>Korrektor</td><td><select name='corrector'><option value=\"yes\">ja</option><option value=\"no\" selected>nein</option></select></td></tr><tr><td>Admin</td><td><select name='admin'><option value=\"yes\">ja</option><option value=\"no\" selected>nein</option></select></td></tr></table></form></div></div>";


	// in panel there is a list of all students
	$tmpl .= "<h1>Liste der Benutzer</h1>";
	$tmpl .= "<table class=\"tablesorter\" id=\"users_userlist\"><thead><tr><th class=\"header\">Aktiv</th><th class=\"header\">Login</th><th class=\"header\">Name</th><th class=\"header\">A</th><th class=\"header\">K</th></tr></thead><tbody></tbody></table>";
	$tmpl .= "<div><button id='users_adduser'>Neuen Benutzer erstellen.</button></div>";

	$tmpl .= "<h2>H&auml;ufige Fragen</h2><div><strong>Warum kann ich einen Nutzer nicht entfernen?</strong> Evtl. hat der Nutzer bereits Leistungen eingetragen, daher ist das L&ouml;schen von Nutzern nicht m&ouml;glich. Allerdings k&ouml;nnen Sie Benutzer deaktivieren, um ihnen in Zukunft keinen Zugriff mehr zu gestatten.</div>";

	return $tmpl;
}
?>
