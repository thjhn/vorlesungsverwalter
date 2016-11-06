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

// The user is informed about his registration success using a dialog
// this dialog will be initialized now
$( "#settings_chpasswd_response" ).dialog({
	modal: true,
	autoOpen:false,
	buttons: {
		Ok: function() {
			$( this ).dialog( "close" );
		}
	}
});

$("#settings_chpasswd_dialog").dialog({
	modal: true,
	autoOpen:false,
	buttons: {
		Abbrechen: function() {
			$( this ).dialog( "close" );
		},
		Speichern: function() {
			if( $("#settings_chpasswd_newpw1").attr("value") == $("#settings_chpasswd_newpw2").attr("value") ){
				$.ajax({
					url:"i.php",
					type:"post",
					dataType:"json",
					data:{
						cmd:"CHANGE_PASSWORD",
						data:{
							"oldpassword":$("#settings_chpasswd_oldpw").attr("value"),
							"newpassword":$("#settings_chpasswd_newpw1").attr("value")
						}
					}
				}).done(function(data){
					console.log(data);

					if(data.success == 'yes'){
						showSuccessMsg("Ihr Passwort wurde ge&auml;ndert.");
						$("#settings_chpasswd_dialog").dialog("close");
					}else{
						showErrorMsg("Ihr Passwort konnte leider nicht ge&auml;ndert werden.");
					}
				});
			}else{	// the two new password to not match
				showErrorMsg("Die beiden neuen Passw&ouml;rter stimmen nicht &uuml;berein!");
				$("#settings_chpasswd_dialog input").attr("value","");
			}
		}
	}

});

// make the button nicer using jquery-ui
$( "#settings_chpaswdbtn" ).button({
	icons: {
		primary: "ui-icon-pencil"
	},
	text: true
});

// add the 'submit' functionality
$("#settings_chpaswdbtn").on('click',function(e){
	e.preventDefault();

	// open the dialog
	$("#settings_chpasswd_dialog input").attr("value","");
	$("#settings_chpasswd_dialog").dialog("open");
});


// get the number of sheets and generate the corresponding drop down menu
$.ajax({
	url:"i.php",
	type:"post",
	dataType:"json",
	data:{cmd:"GET_LOGIN",data:""}
}).done(function(data){
	if(data.success=='yes'){
		$("#settings_datatable_username").append(data.username);
		$("#settings_datatable_realname").append(data.realname);
		$("#settings_datatable_roles").append(data.roles);
	}else{
		showErrorMsg("Der Server konnte die gew&uuml;nschten Daten nicht bereitstellen!");
	}
});
