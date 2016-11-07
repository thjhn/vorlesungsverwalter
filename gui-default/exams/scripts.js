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

// if the user's picks an exam, a dialog appears.
// we start with initalizing that dialog
$("#exams_edit_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Speichern": function(){
			// add each field that has to be changed to the changearray
			var editarray = [];
			$("#exams_edit_dialog input[class=field_edited]").each(function(index){
				editarray.push({field:$(this).attr("name"), newvalue:$(this).attr("value")});
			});
			$("#exams_edit_dialog select[name=registration] option:selected").each(function(index){
				editarray.push({field:$(this).parent().attr("name"), newvalue:$(this).attr("value")});
			});
			$("#exams_edit_dialog select[name=enterscores] option:selected").each(function(index){
				editarray.push({field:$(this).parent().attr("name"), newvalue:$(this).attr("value")});
			});

			var dataobject = {exam:$("#exams_edit_dialog input[name=examid]").attr("value"), changes:editarray};
			console.log(dataobject);
			$.ajax({
				url:"i.php",
				type:"POST",
				dataType:"json",
				data:{
					cmd:"EDIT_EXAM",
					data:dataobject
				}
			}).done(function(data){
				$("#exams_edit_dialog").empty();
				if(data.success == 'yes'){
					$("#exams_changes_dialog").append("<div>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>");
				}else{
					$("#exams_changes_dialog").append("<div>Die &Auml;nderungen konnten leider nicht gespeichert werden. "+data.errormsg+"</div>");
				}
				$("#exams_edit_dialog").dialog("close");
				$("#exams_changes_dialog").dialog("open");

				$("#exams_edit_dialog input").removeClass("field_edited");
				$("#exams_edit_dialog input[name=password]").attr("value","");
				refreshExamsTable();
			});
		},
		"Abbrechen": function(){
			$("#exams_edit_dialog input").removeClass("field_edited");
			$( this ).dialog( "close" );
		}
	}
});

// we have a dialog that is shown when changes have been saved
// we start with initalizing that dialog
$("#exams_changes_dialog").dialog({
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
		data:{cmd:"GET_ALL_EXAMS",data:""}
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

			$("#exams_examlist tbody").append("<tr><td class='exams_examidtd'>"+data[i].exam+"</td><td class='exams_examnametd'>"+data[i].name+"</td><td>"+exams_field_enabled+"</td><td>"+exams_field_enterscores+"</td></tr>");
		}

		// Activate tooltips for the newly added pictures
		$(document).tooltip();
			
		$("#exams_examlist tbody tr").on('click',function(e){
			e.preventDefault();

			var cur_examid = $(this).find(".exams_examidtd").text();
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{
					cmd:"GET_EXAM",
					data:cur_examid
				}
			}).done(function(data){
				console.log(data);
				if(data.success == 'yes'){
					$("#exams_edit_dialog").find("input[name=examid]").attr("value",data.exam);
					$("#exams_edit_dialog").find("input[name=name]").attr("value",data.name);
					$("#exams_edit_dialog").find("select option").attr("selected","");
					$("#exams_edit_dialog").find("select[name=registration] option[value='"+data.registration+"']").attr("selected","selected");
					$("#exams_edit_dialog").find("select[name=enterscores] option[value='"+data.enterscores+"']").attr("selected","selected");		

				}else{
					// TODO ERRORMSG
				}
			});

			$("#exams_edit_dialog").find("input[name=username]").first().attr("value", cur_examid );

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
	window.setTimeout(refreshExamsTable,1000*60*5);
}
refreshExamsTable();
