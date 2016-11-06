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

$(document).ajaxError(function(event, jqxhr, ajaxOptions, errorThrown) {
	showErrorMsg("Ihre Anfrage konnte nicht an den Server &uuml;bermittelt werden. Versuchen Sie es sp&auml;ter erneut. Sollte das Problem weiterhin bestehen, so kontaktieren Sie bitte den Administrator.");
    console.log("AJAX request failed:"+ajaxOptions.url+" "+ajaxOptions.data);
});

jQuery().ready(function(){
	/* Initialize the Error dialog */
	$( "#general_errormsg" ).dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	/* Initialize the Success dialog */
	$( "#general_successmsg" ).dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
			}
		}
	});
});


/* This function shows the error dialog with the message msg */
function showErrorMsg(msg){
	$( "#general_errormsg_msg" ).empty();
	$( "#general_errormsg_msg" ).append(msg);
	$( "#general_errormsg" ).dialog('open');
 }

/* This function shows the error dialog with the message msg */
function showSuccessMsg(msg){
	$( "#general_successmsg_msg" ).empty();
	$( "#general_successmsg_msg" ).append(msg);
	$( "#general_successmsg" ).dialog('open');
 }
