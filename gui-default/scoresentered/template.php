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
function generate_scoresentered_template(){
	// Area for entering scores
	$tmpl = "<h1>Eingetragene Punkte</h1>";

	// List of entered scores (by the current user)
	$tmpl .= "<h2>Auflistung aller eingetragenen Bl&auml;tter</h2>";
	$tmpl .= "<div><table id=\"scoresentered_entered\"><thead><tr><th>Blatt</th><th>Student</th><th colspan='2'>Punkte</th></tr></thead><tbody></tbody></table></div>";

	// the dialog
	$tmpl .= "<div id='scoresentered_dialog' title='Punkteeingabe'></div>";
	// the dialog for editing socres
	$tmpl .= "<div id='scoresentered_editdialog' title='&Auml;ndern von Punkten'></div>";
	return $tmpl;
}
?>
