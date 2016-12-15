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
function generate_sheetstats_template(){
	// Area for statistics
	$tmpl = "<h1>Statistiken</h1>";

	// on individual sheets
	$tmpl .= "<h2>Einzelne Bl&auml;tter</h2>";
	$tmpl .= "<form class='sheetSelectForm'><select size=\"1\" id=\"sheetstats_group\" class='ui-corner-all'></select></form>";
	$tmpl .= "<div id=\"sheetstats_chartdiv\" style=\"height: 300px; width: 95%;\"></div>";
	return $tmpl;
}
?>
