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

		jQuery().ready(function(){
			// Hide the Login Panel at startup.
			$("#loginpanel").hide();
			updateLoginStuff();
			updateMenu();

			// set slogan
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{"cmd":"GET_BASIC_INFO"}
			}).done(function(data){
				$("#slogan").empty();
				$("#slogan").append(data.lecture+" ("+data.lecturer+", "+data.term+")");
			});
		});

		// Here we do all the login/logout stuff
		function updateLoginStuff(){
			// First of all, we check the user's current status
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{"cmd":"GET_LOGIN"}
			}).done(function(data){
				// The users is NOT logged in
				if(data.status == 'out'){
					// Update the information in the top-line
					$("#login").empty();
					$("#login").append("Gastzugang <a href=''><img src='client/icons/key.png'/></a>");
					$("#loginpanel").empty();
					$("#loginpanel").append("<form><div>Username:<br><input type='input' name='username'></div><div>Password:<br><input type='password' name='password'></div><div><input id='loginbutton' type='submit' value='Login'></div><div><a href=''>Close this panel.</a></form>");
					$("#login a").on('click',function(e){
						e.preventDefault();
						$("#loginpanel").slideToggle(1000);
					});
					$("#loginpanel a").on('click',function(e){
						e.preventDefault();
						$("#loginpanel").slideUp(1000);
					});
					$("#loginbutton").on('click',function(e){
						// User wants to be logged in.
						// We send a corresponding query to the server
						// and recall this function
						username = $("#loginpanel input[name=username]").attr('value');
						password = $("#loginpanel input[name=password]").attr('value');
						e.preventDefault();
						$.ajax({
							url:"i.php",
							type:"post",
							dataType:"json",
							data:{"cmd":"LOGIN","username":username,"password":password}
						}).done(function(msg){
							if(msg.success=='yes'){
								$("#loginpanel").hide();
								updateMenu();
								updateLoginStuff();
							}else{
								alert("The provided login information could not been verified.");
							}
						});

					}); 

					$("#loginpanel").hide();
				}else{
					// Update the information in the top-line
					$("#login").empty();
					$("#login").append("Hello "+data.username+"! <a href=''>Logout</a>.");
					$("#login a").on('click',function(e){
						// The User wants to be logged out
						// We send a corresponding query to the server
						// and recall this function
						e.preventDefault();
						$.ajax({
							url:"i.php",
							type:"post",
							data:{"cmd":"LOGOUT"}
						}).done(function(){
							updateMenu();
							updateLoginStuff();
						});
					});
				}
			});
		}

function updateMenu(){
			$( "#switches" ).accordion();			
			$( "#switches" ).accordion( "destroy" );
			$("#switches").empty();
			$.ajax({
				url:"i.php",
				type:"post",
				dataType:"json",
				data:{"cmd":"GET_MENU"}
			}).done(function(data){
				if(data.success == 'yes'){

					for(var i = 0; i<data.menu.length; i++){
						var boxString = "";
						for(var j = 0; j<data.menu[i].modules.length; j++){
							boxString += "<div class='aSwitch'><span class='theSwitchKey'>"+data.menu[i].modules[j].key+"</span>"+data.menu[i].modules[j].title+"</div>";
						}
						$("#switches").append("<h3>"+data.menu[i].title+"</h3><div>"+boxString+"</div>");

					}

					// hide the newly added switchKey spans
					$("#switches .theSwitchKey").hide();

					// what happens when a user clicks a switch:
					$("#switches .aSwitch").on('click',function(e){
						e.preventDefault();
						// extract the key of the clicked switch
						var key = $(this).children('.theSwitchKey').first().text();

						// Get all information about the tool with the key key
						$.ajax({
							url:"i.php",
							type:"post",
							dataType:"json",
							data:{cmd:"GET_TOOL",key:key},
						}).done(function(tool){
							// check if the tool is available
							if(tool.status=='ok'){
								// put the template on the panel
								$("#panel").empty();
								$("#panel").append("<div class='container'>"+jQuery.base64('decode',tool.template)+"</div>");

								// load the tool's scriptfile (and execute those)
								console.log("Loading script for key="+key,this);
								$.ajax({		
									url:"i.php",
									dataType:"script",
									type:"post",
									data:{cmd:"GET_TOOL_SCRIPT",key:key}
								}).done(function(){
									console.log("Script loaded for key="+key,this);
								});
							}else{
								// could not load the tool
								showErrorMsg("Das gew&uuml;nschte Werkzeug konnte nicht vom Sever geladen werden!");
								console.log("Tried to get the tool '"+key+"' but the server returned the status '"+tool.status+"'.",this);
							}
						});
					});
			

					// we need a special effect for the menu ;)
					$("#switches").accordion({
						heightStyle: "content"
					});
				}else{
					// TODO
				}

				// switch to the first panel
				$("#switches .aSwitch").first().click();

			});
}
