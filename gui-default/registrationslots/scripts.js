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
$("#registrationslots_changes_dialog").dialog({
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
$("#registrationslots_error_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Ok": function(){
			$( this ).dialog( "close" );
		}
	}
});

// add the datepickes
$( "#registrationslots_edit_dialog_start" ).datepicker({
	numberOfMonths: 3,
	"dateFormat":"dd.mm.yy"
});
$( "#registrationslots_edit_dialog_end" ).datepicker({
	numberOfMonths: 3,
	"dateFormat":"dd.mm.yy"
});

// if the user's picks an user, a dialog appears.
// we start with initalizing that dialog
$("#registrationslots_edit_dialog").dialog({
	autoOpen: false,
	modal:true,
	width:500,
	buttons: {
		"Speichern": function(){
			var start = $("#registrationslots_edit_dialog_start").attr('value');
			var start_enc = start.substr(6,4)+"-"+start.substr(3,2)+"-"+start.substr(0,2)+"T"+$("#registrationslots_edit_dialog_starttime").attr('value')+":00";
			var end = $("#registrationslots_edit_dialog_end").attr('value');
			var end_enc = end.substr(6,4)+"-"+end.substr(3,2)+"-"+end.substr(0,2)+"T"+$("#registrationslots_edit_dialog_endtime").attr('value')+":00";

			var dataobject = {slotid:$("#registrationslots_edit_dialog_id").attr("value"), start:start_enc, end:end_enc};
console.log(dataobject);

			$.ajax({
				url:"i.php",
				type:"POST",
				dataType:"json",
				data:{
					cmd:"EDIT_REGISTRATIONSLOT",
					data:dataobject
				}
			}).done(function(data){
				$("#registrationslots_changes_dialog").empty();
				if(data.success == 'yes'){
					$("#registrationslots_changes_dialog").append("<div>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>");
					refreshSlotsTable();
				}else{
					$("#registrationslots_changes_dialog").append("<div>Die &Auml;nderungen konnten leider nicht gespeichert werden. "+data.errormsg+"</div>");
				}

				$("#registrationslots_edit_dialog").dialog("close");
				$("#registrationslots_changes_dialog").dialog("open");
			});
		},
		"Abbrechen": function(){
			$("#users_edit_dialog input").removeClass("field_edited");
			$( this ).dialog( "close" );
		}
	}
});




// make the button nicer using jquery-ui
$( "#registrationslot_addslot" ).button({
	icons: {
		primary: "ui-icon-plusthick"
	},
	text: true
});

// add an event to the 'add a user' button
$("#registrationslot_addslot").on('click',function(e){
	e.preventDefault();
	$("#registrationslots_edit_dialog").dialog("open");
	$("#registrationslots_edit_dialog_start").attr("value","");
	$("#registrationslots_edit_dialog_starttime").attr("value","00:00");
	$("#registrationslots_edit_dialog_end").attr("value","");
	$("#registrationslots_edit_dialog_endtime").attr("value","00:00");
	$("#registrationslots_edit_dialog_id").attr("value","_new");
});


// all we need in order to get the table
function refreshSlotsTable(){
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"LIST_REGISTRATIONSLOTS",data:""}
	}).done(function(data){
		if(data.success=='yes'){
			$("#registrationslots_slotlist tbody").empty();
			for(i=0; i<data.slots.length; i++){
				var start = data.slots[i].start;
				var end = data.slots[i].end;
				$("#registrationslots_slotlist tbody").append("<tr><td class='registrationslots_rowid'>"+data.slots[i].id+"</td><td class='registrationslots_rowstart'>"+start.substr(8,2)+"."+start.substr(5,2)+"."+start.substr(0,4)+" "+start.substr(11,2)+":"+start.substr(14,2)+"</td><td class='registrationslots_rowend'>"+end.substr(8,2)+"."+end.substr(5,2)+"."+end.substr(0,4)+" "+end.substr(11,2)+":"+end.substr(14,2)+"</td><td><img src=\"client/icons/bullet_edit.png\" class=\"registrationslots_editslot\" alt=\"Editieren\" title=\"Diesen Zeitraum editieren.\"></td></tr>");
			}

			// Activate tooltips for the newly added pictures
			$(document).tooltip();

			$(".registrationslots_rowid").hide();

			$(".registrationslots_editslot").on('click',function(e){
				e.preventDefault();
				$("#registrationslots_edit_dialog").dialog("open");
				$("#registrationslots_edit_dialog_id").attr("value",$(this).parents('tr').find('.registrationslots_rowid').text());
				$("#registrationslots_edit_dialog_start").attr("value",$(this).parents('tr').find('.registrationslots_rowstart').text().substr(0,10));
				$("#registrationslots_edit_dialog_starttime").attr("value",$(this).parents('tr').find('.registrationslots_rowstart').text().substr(11,5));
				$("#registrationslots_edit_dialog_end").attr("value",$(this).parents('tr').find('.registrationslots_rowend').text().substr(0,10));
				$("#registrationslots_edit_dialog_endtime").attr("value",$(this).parents('tr').find('.registrationslots_rowend').text().substr(11,5));
			});
			
		}
	});
}
refreshSlotsTable();

