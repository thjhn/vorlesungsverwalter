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


function scorestats_generateSheetStat(){
	
	// clean display before drawing anew
	$(scorestats_chartdiv).empty();

	// fetch statistics from server
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"GET_SCORESTATS",data:{sheet:$("#scorestats_sheet option:selected").attr("value")}}
	}).done(function(data){
		if(data.success=='yes'){
			maxscore = 0;
			histlist = [];
			no_data_to_display = true;
			$.each(data.stat, function(group, group_stat) {
			if(group == $("#scorestats_group option:selected").attr("value")){
				local_histlist = [];
				empty = true;
				$.each(group_stat.hist, function(index, value) {
					empty = false;
					no_data_to_display = false;
					local_histlist.push([index,value]);
					if(maxscore<index) maxscore=parseInt(index);
				});
				if(!empty) histlist.push(local_histlist);
			}
			});
			maxscore = Math.ceil(maxscore+0.5);

			if(no_data_to_display){
				$("#scorestats_chartdiv").append("<div class='errorMsgInPanel'><p><b>Keine Punkte.</b></p><p>F&uuml;r Ihre Auswahl sind noch keine Punkte eingetragen.</p></div>");
			}else{
				$.jqplot('scorestats_chartdiv', histlist, {
					seriesDefaults:{
						renderer:$.jqplot.BarRenderer,
						rendererOptions: {shadowOffset: 0, barMargin:15},
						pointLabels: {show: true, formatString: '%d'}
					},
					animate: !$.jqplot.use_excanvas,
					axes: {
						xaxis: {min: 0, max : maxscore, tickInterval:0.5, showTicks: false},
						x2axis: {tickInterval:1, min: 0, max : maxscore, showTicks: true, show:true},
						yaxis: {tickInterval:1.0}
					}
				});
			}
		}
	});
}

$("#scorestats_sheet").on('change',function(e){
	scorestats_generateSheetStat();
});
$("#scorestats_group").on('change',function(e){
	scorestats_generateSheetStat();
});


$("#scorestats_sheet").append("<option value='0'>Alle</option>");
// get the number of sheets and generate the corresponding drop down menu
$.ajax({
	url:"i.php",
	type:"post",
	dataType:"json",
	data:{cmd:"GET_NR_OF_SHEETS",data:""}
}).done(function(data){
	if(data.success=='yes'){
		for(var i = 1; i <= data.sheets; i++){
			$("#scorestats_sheet").append("<option value='"+i+"'>Blatt "+i+"</option>");
		}
	}
});

$("#scorestats_group").append("<option value='allgroups'>Alle Gruppen</option>");
// get the groups and generate the corresponding drop down menu
$.ajax({
	url:"i.php",
	type:"post",
	dataType:"json",
	data:{cmd:"LIST_ALL_GROUPS",data:""}
}).done(function(data){
	for(var i = 0; i < data.length; i++){
		$("#scorestats_group").append("<option value='"+data[i].groupid+"'>"+data[i].name+"</option>");
	}
});

// call the graph the first time.
scorestats_generateSheetStat();

