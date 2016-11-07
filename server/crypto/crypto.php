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
 *  Contains the class Student.
 */

include_once('server/logger/logger.php');

/**
 * Provides the cryphtographic routines used.
 *
 * @author Thomas Jahn vv3@t-und-j.de
 */


class Crypto{

	/**
	 * Encrypt data using the team's common public key.
	 *
	 * @param $data Data that are to be encrypted.
	 *
	 * @return The encrypted data. Or false if there was any error.
	 */
	public static function encrypt_for_team($data){
		$fp=fopen("keys/vv3publickey.pem","r");
		if($fp === false){
			Logger::log("Could not open public key file.",Logger::LOGLEVEL_ERROR);
			return false;
		}
		$pub_key_string=fread($fp,8192);
		fclose($fp);
		if(openssl_public_encrypt($data,$crypttext,$pub_key_string)===false){
			Logger::log("Could not use public key file.",Logger::LOGLEVEL_ERROR);
			return false;
		}
		return base64_encode($crypttext);
	}

	/**
	 * Decrypts data using the team's common public key.
	 *
	 * @param $data Data to be decrypted.
	 * @param $auth The user's auth-object.
	 *
	 * @return The decrypted data. Or false if there was any error.
	 */
	public static function decrypt_team($data, $auth){
		$fp=fopen("keys/vv3privatekey.pem","r");
		if($fp === false){
			Logger::log("Could not open private key file.", Logger::LOGLEVEL_ERROR);
			return false;
		}
		$priv_key_string=fread($fp,8192);
		fclose($fp);

		$password = $auth->privkeykey;
		$res = openssl_get_privatekey($priv_key_string,$password);
		if($res){
			openssl_private_decrypt(base64_decode($data), $plaintext, $res);
			return $plaintext;
		}else{
			Logger::log("Could not use private key file.",Logger::LOGLEVEL_ERROR);
			return false;
		}
	}

	/**
	 * Encrypt data using a symmetric cipher and the team's common key.
	 *
	 * @param $data Data that are to be encrypted.
	 *
	 * @return The encrypted data. Or false if there was any error.
	 *
	 * @see decrypt_in_team
	 */
	function encrypt_in_team($data,$auth){
		$method = 'AES128';

		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
		$crypttext = openssl_encrypt($data,$method,$auth->privkeykey,0,$iv);
		if($crypttext === false){
			return false;
		}

		return base64_encode($iv.$crypttext);
	}

	/**
	 * Decrypt data using a symmetric cipher and the team's common key.
	 *
	 * @param $data Data that are to be encrypted.
	 *
	 * @return The encrypted data. Or false if there was any error.
	 *
	 * @see decrypt_in_team
	 */
	function decrypt_in_team($data,$auth){
		$method = 'AES128';
		$iv_length = openssl_cipher_iv_length($method);

		$msg_enc = substr(base64_decode($data),$iv_length);
		$iv = substr(base64_decode($data),0,$iv_length);
		$plaintext = openssl_decrypt($msg_enc,$method,$auth->privkeykey,0,$iv);

		if($plaintext === false){
			return false;
		}

		return $plaintext;
	}

	/**
	 * Encrypt the privkeykey with a password given.
	 *
	 * @param $password The encryption password to be used.
	 * @param $auth The current user's auth object.
	 *
	 * @return The encrypted privkeykey or false if any error occured.
	 */
	function encrypt_privkeykey($password,$auth){
		$method = 'AES256';
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
		$crypttext = openssl_encrypt($auth->privkeykey,$method,$password,0,$iv);

		if($crypttext === false){
			return false;
		}

		return base64_encode($iv.$crypttext);
	}

	/**
	 * Decrypt the privkeykey with a password given.
	 *
	 * @param $data The data to be decrypted
	 * @param $password The encryption password to be used.
	 *
	 * @return The encrypted data. Or false if there was any error.
	 *
	 * @see decrypt_in_team
	 */
	function decrypt_privkeykey($data,$password){
		$method = 'AES256';
		$iv_length = openssl_cipher_iv_length($method);

		$msg_enc = substr(base64_decode($data),$iv_length);
		$iv = substr(base64_decode($data),0,$iv_length);
		$plaintext = openssl_decrypt($msg_enc,$method,$password,0,$iv);

		if($plaintext === false){
			return false;
		}

		return $plaintext;
	}
}

?>
