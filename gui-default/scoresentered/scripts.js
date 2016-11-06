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
$( "#scoresentered_dialog" ).dialog({
	modal: true,
	autoOpen:false,
	buttons: {
		Ok: function() {
			$( this ).dialog( "close" );
		}
	}
});

$("#scoresentered_editdialog").dialog({
	modal: true,
	autoOpen:false,
	buttons: {
		Abbrechen: function() {
			$( this ).dialog( "close" );
		},
		Speichern: function() {
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{cmd:"EDIT_SCORE",data:{"sid":$("#scoresentered_editdialog_sid").attr("value"),"newscore":$("#scoresentered_editdialog_newscore").attr("value")}}
			}).done(function(data){
				if(data.success == 'yes'){
					// change the dialog's content
					$( "#scoresentered_dialog" ).empty();
					$( "#scoresentered_dialog" ).append("<p>Ihre Eintragung von Punkten war erfolgreich.</p>");
					// open the dialog
					$("#scoresentered_dialog").dialog("open");
					// update the scores table
					updateScoreTable();
				}else{
					// produce an error dialog
					showErrorMsg("<p>Ihre Eintragung von Punkten war <b>nicht</b> erfolgreich. "+data.errormsg+"</p>");
				}
				$("#scoresentered_editdialog").dialog( "close" );
			});
		}
	}

});

// here we add the list of scores
function updateScoreTable(){
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"LIST_ALL_CORRECTORS_SCORES"}
	}).done(function(data){
		$("#scoresentered_entered tbody").empty();
		if(data.success=="yes"){
			for(i = 0; i<data.list.length; i++){
				// generate list of students
				var studs = "";
				for(j=0; j<data.list[i].students.length; j++){
					studs += data.list[i].students[j];
					if(j<(data.list[i].students.length-1)){
						studs += ", ";
					}
				}
	
				///$("#scoresentered_entered tbody").append("<tr><td class='scoresentered_entered_sheet'>"+data.list[i].sheet+"</td><td class='scoresentered_entered_students'>"+studs+"</td><td><span class='scoresentered_entered_score'>"+data.list[i].score+"</span><div class='scoresentered_entered_sid'>"+data.list[i].sid+"</div><img class='scoresentered_entered_edit' src=\"client/icons/bullet_edit16x16.png\" alt='Editieren' title='Dieses Übungsblatt bearbeiten.'></td></tr>");
				$("#scoresentered_entered tbody").append("<tr><td class='scoresentered_entered_sheet'>"+data.list[i].sheet+"</td><td class='scoresentered_entered_students'>"+studs+"</td><td><span class='scoresentered_entered_score'>"+data.list[i].score+"</span></td><td><div class='scoresentered_entered_sid'>"+data.list[i].sid+"</div><button class='scoresentered_entered_edit' title='Punktezahl dieses Blattes ver&auml;ndern.'>Editieren</button></td></tr>");

			// make the button nicer using jquery-ui
			$( ".scoresentered_entered_edit" ).button({
				icons: {
					primary: "ui-icon-pencil"
				},
				text: false
			});


			}
			// hide the sids
			$(".scoresentered_entered_sid").hide();
			// Activate tooltips for the newly added pictures
			$(document).tooltip();
			// add the change-functionality
			$(".scoresentered_entered_edit").on('click',function(e){
				e.preventDefault();
				sid = $(this).parent().children(".scoresentered_entered_sid").text();
				studs = $(this).parent().parent().children(".scoresentered_entered_students").text()
				score = $(this).parent().parent().children("td").children(".scoresentered_entered_score").text()
				sheet = $(this).parent().parent().children(".scoresentered_entered_sheet").text()
				
				$("#scoresentered_editdialog").empty();
				$("#scoresentered_editdialog").append("<div>Das Blatt <i>"+sheet+"</i> von den Studenten <i>"+studs+"</i> bearbeitet und mit <i>"+score+"</i> Punkten bewertet.</div><div>Geben Sie die neue Punktezahl ein: <form><input type='hidden' id='scoresentered_editdialog_sid' value='"+sid+"'><input type='input' id='scoresentered_editdialog_newscore'></form></div>");
				$("#scoresentered_editdialog").dialog("open");
				// TODO EDIT
			});
			
		}else{
			// an error occured
			console.log("ERROR: "+data.errormsg);
		}
	});
}
updateScoreTable();
