<?php
// updated 15.05.2025

// //mySQL 
$host="localhost:3306"; // Host name
$db_name="cnsx001_master"; // Database name
$username="root"; // Mysql local username
$password=".07panteraroz07"; // Mysql local password
//$username="cnsx001_certusr"; // Mysql server username
//$password="lasama1"; // Mysql server password

//email settings
$SmtpServer="smtp.office365.com";
$SmtpPort="587";
$SmtpUser="office@consaltis.ro";
$SmtpPass="07bugsbunny#";

//hddpath
$hddpath='/Users/cristianbanu/Sites/data/';
//$hddpath='/home/consaltis.ro/domains/crm.consaltis.ro/data';

//app setting
$cartenabled=1; //0 no cart, 1 with shoping cart
$blogenabled=0; //0 no blog, 1 with blog
$crmenabled=0; //0 no crm, 1 with crm
$billingenabled=0; //0 no billing, 1 with billing
$salesenabled=0; //0 no sales, 1 with sales
$cmsenabled=1; //0 no cms, 1 with cms
$feedreaderenabled=0; //0 no feedreader, 1 with feedreader
$elearningenabled=1; //0 no elearning, 1 with elearning


//other
date_default_timezone_set('Europe/Bucharest');
date_default_timezone_set(date_default_timezone_get());
$array = array('ro_RO.ISO8859-1', 'ro_RO.ISO-8859-1', 'ro', 'ro_RO', 'rom', 'romanian');
setlocale(LC_ALL, $array);