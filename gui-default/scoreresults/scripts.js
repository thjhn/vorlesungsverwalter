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


var thead_tr_1st = "<th rowspan='2' class='scoreresults_cols_names'>Name</th><th rowspan='2' class='scoreresults_cols_matrnr'>MatrNr</th>";
var thead_tr_2nd = "";
// get the number of sheets and generate the corresponding tableheader
var nr_of_sheets;
$.ajax({
	url:"i.php",
	type:"post",
	dataType:"json",
	data:{cmd:"GET_NR_OF_SHEETS",data:""}
}).done(function(data){
	if(data.success=='yes'){
		nr_of_sheets = data.sheets;
		for(var i = 1; i <= data.sheets; i++){
			thead_tr_2nd += "<th class='scoreresults_cols_scores'>"+i+"</th>";
		}
	}
	thead_tr_1st += "<th colspan='"+(data.sheets+1)+"' class='scoreresults_cols_scores'>&Uuml;bungen</th>";
	thead_tr_2nd += "<th class='scoreresults_cols_scores'>Sum</th>";

	// get the exams and generate the corresponding tableheader
	var examids = [];
	var examproblems = [];
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"GET_ALL_EXAMS",data:""}
	}).done(function(data){
		for(var i = 0; i < data.length; i++){
			examids.push(data[i].exam);
			examproblems.push(parseInt(data[i].problems));
			thead_tr_1st += "<th colspan='"+(parseInt(data[i].problems)+1)+"' class='scoreresults_cols_exams'>"+data[i].examname+"</th>";
			for(var j = 1; j <= data[i].problems; j++){
				thead_tr_2nd += "<th class='scoreresults_cols_exams'>"+j+"</th>";
			}
			thead_tr_2nd += "<th class='scoreresults_cols_exams'>Sum</th>";
		}

		$("#scoreresults_list thead").append("<tr>"+thead_tr_1st+"</tr>");	
		$("#scoreresults_list thead").append("<tr>"+thead_tr_2nd+"</tr>");

		$.ajax({
			url:"i.php",
			type:"post",
			dataType:"json",
			data:{
				cmd:"LIST_ALL_STUDENTS_SCORES"
			}
		}).done(function(data){
			$.each(data,function(index,value){
				var scoreTDs = "";
				var sum = 0.0;
				if(value.scores != null){
					for(var i=0; i<value.scores.length; i++){
						scoreTDs = scoreTDs + "<td class='scoreresults_cols_scores'>" + value.scores[i] + "</td>";
						if(value.scores[i] != ''){
							sum += parseFloat(value.scores[i]);
						}
					}
					scoreTDs = scoreTDs + "<td class='scoreresults_cols_scores'>" + sum + "</td>";
				}else{
					for(var i = 0; i<=nr_of_sheets; i++){
						// add empty cell (including for sum row)
						scoreTDs = scoreTDs + "<td></td>";
					}

				}

				for(var i=0; i<examids.length; i++){
					var cur_examid = examids[i];
					var emptyExam = "";
					for( var j=0; j<= examproblems[i]; j++){
						emptyExam += "<td class='scoreresults_cols_exams'></td>";
					}
					if(value.exams != null && value.exams[cur_examid] != null){
						sum = 0.0;
						for(j = 0; j< value.exams[cur_examid].scores.length; j++){
							scoreTDs += "<td class='scoreresults_cols_exams'>"+value.exams[cur_examid].scores[j]+"</td>";
							sum += parseFloat(value.exams[cur_examid].scores[j]);
						}
						scoreTDs += "<td class='scoreresults_cols_exams'>"+sum+"</td>";
					}else scoreTDs += emptyExam;
				}
				$("#scoreresults_list tbody").append("<tr><td class='scoreresults_cols_names'>"+value.familyname+", "+value.givenname+"</td><td class='scoreresults_cols_matrnr'>"+value.matrnr+"</td>"+scoreTDs+"</tr>");
			});
	
			// Make the table sortable
			$("#scoreresults_list").tablesorter();
			// Hide matrnr and exams if the user is 'only' a corrector
			// (in which case we won't get any data to show in these rows anyways)
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{"cmd":"GET_LOGIN"}
			}).done(function(data){
				if(data.roles.indexOf("admin")<0){
					$("#scoreresults_cols input[name='scoreresults_cols_matrnr']").prop("checked",false);
					$("#scoreresults_cols input[name='scoreresults_cols_matrnr']").prop("disabled", true);
					$(".scoreresults_cols_matrnr").hide();
					$("#scoreresults_cols input[name='scoreresults_cols_exams']").prop("checked",false);
					$("#scoreresults_cols input[name='scoreresults_cols_exams']").prop("disabled",true);
					$(".scoreresults_cols_exams").hide();
				}
			});
		});
	
	});
});


// there are checkboxes that allow hiding some rows
$("#scoreresults_cols input").on('click',function(e){
	if(this.checked){
		$("."+this.name).show();

	}else{
		$("."+this.name).hide();
	}
});


