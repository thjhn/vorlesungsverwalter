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

// make the button nicer using jquery-ui
$( "#enterscores_enter" ).button({
	icons: {
		primary: "ui-icon-check"
	},
	text: true
});

function enterscores_addANewNamebox(){
	$("#enterscores_thenameboxes").append("<div class='enterscores_anamebox'><input type=\"text\" value=\"\" class=\"enterscores_name\" class='ui-corner-all'/><input type=\"hidden\" value=\"\" class=\"enterscores_select_id\"/><button class='enterscores_removestudent'><span class='button_label'>Diesen Studenten entfernen</span></button><button class='enterscores_morestudents'><span class='button_label'>Weiteren Studenten hinzuf&uuml;gen</span></button></div>");
	enterscores_enableMoreLessStudentsButtons();
}

// the buttons to add or remove a students have to reinitalized each time
function enterscores_enableMoreLessStudentsButtons(){
	// first, make them nicer using jquery-ui
	$( ".enterscores_morestudents" ).button({
		icons: {
			primary: "ui-icon-plusthick"
		},
		text: false
	});	
	$( ".enterscores_removestudent" ).button({
		icons: {
			primary: "ui-icon-minusthick"
		},
		text: false
	});

	// second, say what happens on clicks
	// adding new fields
	$( ".enterscores_morestudents" ).off('click');
	$( ".enterscores_morestudents" ).on('click',function(e){
		e.preventDefault();
		enterscores_addANewNamebox();
	});	

	// removing fields
	$( ".enterscores_removestudent" ).off('click');
	$( ".enterscores_removestudent" ).on('click',function(e){
		e.preventDefault();

		// only remove fields when there is at least one left!
		if($(".enterscores_anamebox").length > 1){
			$(this).closest(".enterscores_anamebox").remove();
		}else{
			$(".enterscores_select_id").attr("value","");
			$(".enterscores_name").attr("value","");
		}
	});

	// third, add a autocomplete to the search-field
	$(".enterscores_anamebox input.enterscores_name").autocomplete({
		source: function(request,response){
			// the term will be send to the server in order to generate the list
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{cmd:"LIST_ALL_STUDENTS_FG",subcmd:"FIND_STUDENTS",data:request.term}
			}).done(function(data){
				response(data);
			});
		},
		select: function(e,item){
			// populate the text-field with the acutal name and the hidden field with the student's id
			$(this).siblings("input.enterscores_select_id").attr("value",item.item.id);
			$(this).val(item.item.value);
			return false;
		}
	});
}

enterscores_enableMoreLessStudentsButtons();


// add the 'submit' functionality
$("#enterscores_enter").on('click',function(e){
	e.preventDefault();
	var studentsstring = "";
	$(".enterscores_anamebox input.enterscores_select_id").each(function(data){
		studentsstring += ",\""+$(this).attr("value")+"\"";
	});

	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{
			cmd:"ENTER_SCORE",
			data:"{\"sheet\":\""+$("#enterscores_sheet option:selected").attr("value")+"\",\"student\":["+studentsstring.substr(1)+"],\"score\":\""+$("#enterscores_credits").attr("value")+"\"}"
		}
	}).done(function(data){
		// clear the input fields
		$("input.enterscores_select_id").attr("value","");
		$("input.enterscores_name").attr("value","");
		$("#enterscores_credits").attr("value","");

		if(data.success=="yes"){
			showSuccessMsg("Ihre Eintragung von Punkten war erfolgreich.");
		}else{
			showErrorMsg("Ihre Eintragung von Punkten war <b>nicht</b> erfolgreich. "+data.errormsg);
		}
	}).fail(function(){
		showErrorMsg("Die Punkte konnten nicht an den Server übermittelt werden. Versuchen Sie es später erneut. Kontaktieren Sie ggf. den Administrator.");
	});
});


// get the number of sheets and generate the corresponding drop down menu
$.ajax({
	url:"i.php",
	type:"post",
	dataType:"json",
	data:{cmd:"GET_NR_OF_SHEETS",data:""}
}).done(function(data){
	if(data.success=='yes'){
		for(var i = 1; i <= data.sheets; i++){
			$("#enterscores_sheet").append("<option value='"+i+"'>Blatt "+i+"</option>");
		}
	}
});
