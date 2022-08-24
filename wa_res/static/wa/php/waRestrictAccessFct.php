<?php

session_start();

$filename_php = $wa_page_infos['name_infos']['php'];
$basename = $wa_page_infos['name_infos']['basename'];
$wafx_base_res = $wa_page_infos['name_infos']['wafx_base_res'];
$lang = $wa_page_infos['name_infos']['lang'];


$wafx_key_flash_message = 'wafx_key_flash_message';
$_SESSION[$wafx_key_flash_message] = "";


$wafx_auth_valid = false;

if (waSessionIsAuthenticated($wa_authorized_uuids,$global_webacappella_auth_chain)===true)
{
	$wafx_auth_valid = true;
}
/////

if ($wafx_auth_valid!=true)
{
	if (array_key_exists('auth_wa_identifier', $_POST) && array_key_exists('auth_wa_password', $_POST))
	{
			$wafx_identifier = $_POST['auth_wa_identifier'];
			$wafx_password = $_POST['auth_wa_password'];
			if (waAuthCheck($wafx_identifier,$wafx_password,$wa_authorized_uuids,$global_webacappella_auth_chain)===true)
			{
				$wafx_auth_valid = true;
				header('Location: '.$filename_php);
				
				//exit();
			}
			else
			{
				$_SESSION[$wafx_key_flash_message] = waWebMessage('WebMessage:RestrictArea:error authentication failed',$lang);

			}
	}
}

if ($wafx_auth_valid!=true)
{

	$message_welcome = waWebMessage('WebMessage:RestrictArea:welcome message',$lang);


    include_once(__DIR__.'/../../../generated/.global.restrict.auth.php');

	$_SESSION[$wafx_key_flash_message] = "";
	exit();
}
