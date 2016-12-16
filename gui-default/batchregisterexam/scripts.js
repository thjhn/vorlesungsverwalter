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

// here we add the list of students
function updateStudentTable(){
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"LIST_ALL_EXAMS"}
	}).done(function(data){
		$("#batchregisterexam_exams").empty();
		for(var i=0;i<data.length;i++){
			$("#batchregisterexam_exams").append("<p><button class='batchregisterexam_examsregister' title='Zur Klausur anmelden'><div class='hidden batchregisterexam_examsregister_id'>"+data[i].exam+"</div>Zur Klausur "+data[i].examname+" anmelden.</button></p>");
		}

		// make the button nicer using jquery-ui
		$( ".batchregisterexam_examsregister" ).button({
			icons: {
				primary: "ui-icon-plusthick"
			},
			text: true
		});

		// add an event handler
		$( ".batchregisterexam_examsregister" ).on('click',function(e){
			var examid = $(this).find(".batchregisterexam_examsregister_id").text();
			$(".batchregisterexam_list_check:checked").each(function(index){
				var dataobject = {exam:examid, student:$(this).parent().parent().find(".batchregisterexam_list_idcol").text()};
				$.ajax({
					url:"i.php",
					type:"post",
					dataType:"json",
					data:{
						cmd:"REGISTER_STUDENT_TO_EXAM",
						data:dataobject
					}
				}).done(function(data){
				});
			});

			
		});

	});
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"LIST_OF_ALL_STUDENTS_WITH_INFO"}
	}).done(function(studentdata){
		$.each(studentdata,function(key,value){
			$("#batchregisterexam_list tbody").append("<tr><td>"+value.familyname+"</td><td>"+value.givenname+"</td><td class='batchregisterexam_list_idcol'>"+value.id+"</td><td><input type='checkbox' class='batchregisterexam_list_check'/></tr>");
		});
	});
}
updateStudentTable();

$("#batchregisterexam_checkall a").on('click',function(e){
	e.preventDefault();
	$(".batchregisterexam_list_check").each(function(index){
		$(this).prop('checked',true);
	});
});
