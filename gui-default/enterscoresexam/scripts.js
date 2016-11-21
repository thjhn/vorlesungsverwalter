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
$( "#enterscoresexam_enter" ).button({
	icons: {
		primary: "ui-icon-check"
	},
	text: true
});


$("#enterscoresexam_name").autocomplete({
	source: function(request,response){
		// the term will be send to the server in order to generate the list
		$.ajax({
			url:"i.php",
			type:"post",
			dataType:"json",
			data:{cmd:"LIST_ALL_STUDENTS_FGM",subcmd:"FIND_STUDENTS",data:request.term}
		}).done(function(data){
			response(data);
		});
	},
	select: function(e,item){
		// populate the text-field with the acutal name and the hidden field with the student's id
		$(this).siblings("#enterscoresexam_select_id").attr("value",item.item.id);
		$(this).val(item.item.value);
		loadScores();
		return false;
	}
});



// add the 'submit' functionality
$("#enterscoresexam_enter").on('click',function(e){
	e.preventDefault();
	var scorestring = "";
	$("#enterscoresexam_points .enterscoresexam_credits").each(function(data){
		scorestring += "\""+$(this).attr("value")+"\",";
	});

	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{
			cmd:"ENTER_SCORE_EXAM",
			data:"{\"exam\":\""+$("#enterscoresexam_exam option:selected").attr("value")+"\",\"student\":\""+$("#enterscoresexam_select_id").attr("value")+"\",\"scores\":["+scorestring.substr(0,scorestring.length-1)+"], \"overwrite\":\""+$("#enterscoresexam_overwrite").prop("checked")+"\"}"
		}
	}).done(function(data){
		if(data.success=="yes"){
			showSuccessMsg("Ihre Eintragung von Punkten war erfolgreich.");
			// clear the input fields
			$("#enterscoresexam_select_id").attr("value","");
			$("#enterscoresexam_name").attr("value","");
			$("#enterscoresexam_points input").attr("value","");
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
	data:{cmd:"GET_ALL_EXAMS",data:""}
}).done(function(data){
	for(var i = 0; i < data.length; i++){
		if(data[i].enterscores == 'true'){
			$("#enterscoresexam_exam").append("<option value='"+data[i].exam+"'>"+data[i].examname+"</option>");
		}
	}
	adjustScoreBoxes();
});

function adjustScoreBoxes(){
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"GET_EXAM",data:$("#enterscoresexam_exam option:selected").attr("value")}
	}).done(function(data){
		if(data.success == 'yes'){
			$("#enterscoresexam_points").empty();
			for(i = 1; i <= data.problems; i++){
				$("#enterscoresexam_points").append("<div class='inputbox sm'><span class='label'>Aufgabe "+i+"</span><input type=\"text\" value=\"\" class=\"enterscoresexam_credits\" class='ui-corner-all'/></div>");
			}
			loadScores();
		}
	});
}

function loadScores(){
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"GET_SCORE_EXAM",
		  data:{exam:$("#enterscoresexam_exam option:selected").attr("value"),
		  student:$('#enterscoresexam_select_id').attr("value")}}
	}).done(function(data){
		if(data.success == 'yes'){
			$("#enterscoresexam_overwrite").prop("checked",false)
			var count = 0;
			$(".enterscoresexam_credits").each(function(){
				$(this).attr("value",data.scores[count++]);
			});
		}else{
			$(".enterscoresexam_credits").each(function(){
				$(this).attr("value","");
			});			
		}
	});
}

$("#enterscoresexam_exam").on('change',function(e){
	adjustScoreBoxes();
});
