<?php
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
 *
 *  ----
 *  This file is a extremly dirty installation script.
 *  It should be rewritten as soon as possible.
 */


  function generateGroups(){
    $out = "";
    $out .= "<?xml version=\"1.0\" standalone=\"yes\"?>\n";
    $out .= "<groups>\n";
    $out .= "</groups>\n";
    return $out;
  }
  function generateUser($username,$realname,$privkeykey){
    $out = "";
    $out .= "<?xml version=\"1.0\" standalone=\"yes\"?>\n";
    $out .= "<users>\n";
    $out .= "	<user username=\"$username\" realname=\"$realname\" privkeykey = \"$privkeykey\" enabled=\"true\" is_corrector=\"true\" is_admin=\"true\" is_dev=\"true\"/>\n";
    $out .= "</users>\n";
    return $out;
  }
  function generateConfig($title,$term,$lecturer,$sheets,$courses){
    $out = "";
    $out .= "<?xml version=\"1.0\"?>\n";
    $out .= "<config>\n";
    $out .= "	<lecture>$title</lecture>\n";
    $out .= "	<term>$term</term>\n";
    $out .= "	<lecturer>$lecturer</lecturer>\n";
    $out .= "	<sheets>$sheets</sheets>\n";
    $out .= "	<registrationslots>\n";
    $out .= "	</registrationslots>\n";
    $out .= "	<courses>\n";
    $courses_list = explode("|",$courses);
    for($i = 0; $i<count($courses_list); $i++){
      $out .= "		<course>".$courses_list[$i]."</course>\n";
    } 	 
    $out .= "	</courses>\n";
    $out .= "	<menu>\n";
    $out .= "		<box title=\"Registrieren\">\n";
    $out .= "			<module key=\"register\" order=\"1\"/>\n";
    $out .= "		</box>\n";
    $out .= "		<box title=\"Punkte\">\n";
    $out .= "			<module key=\"enterscores\"/>\n";
    $out .= "			<module key=\"scoresentered\"/>\n";
    $out .= "			<module key=\"scoreresults\"/>\n";
    $out .= "		</box>\n";
    $out .= "		<box title=\"Konfiguration\">\n";
    $out .= "			<module key=\"settings\"/>\n";
    $out .= "			<module key=\"groups\"/>\n";
    $out .= "			<module key=\"users\"/>\n";
    $out .= "			<module key=\"exams\"/>\n";
    $out .= "			<module key=\"registrationslots\"/>\n";
    $out .= "		</box>\n";
    $out .= "		<box title=\"Studenten\">\n";
    $out .= "			<module key=\"students\"/>\n";
    $out .= "		</box>\n";
    $out .= "		<box title=\"Entwicklung\">\n";
    $out .= "			<module key=\"dev\"/>\n";
    $out .= "		</box>\n";
    $out .= "	</menu>\n";
    $out .= "</config>\n";
    return $out;
  }
  function checkFile($file,$is_a_file){
    $errors = 0;
    if($is_a_file){
      if(file_exists($file)){
        print("<p><b>Warning.</b> The file <code>$file</code> already exists. This will be overwritten if you continue! You may loose your keys to decrypt existing data.</p>");
        if(!is_writeable($file)){
          $errors++;
          print("<p><b>Error.</b> The file <code>$file</code> is not writeable.</p>");
        }
      }
    }else{
     if(!is_writeable($file)){
       $errors++;
       print("<p><b>Error.</b> The directory <code>$file</code> is not writeable.</p>");
     }
    }
    return $errors;
  }

  print("<html><head></head><body><h1>VV3 ugly Installation.</h1>");
  // The installation process is conducted in several steps
  // the steps is indicated by $_POST['step']
  // of course at start the value of $_POST['step'] is empty.
  if(!isset($_POST['step'])){
    $step = 0;
  }else{
    // TODO: do a sanity check!
    $step = $_POST['step'];
  }

  switch($step){
  case 0:
    print("<h2>Testing</h2>");
    $errors = 0;
    $errors += checkFile("data/config.xml",True);
    $errors += checkFile("data/users.xml",True);
    $errors += checkFile("data/groups.xml",True);
    $errors += checkFile("data/",False);
    $errors += checkFile("keys/vv3privatekey.pem",True);
    $errors += checkFile("keys/vv3publickey.pem",True);
    $errors += checkFile("log/main.log",True);
    $errors += checkFile("keys/",False);
    if($errors == 0){
      print("<p>Tests passed.</p>");
    }else{
      print("<p>$errors tests failed. Stopping installation.</p>");
      break;
    }
	
    print("<h2>Config</h2>");
    print("<p>Please provide the following data about your lecture.</p>");
    print("<form action='install.php' method='post'>");
    print("<input type='hidden' name='step' value='1'/>");
    print("<table>");
    print("<tr><td>Title</td><td><input type='input' name='title'/></td></tr>");
    print("<tr><td>Term</td><td><input type='input' name='term'/></td></tr>");
    print("<tr><td>Lecturer</td><td><input type='input' name='lecturer'/></td></tr>");
    print("<tr><td>Username</td><td><input type='input' name='username'/></td></tr>");
    print("<tr><td>Password</td><td><input type='password' name='password'/></td></tr>");
    print("<tr><td>(Max) No of sheets</td><td><input type='input' name='sheets'/></td></tr>");
    print("<tr><td>Courses</td><td><input type='input' name='courses'/><br/>Multiple courses possible. Separate them by the pipe symbol |.</td></tr>");
    print("<tr><td>Ready?</td><td><input type='submit'/></td></tr>");
    break;
  case 1:
    // before writing the config we create the keys.
    $key_config = array(
      "digest_alg" => "AES128",
      "private_key_bits" => 4096,
      "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    $theprivkeykey = base64_encode(openssl_random_pseudo_bytes(128));
   
    // Create the private and public key
    $res = openssl_pkey_new($config);

    // Extract the private key from $res to $privKey
    openssl_pkey_export($res, $privKey,$theprivkeykey);

    // Extract the public key from $res to $pubKey
    $pubKey = openssl_pkey_get_details($res);
    $pubKey = $pubKey["key"];

    print("<p>Private Key generated.</p>");
    print("<pre>$privKey</pre>");
    print("<p>Public Key generated.</p>");
    print("<pre>".$pubKey."</pre>");

    $f_privkey = fopen("keys/vv3privatekey.pem","w");
    if($f){
      print("Writing private key failed. File not writeable.");
    }else{
      fwrite($f_privkey,$privKey);
      $f_pubkey = fopen("keys/vv3publickey.pem","w");
      fclose($f);
      print("<p>Private key written.</p>");
      if($f){
        print("Writing public key failed. File not writeable.");
      }else{
        fwrite($f_pubkey,$pubKey);
      print("<p>Public key written.</p>");
        fclose($f);
        $method = 'AES256';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $crypttext = openssl_encrypt($theprivkeykey,$method,$_POST['password'],0,$iv);
        $privkeykey = base64_encode($iv.$crypttext);
        $f = fopen("data/users.xml","w");
        if(!$f){
          print("Was not able to write the users file. Check whether data-directory is writable!");
        }else{
          fwrite($f,generateUser($_POST['username'],$_POST['lecturer'],$privkeykey));              
          fclose($f);
          print("<p>Users file written.</p>");
        }
        $f = fopen("data/config.xml","w");
	if(!$f){
          print("Was not able to write the config file. Check whether data-directory is writable!");
        }else{
          fwrite($f,generateConfig($_POST['title'],$_POST['term'],$_POST['lecturer'],$_POST['sheets'],$_POST['courses']));
          print("<p>Config written.</p>");
        }
        $f = fopen("data/groups.xml","w");
	if(!$f){
          print("Was not able to write the groups file. Check whether data-directory is writable!");
        }else{
          fwrite($f,generateGroups());
          print("<p>Groups written.</p>");
        }
        print("<p>Installtion finished. <a href=\"index.html\">Go to your VV</a>.</p>");

      }
    }
    break;  	 
  }

  
  print("</body></html>");
?>

