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

// we have a dialog that is shown when changes were saved
// we start with initalizing that dialog
$("#users_changes_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Ok": function(){
			$( this ).dialog( "close" );
		}
	}
});


// another dialog appears when the users looks for a non existing student.
$("users_error_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Ok": function(){
			$( this ).dialog( "close" );
		}
	}
});

// if the user's picks an user, a dialog appears.
// we start with initalizing that dialog
$("#users_edit_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Speichern": function(){			
			var dataobject = {
				username:$("#users_edit_dialog input[name=username]").attr("value"),
				realname:$("#users_edit_dialog input[name=realname]").attr("value"),
				enabled:$("#users_edit_dialog select[name=enabled]  option:selected").attr("value"),
				password:$("#users_edit_dialog input[name=password]").attr("value"),
				is_corrector:$("#users_edit_dialog select[name=corrector]  option:selected").attr("value"),
				is_admin:$("#users_edit_dialog select[name=admin]  option:selected").attr("value")
			};

			$.ajax({
				url:"i.php",
				type:"POST",
				dataType:"json",
				data:{
					cmd:"EDIT_USER",
					data:dataobject
				}
			}).done(function(data){
				$("#users_changes_dialog").empty();
				if(data.success == 'yes'){
					$("#users_changes_dialog").append("<div>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>");
					refreshUsersTable();
				}else{
					$("#users_changes_dialog").append("<div>Die &Auml;nderungen konnten leider nicht gespeichert werden. "+data.errormsg+"</div>");
				}
				$("#users_edit_dialog").dialog("close");
				$("#users_changes_dialog").dialog("open");

				$("#users_edit_dialog input[name=password]").attr("value","");
			});
		},
		"Abbrechen": function(){
			$( this ).dialog( "close" );
		}
	}
});

// if the user's wants to add an user, a dialog appears.
// we start with initalizing that dialog
$("#users_add_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Speichern": function(){
			var dataobject = {
				username:$("#users_add_dialog input[name=username]").attr("value"),
				realname:$("#users_add_dialog input[name=realname]").attr("value"),
				enabled:$("#users_add_dialog select[name=enabled]  option:selected").attr("value"),
				password:$("#users_add_dialog input[name=password]").attr("value"),
				is_corrector:$("#users_add_dialog select[name=corrector]  option:selected").attr("value"),
				is_admin:$("#users_add_dialog select[name=admin]  option:selected").attr("value")
			};
			$.ajax({
				url:"i.php",
				type:"POST",
				dataType:"json",
				data:{
					cmd:"ADD_USER",
					data:dataobject
				}
			}).done(function(data){
				$("#users_changes_dialog").empty();
				if(data.success == 'yes'){
					$("#users_changes_dialog").append("<div>Der neue Benutzer wurde hinzugefügt.</div>");
					refreshUsersTable();
				}else{
					$("#users_changes_dialog").append("<div>Der Benutzer konnte nicht hinzugef&uuml;gt werden. "+data.errormsg+"</div>");
				}
				$("#users_add_dialog").dialog("close");
				$("#users_changes_dialog").dialog("open");

				$("#users_edit_dialog input[name=password]").attr("value","");
			});
		},
		"Abbrechen": function(){
			$( this ).dialog( "close" );
		}
	}
});




// make the button nicer using jquery-ui
$( "#users_adduser" ).button({
	icons: {
		primary: "ui-icon-plusthick"
	},
	text: true
});

// add an event to the 'add a user' button
$("#users_adduser").on('click',function(e){
	e.preventDefault();
	$("#users_add_dialog").dialog("open");
	$("#users_add_dialog input[name=username]").attr("value","");
	$("#users_add_dialog input[name=realname]").attr("value","");
	$("#users_add_dialog input[name=password]").attr("value","");
});


// all we need in order to get the table
function refreshUsersTable(){
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"LIST_USERS",data:""}
	}).done(function(data){
		$("#users_userlist tbody").empty();
		for(i=0; i<data.length; i++){
			if(data[i].enabled=='yes'){
				var users_field_enabled = "<img src=\"client/icons/bullet_green.png\" alt=\"yes\" title=\"Der Benutzer ist aktiv und kann sich anmelden.\">";
			}else{
				var users_field_enabled = "<img src=\"client/icons/bullet_red.png\" alt=\"no\" title=\"Der Benutzer ist gesperrt und kann sich daher nicht anmelden.\">";
			}

			if(data[i].is_corrector=='yes'){
				var users_field_corrector = "<img src=\"client/icons/bullet_green.png\" alt=\"yes\" title=\"Benutzer ist Korrektor.\">";
			}else{
				var users_field_corrector = "<img src=\"client/icons/bullet_red.png\" alt=\"no\" title=\"Benutzer ist kein Korrektor.\">";
			}

			if(data[i].is_admin=='yes'){
				var users_field_admin = "<img src=\"client/icons/bullet_green.png\" alt=\"yes\" title=\"Benutzer ist Admin.\">";
			}else{
				var users_field_admin = "<img src=\"client/icons/bullet_red.png\" alt=\"no\" title=\"Benutzer ist kein Admin.\">";
			}

			$("#users_userlist tbody").append("<tr><td>"+users_field_enabled+"</td><td class='user_usernametd'>"+data[i].username+"</td><td>"+data[i].realname+"</td><td>"+users_field_admin+"</td><td>"+users_field_corrector+"</td></tr>");
		}

		// Activate tooltips for the newly added pictures
		$(document).tooltip();
			
		$("#users_userlist tbody tr").on('click',function(e){
			e.preventDefault();

			var cur_username = $(this).find(".user_usernametd").text();
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{
					cmd:"GET_USER",
					data:cur_username
				}
			}).done(function(data){
				if(data.success == 'yes'){
					$("#users_edit_dialog").find("input[name=realname]").attr("value",data.realname);
					$("#users_edit_dialog").find("select option").attr("selected","");

					$("#users_edit_dialog").find("select[name=enabled] option[value='"+data.enabled+"']").attr("selected","selected");
			
					if(data.is_admin=="yes"){
						$("#users_edit_dialog").find("select[name=admin] option[value='yes']").attr("selected","selected");
					}
					if(data.is_corrector=="yes"){
						$("#users_edit_dialog").find("select[name=corrector] option[value='yes']").attr("selected","selected");
					}

				}else{
					// TODO ERRORMSG
				}
			});

			$("#users_edit_dialog").find("input[name=username]").first().attr("value", cur_username );

			// open the dialog
			$("#users_edit_dialog").dialog("open");
		});
	});
}
refreshUsersTable();

