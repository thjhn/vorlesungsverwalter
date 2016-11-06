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
function generate_settings_template(){
	$tmpl = "<h1>Eigenes Profil</h1>";

	$tmpl .= "<table id='settings_datatable'><p>Folgende Daten liegen Ihrem Profil zu Grunde:</p>";
	$tmpl .= "<tr><th>Benutzername</th><td id='settings_datatable_username'></td></tr>";
	$tmpl .= "<tr><th>Realname</th><td id='settings_datatable_realname'></td></tr>";
	$tmpl .= "<tr><th>Berechtigungen</th><td id='settings_datatable_roles'></td></tr>";
	$tmpl .= "</table>";
	$tmpl .= "<div><button id='settings_chpaswdbtn'>Passwort &auml;ndern.</button></div>";

	$tmpl .= "<h2></h2>";


	// the dialog to change a password
	$tmpl .= "<div id='settings_chpasswd_dialog' title='Passwort &auml;ndern'><form>";
	$tmpl .= "<div class='inputbox sm'><span class='label'>Altes Passwort</span><input type=\"password\" value=\"\" id=\"settings_chpasswd_oldpw\" class='ui-corner-all'/></div>";
	$tmpl .= "<div class='inputbox sm'><span class='label'>Neues Passwort</span><input type=\"password\" value=\"\" id=\"settings_chpasswd_newpw1\" class='ui-corner-all'/></div>";
	$tmpl .= "<div class='inputbox sm'><span class='label'>Neues Passwort best&auml;tigen</span><input type=\"password\" value=\"\" id=\"settings_chpasswd_newpw2\" class='ui-corner-all'/></div>";
	$tmpl .= "</form></div>";

	// the success dialog
	$tmpl .= "<div id='settings_chpasswd_response' title='Passwort &auml;ndern'><form>";
	return $tmpl;
}
?>
