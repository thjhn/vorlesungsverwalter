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
$( "#register_dialog" ).dialog({
	modal: true,
	autoOpen:false,
	buttons: {
		Ok: function() {
			$( this ).dialog( "close" );
		}
	}
});

function loadGroups(){
	$("#register_groups tbody").empty();
	// We have to insert the various groups
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{
			cmd:"LIST_ALL_GROUPS"
		}
	}).done(function(data){
		// while itearating we count the number of groups in which there are seats left; if these number equals zero we do not allow registering.
		var nrOfOpenGroups = 0;
		
		for(var i =0; i<data.length;i++){
			// We generate a list of groups. If there is no possible to enrol in that group, the radio button is not present.
			var seatsText = "";
			var selector = "<input type='radio' name='group' value='"+data[i].groupid+"'>";

			if(data[i].seats == "inf"){
				// There is an infinite number of seats
				seatsText = "unbegrenzt";
				nrOfOpenGroups++;
			}else{
				// There are free seats
				if((data[i].seats-data[i].students) > 0){
					seatsText = (data[i].seats-data[i].students)+" freie Plätze";
					nrOfOpenGroups++;
				}else{
					seatsText = "ausgebucht";
					selector = "<img src=\"client/icons/bullet_red.png\" alt=\"Rot\" title=\"Es sind keine Plätze mehr frei.\"/>";
				}
			}
			$("#register_groups tbody").append("<tr><td>"+selector+"</td><td>"+data[i].name+"</td><td>"+data[i].description+"</td><td>"+seatsText+"</td></tr>");
		}

		if(nrOfOpenGroups == 0){
			$(".register_registerform").parent().empty();
			$(".container").append("<div class='errorMsgInPanel'><p><b>Keine freie Gruppe verf&uuml;gbar.</b></p><p>Leider ist in keiner Gruppe ein Platz frei; daher ist auch keine Anmeldung zum Übungsbetrieb möglich. Kontaktieren Sie bitte das Vorlesungsteam, damit dieses Abhilfe schaffen kann.</div>");
		}
	});
}



// make the button nicer using jquery-ui
$( "#register_btn_submit" ).button({
	icons: {
		primary: "ui-icon-check"
	},
	text: true
});

// User wants to register a student
$("#register_btn_submit").on('click',function(e){
	e.preventDefault();
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:'REGISTER_NEW_STUDENT',data:"{"+
			"\"familyname\":\""+$(".register_registerform input[name=familyname]").attr('value')+"\","+
			"\"givenname\":\""+$(".register_registerform input[name=givenname]").attr('value')+"\","+
			"\"matrnr\":\""+$(".register_registerform input[name=matrnr]").attr('value')+"\","+
			"\"term\":\""+$(".register_registerform input[name=term]").attr('value')+"\","+
			"\"email\":\""+$(".register_registerform input[name=email]").attr('value')+"\","+
			"\"ingroup\":\""+$(".register_registerform input[name=group]").attr('value')+"\","+
			"\"course\":\""+$(".register_registerform select[name=course] option:selected").attr('value')+"\""+
			"}"
		}
	}).done(function(data){
		if(data.success=="yes"){
			// change the dialog's content
			$( "#register_dialog" ).empty();
			$( "#register_dialog" ).append("<p>Ihre Anmeldung zur Vorlesung war erfolgreich.</p>");
			$( ".register_registerform input").attr("value","");
			loadGroups();
			// open the dialog
			$("#register_dialog").dialog("open");
		}else{
			loadGroups();
			if(data.failures[0]=='DUPLICATE'){
				showErrorMsg("Ihre Anmeldung zur Vorlesung war <b>nicht</b> erfolgreich. Es wurde bereits ein Eintrag mit Ihrem Namen gefunden. Kontaktieren Sie bitte das Vorlesungsteam.");
			}else{
				showErrorMsg("Ihre Anmeldung zur Vorlesung war <b>nicht</b> erfolgreich. Füllen Sie die markierten Felder korrekt aus.");
			}

			// the array data.failures contains the labels of each filed that was not accepted by the server.
			for(var i = 0; i<data.failures.length; i++){
				$(".register_registerform input[name="+data.failures[i]+"]").parent().addClass("fail");
				$(".register_registerform input[name="+data.failures[i]+"]").on('change',function(e){
					$(this).parent().removeClass("fail");
				});
			}


		}
	});
});

function registerFinishTemplating(){
	loadGroups();
	// load the available courses
	$.ajax({
		url:"i.php",
		type:"post",
		dataType:"json",
		data:{cmd:'LIST_COURSES',data:""}
	}).done(function(data){
		if(data.success=="yes"){
			$(".register_registerform select[name=course]").empty();
			for(var i=0; i<data.courses.length; i++){
				$(".register_registerform select[name=course]").append("<option>"+data.courses[i]+"</option>");
			}
		}else{
			//TODO: ERRORMSG
		}
	});
}

// Check whether there are open registrationslots.
$.ajax({
	url:"i.php",
	type:"post",
	dataType:"json",
	data:{cmd:'IS_REGISTRATION_ALLOWED',data:""}
}).done(function(data){
	if(data.success!="yes"){
		// there are no open registrations slots; but maybe the user is an admin?
		$.ajax({
			url:"i.php",
			type:"post",
			dataType:"json",
			data:{"cmd":"GET_LOGIN"}
		}).done(function(data){
			// The users is logged in
			var skipClosing = false;
			if(data.status == 'in'){
				if(data.roles.indexOf('admin;')>-1){
					skipClosing = true;
				}
			}
console.log(skipClosing);
			if(!skipClosing){
				$(".container").empty();
				$(".container").append("<div class='errorMsgInPanel'><p><b>Anmeldung gesperrt.</b></p><p>Derzeit ist die Anmeldung zum &Uuml;bungsbetrieb <em>nicht</em> freigeschalten. Wenden Sie sich bitte an das Vorlesungsteam, wenn Sie dies f&uuml;r einen Fehler halten oder Sie Fragen dazu haben.</p></div>");
			}else{
				registerFinishTemplating();
				$(".container").prepend("<div class='errorMsgInPanel'><p><b>Anmeldung gesperrt.</b></p><p>Derzeit ist die Anmeldung zum &Uuml;bungsbetrieb <em>nicht</em> freigeschalten. Nur weil Sie Administrator sind, können Sie dies dennoch tun!</p></div>");
			}
		});
	}else{
		registerFinishTemplating();
	}
});



