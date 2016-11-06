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

// if the user's picks a student, a dialog appears.
// we start with initalizing that dialog
$("#exams_edit_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Speichern": function(){
			// add each field that has to be changed to the changearray (except roles!)
			var editarray = [];
			$("#users_edit_dialog input[class=field_edited]").each(function(index){
				editarray.push({field:$(this).attr("name"), newvalue:$(this).attr("value")});
			});
			$("#users_edit_dialog select[name=enabled] option:selected").each(function(index){
				editarray.push({field:$(this).parent().attr("name"), newvalue:$(this).attr("value")});
			});

			// generate the list of roles the user should be asigned to...
			var rolearray = [];
			if($("#users_edit_dialog").find("select[name=admin] option[value='yes']").attr("selected") == "selected" ){
				rolearray.push("admin");
			}
			if($("#users_edit_dialog").find("select[name=corrector] option[value='yes']").attr("selected") == "selected" ){
				rolearray.push("corrector");
			}

			var dataobject = {username:$("#users_edit_dialog input[name=username]").attr("value"), changes:editarray, roles:rolearray};
console.log(dataobject);
			$.ajax({
				url:"i.php",
				type:"POST",
				dataType:"json",
				data:{
					key:"users",
					cmd:"TOOL_I",
					subcmd:"SAVE_CHANGES",
					subcmddata:dataobject
				}
			}).done(function(data){
				$("#users_changes_dialog").empty();
				if(data.success == 'yes'){
					$("#users_changes_dialog").append("<div>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>");
				}else{
					$("#users_changes_dialog").append("<div>Die &Auml;nderungen konnten leider nicht gespeichert werden. "+data.errormsg+"</div>");
				}
				$("#users_edit_dialog").dialog("close");
				$("#users_changes_dialog").dialog("open");

				$("#users_edit_dialog input").removeClass("field_edited");
				$("#users_edit_dialog input[name=password]").attr("value","");
			});
		},
		"Abbrechen": function(){
			$("#users_edit_dialog input").removeClass("field_edited");
			$( this ).dialog( "close" );
		}
	}
});

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


// all we need in order to get the table
function refreshExamsTable(){
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"TOOL_I",key:"exams",subcmd:"GET_ALL_EXAMS",subcmddata:""}
	}).done(function(data){
		$("#exams_examlist tbody").empty();
		for(i=0; i<data.length; i++){
			// Generate the enabled-picture
			if(data[i].registration=='enabled'){
				var exams_field_enabled = "<img src=\"client/icons/bullet_green.png\" alt=\"yes\" title=\"Zur Klausur ist derzeit eine Anmeldung möglich.\">";
			}else{
				var exams_field_enabled = "<img src=\"client/icons/bullet_red.png\" alt=\"no\" title=\"Zur Klausur ist derzeit keine Anmeldung möglich.\">";
			}
			if(data[i].enterscores=='enabled'){
				var exams_field_enterscores = "<img src=\"client/icons/bullet_green.png\" alt=\"yes\" title=\"Die Klausur kann derzeit bepunktet werden.\">";
			}else{
				var exams_field_enterscores = "<img src=\"client/icons/bullet_red.png\" alt=\"no\" title=\"Die Klausur kann derzeit nicht bepunktet werden.\">";
			}

			$("#exams_examlist tbody").append("<tr><td class='exams_examnametd'>"+data[i].name+"</td><td>"+exams_field_enabled+"</td><td>"+exams_field_enterscores+"</td></tr>");
		}

		// Activate tooltips for the newly added pictures
		$(document).tooltip();
			
		$("#exams_examlist tbody tr").on('click',function(e){
			e.preventDefault();

			var cur_username = $(this).find(".user_usernametd").text();
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{
					key:"users",
					cmd:"TOOL_I",
					subcmd:"GET_USER",
					subcmddata:cur_username
				}
			}).done(function(data){
				console.log(data);
				if(data.success == 'yes'){
					$("#exams_edit_dialog").find("input[name=realname]").attr("value",data.realname);
					$("#exams_edit_dialog").find("select option").attr("selected","");

					$("#exams_edit_dialog").find("select[name=enabled] option[value='"+data.enabled+"']").attr("selected","selected");
			
					if($.inArray("admin",data.rolelist)>=0){
						$("#users_edit_dialog").find("select[name=admin] option[value='yes']").attr("selected","selected");
					}
					if($.inArray("corrector",data.rolelist)>=0){
						$("#users_edit_dialog").find("select[name=corrector] option[value='yes']").attr("selected","selected");
					}

				}else{
					// TODO ERRORMSG
				}
			});

			$("#exams_edit_dialog").find("input[name=username]").first().attr("value", cur_username );

			// add an event handler
			// we mark each field with changes by adding the class 'field_edited'
			$("#exams_edit_dialog input").on('change',function(e){
				$(this).addClass("field_edited");
			});

			// open the dialog
			$("#exams_edit_dialog").dialog("open");
		});
	});

	// Update this table regularly (each 5 min)
	window.setTimeout(refreshStudentsTable,1000*60*5);
}
refreshExamsTable();
