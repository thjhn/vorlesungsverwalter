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


function scorestats_generateSheetStat(field_id, sheet){
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"GET_SCORESTATS",data:{sheet:sheet}}
	}).done(function(data){
		if(data.success=='yes'){
			maxscore = 0;
			histlist = [];
			$.each(data.stat, function(group, group_stat) {
				local_histlist = [];
				empty = true;
				$.each(group_stat.hist, function(index, value) {
					empty = false;
					local_histlist.push([index,value]);
					if(maxscore<index) maxscore=parseInt(index);
				});
				if(!empty) histlist.push(local_histlist);
			});

			maxscore = maxscore+0.5;
			$.jqplot(field_id,  histlist,
				{ legend:{showLabels:true},
				  animate: !$.jqplot.use_excanvas,
				  series:[{renderer:$.jqplot.BarRenderer, label:'A'},{renderer:$.jqplot.BarRenderer},{renderer:$.jqplot.BarRenderer}],
				  axes: { xaxis: {min: 0, max : maxscore, tickInterval:0.5},
					yaxis: {tickInterval:1.0}
				  },
        			  legend: {show: true, location:'ne'}
				});
		}
	});
}

$("#scorestats_sheet").on('change',function(e){
	scorestats_generateSheetStat('scorestats_chartdiv_single', $("#scorestats_sheet option:selected").attr("value"));
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
			$("#scorestats_sheet").append("<option value='"+i+"'>Blatt "+i+"</option>");
		}
	}
});


scorestats_generateSheetStat('scorestats_chartdiv_all', 0);
scorestats_generateSheetStat('scorestats_chartdiv_single', 1);

