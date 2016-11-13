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

// First, we initialize some dialogs.

// The following dialog allows editing a single group
$("#groups_edit_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Speichern": function(){
			var dataobject = {
				groupid:$("#groups_edit_dialog input[name=groupid]").attr("value"),
				name:$("#groups_edit_dialog input[name=name]").attr("value"),
				description:$("#groups_edit_dialog input[name=description]").attr("value"),
				seats:$("#groups_edit_dialog input[name=seats]").attr("value")
			};
			$.ajax({
				url:"i.php",
				type:"POST",
				dataType:"json",
				data:{
					cmd:"EDIT_GROUP",
					data:dataobject
				}
			}).done(function(data){
				$("#groups_changes_dialog").empty();
				if(data.success == 'yes'){
					$("#groups_changes_dialog").append("<div>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>");
				}else{
					$("#groups_changes_dialog").append("<div>Die &Auml;nderungen konnten leider nicht gespeichert werden. "+data.errormsg+"</div>");
				}
				$("#groups_edit_dialog").dialog("close");
				$("#groups_edit_dialog input").removeClass("field_edited");

				$("#groups_changes_dialog").dialog("open");
				refreshGroupsTable();
			});
		},
		"Abbrechen": function(){
			$( this ).dialog( "close" );
		}
	}
});

// we have a dialog that is shown when changes were saved
$("#groups_changes_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Ok": function(){
			$( this ).dialog( "close" );
		}
	}
});


// make the button nicer using jquery-ui
$( "#groups_addgroup" ).button({
	icons: {
		primary: "ui-icon-plusthick"
	},
	text: true
});


// another dialog appears when the users looks for a non existing student.
$("#groups_error_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Ok": function(){
			$( this ).dialog( "close" );
		}
	}
});

// add an event to the 'add a group' button
$("#groups_addgroup").on('click',function(e){
	e.preventDefault();
	$("#groups_edit_dialog").dialog("open");
	$("#groups_edit_dialog input[name=groupid]").attr("value","_new");
	$("#groups_edit_dialog input[name=name]").attr("value","");
	$("#groups_edit_dialog input[name=description]").attr("value","");
	$("#groups_edit_dialog input[name=seats]").attr("value","inf");
});


// all we need in order to get the table
function refreshGroupsTable(){
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"LIST_ALL_GROUPS"}
	}).done(function(data){
		$("#groups_grouplist tbody").empty();
		for(i=0; i<data.length; i++){

			// we use traffic light icons to indicate how many seats are left in a group.
			var traffic_lights = "";
			if(data[i].seats == "inf"){
				traffic_lights = "<img src=\"client/icons/traffic_lights_green.png\" alt=\"Gruen\" title=\"Unbegrenzt viele freie Pl&auml;tze in dieser Gruppe.\"/>";
			}else{
				if((data[i].seats-data[i].students)/data[i].seats >= 0.25){
					traffic_lights = "<img src=\"client/icons/traffic_lights_green.png\" alt=\"Gruen\" title=\"Mehr als 25% der Pl&auml;tze sind frei.\"/>";
				}else{
					if(data[i].seats-data[i].students>0){
						traffic_lights = "<img src=\"client/icons/traffic_lights_yellow.png\" alt=\"Gelb\" title=\"Es sind noch Pl&auml;tze frei, jedoch weniger als 25%.\"/>";
					}else{
						traffic_lights = "<img src=\"client/icons/traffic_lights_red.png\" alt=\"Rot\" title=\"Es sind keine Plätze mehr frei.\"/>";
					}
				}
			}

			$("#groups_grouplist tbody").append("<tr><td class='group_groupnametd'><div class='group_groupid'>"+data[i].groupid+"</div>"+data[i].name+"</td><td class='group_groupdesctd'>"+data[i].description+"</td><td class='group_groupseatstd'>"+traffic_lights+" "+data[i].students+" von "+data[i].seats+"</td><td><img src=\"client/icons/bullet_edit.png\" class=\"groups_editgroup\" alt=\"Editieren\" title=\"Diese Gruppe editieren.\"></td></tr>");
			$(".group_groupid").hide();
		}

		// Activate tooltips for the newly added pictures
		$(document).tooltip();
			
		// Activate the edit functionality
		$(".groups_editgroup").on('click',function(e){

			e.preventDefault();
			
			var groupid = $(this).closest("tr").find(".group_groupid").text();
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{
					cmd:"GET_GROUP_JSON",
					data:groupid
				}
			}).done(function(data){
				if(data.success == 'yes'){
					$("#groups_edit_dialog input[name=groupid]").attr("value",data.groupid);
					$("#groups_edit_dialog input[name=name]").attr("value",data.name);
					$("#groups_edit_dialog input[name=description]").attr("value",data.description);
					$("#groups_edit_dialog input[name=seats]").attr("value",data.seats);

					// open the dialog.
					$("#groups_edit_dialog").dialog("open");
				}else{
					// TODO ERRORMSG
				}
			});			

		});

	});

	// Update this table regularly (each 5 min)
	window.setTimeout(refreshGroupsTable,1000*60*5);
}
refreshGroupsTable();
