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

// get the number of sheets and generate the corresponding tableheader
$.ajax({
	url:"i.php",
	type:"post",
	dataType:"json",
	data:{cmd:"GET_NR_OF_SHEETS",data:""}
}).done(function(data){
	if(data.success=='yes'){
		for(var i = 1; i <= data.sheets; i++){
			$("#scoreresults_list thead tr").append("<th>"+i+"</th>");
		}
	}
	$("#scoreresults_list thead tr").append("<th>Summe</th>");
});

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
			for(var i=0; i<value.scores.length; i++){
				scoreTDs = scoreTDs + "<td>" + value.scores[i] + "</td>";
				if(value.scores[i] != '--'){
					sum += parseFloat(value.scores[i]);
				}
			}
		scoreTDs = scoreTDs + "<td>" + sum + "</td>";
			$("#scoreresults_list tbody").append("<tr><td>"+value.familyname+", "+value.givenname+"</td>"+scoreTDs+"</tr>");
		});

		$("#scoreresults_list").tablesorter();
	});
