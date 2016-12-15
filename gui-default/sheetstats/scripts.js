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


function sheetstats_generateSheetStat(){
	var sheetstats_graphheight = 0; // maximum height necessary
	
	// clean display before drawing anew
	$(sheetstats_chartdiv).empty();

	// fetch statistics from server
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:"GET_SHEETSTATS"}
	}).done(function(data){
		if(data.success=='yes'){
			maxscore = 0;
			histlist = [];
			for(var sheet = 1; sheet < data.stat.length; sheet++){
				var curstat = data.stat[sheet][$("#sheetstats_group option:selected").attr("value")];
				if(curstat['count'] != 0){
					var expect = curstat['sum']/curstat['count'];
					var varianz = curstat['sqsum']/curstat['count'] - curstat['sum']/curstat['count']*curstat['sum']/curstat['count'];
					histlist.push([sheet,curstat['max'],expect+Math.sqrt(varianz),curstat['min'],expect-Math.sqrt(varianz)]);
					if(sheetstats_graphheight<curstat['max']) sheetstats_graphheight = parseFloat(curstat['max'])+2.0;
				}
			}
			console.log(sheetstats_graphheight);
			if(histlist.length > 0){
				$.jqplot('sheetstats_chartdiv', [histlist], {
					seriesDefaults:{
						//renderer:$.jqplot.BarRenderer,
						//rendererOptions: {shadowOffset: 0},
						renderer:$.jqplot.OHLCRenderer,
						rendererOptions: {candleStick:true}
					},
					animate: !$.jqplot.use_excanvas,
					axes: {
						xaxis: {min: 0, max : data.stat.length, tickInterval:1.0, showTicks: true, show:true},
						yaxis: {min: 0, max : sheetstats_graphheight, tickInterval:1.0}
					}
				});
			}		
		}
	});
}

$("#sheetstats_sheet").on('change',function(e){
	sheetstats_generateSheetStat();
});
$("#sheetstats_group").on('change',function(e){
	sheetstats_generateSheetStat();
});

$("#sheetstats_group").append("<option value='allgroups'>Alle Gruppen</option>");
// get the groups and generate the corresponding drop down menu
$.ajax({
	url:"i.php",
	type:"post",
	dataType:"json",
	data:{cmd:"LIST_ALL_GROUPS",data:""}
}).done(function(data){
	for(var i = 0; i < data.length; i++){
		$("#sheetstats_group").append("<option value='"+data[i].groupid+"'>"+data[i].name+"</option>");
	}
});

// call the graph the first time.
sheetstats_generateSheetStat();

