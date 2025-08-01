<?php
    if(!isset($_SESSION)) 
    { 
        session_start(); 
	} 
if (isset($_SESSION['uid']) && !empty($_SESSION['uid']))
		{
   
	function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$d = date("Y-m-d H:i:s");
$uid= $_SESSION['uid'];
$sessionID=session_id();
$ip=getRealIpAddr();

$mSQL = "INSERT INTO ezpub_logs(";
	$mSQL = $mSQL . "log_utilizator_id,";
	$mSQL = $mSQL . "log_IP_address,";
	$mSQL = $mSQL . "log_utilizator_time,";
	$mSQL = $mSQL . "log_utilizator_page,";
	$mSQL = $mSQL . "log_utilizator_session)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .$ip . "', ";
	$mSQL = $mSQL . "'" .$d . "', ";
	$mSQL = $mSQL . "'" .$actual_link . "', ";
	$mSQL = $mSQL . "'" .$sessionID ."')";
				
//It executes the SQL
ezpub_query($conn,$mSQL);
	}
?>