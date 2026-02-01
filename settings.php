<?php
//site function
$sitefunction='CRM';

//folders
$invoice_folder='facturi';
$receipts_folder='chitante';
$annexes_folder='anexe';
$carsheets_folder='foiparcurs';
$pe_folder='deconturi';
$worksheets_folder='pontaje';
$dbbackup_folder='dbbackup';
$upload_folder='uploads';
$exports_folder='exports';
$efactura_folder='efactura';
$receivedeinvoices='efacturipdf';
$error_folder='erori';
$efacturareceived_folder='efacturiprimite';
$efacturadownload_folder='efacturadownload';
$newsletter_folder='newsletter';
$admin_folder='admin';
$contracts_folder='contracte';
$charts_folder='grafice';
$transactions_folder='extrase';
$projects_folder='proiecte';
$documents_folder='documente';
$elearning_folder='elearning';

//freedays
$holidays=array(
"2026-01-1", //Anul nou / fix
"2026-01-2",  //Anul nou/fix
"2026-01-6",  //Boboteaza
"2026-01-7",  //Sfântul Ioan
"2026-01-24",  //Unirea fix
"2026-04-10", //Paste /se schimba anual
"2026-04-13", //Paste /se schimba anual
"2026-05-1", //1 mai / fix
"2026-06-1", //1 iunie / fix
"2026-08-15", //Sfanta Maria / fixa
"2026-11-30", //Sfântul Andrei / fixa
"2026-12-1", //Ziua nationala / fixa
"2026-12-25", //Craciun /fixa
"2026-12-26", //Craciun /fixa
"2025-01-1", //Anul nou / fix
"2025-01-2",  //Anul nou/fix
"2025-01-3",  //Punte bugetari
"2025-01-6",  //Boboteaza
"2025-01-7",  //Sfântul Ioan
"2025-01-24",  //Unirea fix
"2025-04-18", //Paste /se schimba anual
"2025-04-20", //Paste /se schimba anual
"2025-04-21", //Paste /se schimba anual
"2025-05-1", //1 mai / fix
"2025-05-2", //Punte
"2025-06-1", //1 iunie / fix
"2025-06-8", //Rusalii / se schimba anual
"2025-06-9", //Rusalii / se schimba anual
"2025-08-15", //Sfanta Maria / fixa
"2025-11-30", //Sfântul Andrei / fixa
"2025-12-1", //Ziua nationala / fixa
"2025-12-25", //Craciun /fixa
"2025-12-26", //Craciun /fixa
"2025-12-29", //Punte
"2025-12-30", //Punte
"2025-12-31", //Punte
"2024-01-1", //Anul nou / fix
"2024-01-2",  //Anul nou/fix
"2024-01-24",  //Unirea fix
"2024-05-1", //1 mai / fix
"2024-05-2", //2 mai / Punte
"2024-05-3", //3 mai / Vinerea Mare
"2024-05-6", //6 mai / Lunea Paștelui
"2024-06-1", //1 iunie / fix
"2024-06-24", //Rusalii / se schimba anual
"2024-08-14", //Sfanta Maria / fixa
"2024-08-15", //Sfanta Maria / fixa
"2024-08-16", //Sfanta Maria / fixa
"2024-11-30", //Sfântul Andrei / fixa
"2024-12-1", //Ziua nationala / fixa
"2024-12-23", //Punte
"2024-12-24", //Punte
"2024-12-25", //Craciun /fixa
"2024-12-26", //Craciun /fixa
"2024-12-27", //Punte bugetari
"2024-12-30", //Punte bugetari
"2024-12-31", //Punte bugetari
); 

$skipdays = array("Sat", "Sun"); 


//other
if (!defined('ABSPATH')) {
	define('ABSPATH', dirname(__FILE__));
}
date_default_timezone_set('Europe/Bucharest');
date_default_timezone_set(date_default_timezone_get());
$array = array('ro_RO.ISO8859-1', 'ro_RO.ISO-8859-1', 'ro', 'ro_RO', 'rom', 'romanian');
setlocale(LC_ALL, $array);

include '_site/settings.local.php';
//include 'settings.prod.php';
include '_site/company.php';
?>