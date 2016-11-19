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

// we use the accordian-widget of jquery-ui
$("#mod_students_edit_dialog").accordion({
	heightStyle: "content"
});

// add a autocomplete to the search-field
$("#mod_students_name").autocomplete({
	source: function(request,response){
		// the term will be send to the server in order to generate the list
		$.ajax({
			url:"i.php",
			type:"post",
			dataType:"json",
			data:{key:"students",cmd:"LIST_ALL_STUDENTS_FGM",data:request.term}
		}).done(function(data){
			response(data);
		});
	},
	select: function(e,item){
		// populate the text-field with the acutal name and the hidden field with the student's id
		$("#mod_students_select_id").attr("value",item.item.id);
		$("#mod_students_name").val(item.item.value);
		return false;
	}
});

// if the user's picks a student, a dialog appears.
// we start with initalizing that dialog
$("#mod_students_edit_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Speichern": function(){
			var personalarray = {
				familyname:$("#mod_students_edit_dialog input[name=familyname]").attr("value"),
				givenname:$("#mod_students_edit_dialog input[name=givenname]").attr("value"),
				matrnr:$("#mod_students_edit_dialog input[name=matrnr]").attr("value"),
				term:$("#mod_students_edit_dialog input[name=term]").attr("value"),
				course:$("#mod_students_edit_dialog input[name=course]").attr("value"),
				email:$("#mod_students_edit_dialog input[name=email]").attr("value"),
				ingroup:$("#students_edit_personal_dialog select[name=ingroup]").val()
			};

			var editscorearray = [];
			$("#students_scores input").each(function(index){
				if( $(this).hasClass("field_edited") ){
					editscorearray.push({sheet:$(this).parent().children("input.students_sheet").first().attr("value"), newscore:$(this).attr("value")});
				}
			});

			var dataobject = {uid:$("#mod_students_edit_dialog input[name=uid]").attr("value"), personal:personalarray, newscores:editscorearray};
			$.ajax({
				url:"i.php",
				type:"POST",
				dataType:"json",
				data:{
					cmd:"EDIT_STUDENT",
					data:dataobject
				}
			}).done(function(data){
				$("#students_changes_dialog").empty();
				if(data.success == 'yes'){
					$("#students_changes_dialog").append("<div>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>");
					$("#mod_students_edit_dialog").dialog("close");
					$("#students_changes_dialog").dialog("open");
				}else{
					showErrorMsg("Die &Auml;nderungen konnten leider nicht gespeichert werden. "+data.errormsg);
				}
				$("#mod_students_edit_dialog input").removeClass("field_edited");
			});
		},
		"Abbrechen": function(){
			$("#mod_students_edit_dialog input").removeClass("field_edited");
			$( this ).dialog( "close" );
		}
	}
});

// we have a dialog that is shown when changes were saved
// we start with initalizing that dialog
$("#students_changes_dialog").dialog({
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
$( "#students_btn_find" ).button({
	icons: {
		primary: "ui-icon-search"
	},
	text: true
});

// the dialog is opend, when the user selects a student
$("#students_btn_find").on('click',function(e){
	e.preventDefault();

	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{
			cmd:"GET_STUDENT",
			data:$("#mod_students_select_id").attr("value")
		}
	}).done(function(data){
		if(data.success == 'yes'){
			// insert the loaded values
			$("#mod_students_edit_dialog input[name=familyname]").attr("value",data.familyname);
			$("#mod_students_edit_dialog input[name=givenname]").attr("value",data.givenname);
			$("#mod_students_edit_dialog input[name=matrnr]").attr("value",data.matrnr);
			$("#mod_students_edit_dialog input[name=term]").attr("value",data.term);
			$("#mod_students_edit_dialog input[name=course]").attr("value",data.course);
			$("#mod_students_edit_dialog input[name=email]").attr("value",data.email);
			$("#mod_students_edit_dialog input[name=ingrouphidden]").attr("value",data.ingroup);

			// load available groups
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{
					cmd:"LIST_ALL_GROUPS"
				}

			}).done(function(data){
				$("#mod_students_edit_dialog select[name=ingroup]").empty();
				for(var i = 0; i< data.length; i++){

					// check whether the currenct group is the user's one
					var slctd = "";
					if(data[i].groupid == $("#mod_students_edit_dialog input[name=ingrouphidden]").attr("value") ){
						slctd = "selected=\"selected\"";
					}

					// announce the number of free seats
					var free = "";
					if(data[i].seats == "inf"){
						free = "unbegrenzt";
					}else{
						free = (data[i].seats-data[i].students)+" freie Plätze";
					}

					$("#mod_students_edit_dialog select[name=ingroup]").append("<option "+slctd+" value="+data[i].groupid+">"+data[i].name+" ("+free+")</option>");
				}

			});			

			// load sheet scores
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{
					cmd:"GET_A_STUDENTS_SCORES",
					data:$("#mod_students_select_id").attr("value")
				}
			}).done(function(data){
				$("#students_scores").empty();
				$("#students_scores").append("<form></form>");
				for(var i=0;i<data.list.length;i++){
					$("#students_scores form").append("<p><input type='input' class='students_score' value='"+data.list[i].score+"'><input type='hidden' class='students_sheet' value='"+data.list[i].sheet+"'> Punkte auf Blatt "+data.list[i].sheet+" von "+data.list[i].corrector+".</p>");
				}

				// add an event handler
				// we mark each field with changes by adding the class 'field_edited'
				$("#mod_students_edit_dialog input.students_score").on('change',function(e){
					$(this).addClass("field_edited");
				});
			});

			// load sheet scores
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{
					cmd:"GET_ALL_EXAMS"
				}
			}).done(function(data){
				$("#students_exams").empty();
				for(var i=0;i<data.length;i++){
					$("#students_exams").append("<p><button class='students_examregister' title='Zur Klausur anmelden'><div class='hidden students_examregister_id'>"+data[i].exam+"</div>Zur Klausur "+data[i].examname+" anmelden.</button></p>");
				}

				// make the button nicer using jquery-ui
				$( ".students_examregister" ).button({
					icons: {
						primary: "ui-icon-plusthick"
					},
					text: true
				});
				// add an event handler
				$( ".students_examregister" ).on('click',function(e){
					var myID = $(this).children(".students_examregister_id").text();
					var dataobject = {exam:$(this).find(".students_examregister_id").text(), student:$("#mod_students_edit_dialog input[name=uid]").attr("value")};
					$.ajax({
						url:"i.php",
						type:"post",
						dataType:"json",
						data:{
							cmd:"REGISTER_STUDENT_TO_EXAM",
							data:dataobject
						}
					}).done(function(data){
						if(data.success == 'yes'){
							$("#students_changes_dialog").append("<div>Die Anmeldung war erfolgreich.</div>");
							$("#mod_students_edit_dialog").dialog("close");
							$("#students_changes_dialog").dialog("open");
						}else{
							showErrorMsg("Die Anmeldung ist fehlgeschlagen. "+data.errormsg);
						}
					});
				});

			});

			// add an event handler
			// we mark each field with changes by adding the class 'field_edited'
			$("#mod_students_edit_dialog input").on('change',function(e){
				$(this).addClass("field_edited");
			});

			//
			$("#mod_students_edit_dialog input[name=uid]").attr("value",$("#mod_students_select_id").attr("value"));

			// and finally, open the dialog
			$("#mod_students_edit_dialog").dialog("open");

		}else{
			showErrorMsg("Unbekannter Student.");
		}

		// remove the selection
		$("#mod_students_select_id").attr("value","");
		$("#mod_students_name").attr("value","");
	});

	
});

/** GENERATE LIST **/
// first, create the list dialog
$("#students_list_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:1000,
	buttons: {
		"OK": function(){
			$( this ).dialog( "close" );
		}
	}
});

// make the button nicer using jquery-ui
$( "#students_btn_generatelist" ).button({
	icons: {
		primary: "ui-icon-document"
	},
	text: true
});

$( "#students_btn_generatelist" ).on('click',function(e){
	e.preventDefault();
	var fields = new Array();
	$("#students_generatelist_fields").find("input:checked").each(function(index){		
		fields.push([$(this).parent().text(),$(this).attr('name')]);
	});

	var groups = new Array();
	$("#students_generatelist_groups").find("input:checked").each(function(index){
		groups.push($(this).attr('name'));
	});

	if(fields.length*groups.length == 0){
		showErrorMsg("Sie müssen mindestens ein Feld und mindestens eine Gruppe ausw&auml;hlen!");
	}else{
		$("#students_list_dialog").empty();
		$("#students_list_dialog").append("<table class='colored'><thead><tr></tr></thead><tbody></tbody></table>");
		for(var i = 0; i<fields.length; i++){
			$("#students_list_dialog thead tr").append("<th>"+fields[i][0]+"</th>");
		}

		$.ajax({
			url:"i.php",
			type:"post",
			dataType:"json",
			data:{cmd:"LIST_OF_ALL_STUDENTS_WITH_INFO",data:""}
		}).done(function(data){
			$("#students_list_dialog tbody").empty();
			for(i=0; i<data.length; i++){
				var theRow = "<tr>";
				for(j=0; j<fields.length; j++){
					switch(	fields[j][1] ){
						case 'lastname':
							theRow = theRow+"<td>"+data[i].familyname+"</td>";
							break;
						case 'firstname':
							theRow = theRow+"<td>"+data[i].givenname+"</td>";
							break;
						case 'matrnr':
							theRow = theRow+"<td>"+data[i].matrnr+"</td>";
							break;
						case 'term':
							theRow = theRow+"<td>"+data[i].term+"</td>";
							break;
						case 'course':
							theRow = theRow+"<td>"+data[i].course+"</td>";
							break;
						case 'email':
							theRow = theRow+"<td>"+data[i].email+"</td>";
							break;
						case 'group':
							theRow = theRow+"<td>"+data[i].ingroup+"</td>";
							break;
						case 'totalscore':
							theRow = theRow+"<td>"+data[i].totalscore+"</td>";
							break;
						case 'nrhandedin':
							theRow = theRow+"<td>"+data[i].nrhandedin+"</td>";
							break;
					}
					

				}
theRow = theRow+"</tr>";

				// only show students in enabled groups
				if(jQuery.inArray("group_"+data[i].ingroupid,groups)>=0){
					$("#students_list_dialog tbody").append(theRow);
				}

			}
			// make the table sortable	
			$("#students_list_dialog table").tablesorter();
		
	});

		$("#students_list_dialog").dialog("open");
	}

});



$.ajax({
	url:"i.php",
	type:"post",
	dataType:"json",
	data:{
		cmd:"LIST_ALL_GROUPS"
	}
}).done(function(data){
	for(var i =0; i<data.length;i++){
		// We generate a list of groups. If there is no possible to enrol in that group, the radio button is not present.
		$("#students_generatelist_groups").append("<input type='checkbox' name='group_"+data[i].groupid+"'>"+data[i].name+"</input>");
	}
	$("#students_generatelist_selectallgroups").on('click',function(e){
		e.preventDefault();
		$("#students_generatelist_groups input[type=checkbox]").attr("checked","checked");
	});
});

// Add functionalities to the 'select all' links. The select all groups link has to be set after groups have been loaded.
$("#students_generatelist_selectallfields").on('click',function(e){
	e.preventDefault();
	$("#students_generatelist_fields input[type=checkbox]").attr("checked","checked");
});
