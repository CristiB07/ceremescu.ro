<?php
//update 29.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Manage Token";
include '../dashboard/header.php';

$d = date("d-m-Y");
$dataincarcarii = date("Y-m-d H:i:s");
//$s = date('d-m-Y', strtotime($d . ' +10 day'));

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}

	$code=$_GET['code'];
if (isset($_GET['op']) && $_GET['op']=="gettoken" && empty($code)){

		$url = $authorize_url;
		$url .='?client_id='.$site_client_id;
		$url .='&client_secret='.$site_client_secret;
		$url .='&response_type=code';
		$url .='&token_content_type=jwt';
		$url .='&redirect_uri='.$redirect_uri;
		echo "<div class=\"callout success\"><p><a href=\"".$url."\" class=\"button\">$strGetToken</a></p></strong></div>";
}
//get initial code
ElseIf (!empty($code)) {

		$retval=array();
		$url = $token_url;
		$fields = [
			'client_id'      => $site_client_id,
			'client_secret' => $site_client_secret,
			'code'         => $code,
			'redirect_uri'	=> $redirect_uri,
			'grant_type' => 'authorization_code',
			'token_content_type' => 'jwt'
		];
		echo $url;
		$fields_string = http_build_query($fields);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
		$jsonobj = curl_exec($ch);
		$arr = json_decode($jsonobj, true);
		$retval['access_token']=$arr["access_token"];
		$retval['refresh_token']=$arr["refresh_token"];
		$acces_token=$retval['access_token'];
		$refresh_token=$retval['refresh_token'];
		$d=strtotime("+90 days");
		$enddate= date("d.m.Y",$d);
		echo "<div class=\"callout success\">" . $strAccessToken." = <strong>". $acces_token  ."</strong>.<br />". $strRefreshToken."= <strong>". $refresh_token ."</strong><br />". $strDate. "=". $enddate.".</div>";
}
Else {
//refresh code

	$retval=array();
		$url = $token_url;
		$fields = [
			'client_id'      => $site_client_id,
			'client_secret' => $site_client_secret,
			'refresh_token' => $site_client_refresh,
			'redirect_uri'	=> $redirect_uri,
			'grant_type' => 'refresh_token',
			'token_content_type' => 'jwt'
		];
		$fields_string = http_build_query($fields);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
		$jsonobj = curl_exec($ch);
		$arr = json_decode($jsonobj, true);
		$retval['access_token']=$arr["access_token"];
		$retval['refresh_token']=$arr["refresh_token"];
		
		$acces_token=$retval['access_token'];
		$refresh_token=$retval['refresh_token'];
		$d=strtotime("+90 days");
		$enddate= date("d.m.Y",$d);
		echo "<div class=\"callout success\">" . $strAccessToken." = <strong>". $acces_token  ."</strong>.<br />". $strRefreshToken."= <strong>". $refresh_token ."</strong><br />". $strDate. "=". $enddate.".</div>";
}
include '../bottom.php';
die;
?>