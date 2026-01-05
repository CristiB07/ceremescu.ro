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
	exit();
}

// Check if user is admin
if (!isset($_SESSION['clearence']) || $_SESSION['clearence'] != 'ADMIN') {
	header("location:$strSiteURL/index.php?message=unauthorized");
	exit();
}

$code = isset($_GET['code']) ? htmlspecialchars($_GET['code'], ENT_QUOTES, 'UTF-8') : '';
$op = isset($_GET['op']) ? htmlspecialchars($_GET['op'], ENT_QUOTES, 'UTF-8') : '';

if ($op == "gettoken" && empty($code)){

		$url = $authorize_url;
		$url .='?client_id=' . urlencode($site_client_id);
		$url .='&client_secret=' . urlencode($site_client_secret);
		$url .='&response_type=code';
		$url .='&token_content_type=jwt';
		$url .='&redirect_uri=' . urlencode($redirect_uri);
		echo "<div class=\"callout success\"><p><a href=\"" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\" class=\"button\">$strGetToken</a></p></div>";
}
//get initial code
elseIf (!empty($code)) {

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
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 2);
		$jsonobj = curl_exec($ch);
		
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			curl_close($ch);
			die('<div class="callout alert">cURL Error: ' . htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8') . '</div>');
		}
		curl_close($ch);
		
		$arr = json_decode($jsonobj, true);
		
		if (!isset($arr["access_token"]) || !isset($arr["refresh_token"])) {
			die('<div class="callout alert">Invalid response from authorization server</div>');
		}
		
		$retval['access_token']=$arr["access_token"];
		$retval['refresh_token']=$arr["refresh_token"];
		$acces_token=htmlspecialchars($retval['access_token'], ENT_QUOTES, 'UTF-8');
		$refresh_token=htmlspecialchars($retval['refresh_token'], ENT_QUOTES, 'UTF-8');
		$d=strtotime("+90 days");
		$enddate= htmlspecialchars(date("d.m.Y",$d), ENT_QUOTES, 'UTF-8');
		echo "<div class=\"callout success\">" . htmlspecialchars($strAccessToken, ENT_QUOTES, 'UTF-8') . " = <strong>". $acces_token  ."</strong>.<br />". htmlspecialchars($strRefreshToken, ENT_QUOTES, 'UTF-8') ."= <strong>". $refresh_token ."</strong><br />". htmlspecialchars($strDate, ENT_QUOTES, 'UTF-8') . "=". $enddate.".</div>";
}
else {
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
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 2);
		$jsonobj = curl_exec($ch);
		
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			curl_close($ch);
			die('<div class="callout alert">cURL Error: ' . htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8') . '</div>');
		}
		curl_close($ch);
		
		$arr = json_decode($jsonobj, true);
		
		if (!isset($arr["access_token"]) || !isset($arr["refresh_token"])) {
			die('<div class="callout alert">Invalid response from authorization server</div>');
		}
		
		$retval['access_token']=$arr["access_token"];
		$retval['refresh_token']=$arr["refresh_token"];
		
		$acces_token=htmlspecialchars($retval['access_token'], ENT_QUOTES, 'UTF-8');
		$refresh_token=htmlspecialchars($retval['refresh_token'], ENT_QUOTES, 'UTF-8');
		$d=strtotime("+90 days");
		$enddate= htmlspecialchars(date("d.m.Y",$d), ENT_QUOTES, 'UTF-8');
		echo "<div class=\"callout success\">" . htmlspecialchars($strAccessToken, ENT_QUOTES, 'UTF-8') . " = <strong>". $acces_token  ."</strong>.<br />". htmlspecialchars($strRefreshToken, ENT_QUOTES, 'UTF-8') ."= <strong>". $refresh_token ."</strong><br />". htmlspecialchars($strDate, ENT_QUOTES, 'UTF-8') . "=". $enddate.".</div>";
}
include '../bottom.php';
die;
?>