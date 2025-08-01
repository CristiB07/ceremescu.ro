<?php
// Application settings
$strSiteName='eProceduri.ro';
$strSiteOwner='Cert Plus';
$strSiteOwnerData='SC Cert Plus SRL, înregistrată la Registrul Comerțului sub nr. J2023009169400, având CUI RO 48179521, cu sediul social în București, Sector 3, Nerva Traian nr. 3, City Business Center, Biroul 3, cod poștal 031 041';
$strSiteURL='http://localhost/masterapp.ro';
$siteURLShort='master.ro';
$siteCompanyWebsite='https://www.eproceduri.ro';
$siteCompanyEmail='office@certplus.ro';
$siteCompanyEmailMasked='office @ certplus.ro';
$siteCompanyShortSite='eproceduri.ro';
$siteOGImage='certplus_image.jpg';
$strSiteDescription='Magazin online de proceduri și alte documente a '. $strSiteOwner;
$strDescription='mApp 1.0';
$strSiteVersion='1.0';
$strBuildVersion='29.07.2025';
$strDBVersion='MySQL';
$strKeywords='';


//css
$color="#3E5B6F";
$cssname="certplus";

//icons
$iconFacebook="<a href=\"https://www.facebook.com/consaltisconsultantasiaudit\"><img src=\"https://www.consaltis.ro/img/fb.png\" title=\"Facebook\" width=\"25\" height=\"25\"/></a>";
$iconLinkedin="<a href=\"https://www.linkedin.com/company/consaltis-consultanta-si-audit\"><img src=\"https://www.consaltis.ro/img/in.png\" title=\"LinkedIn\" width=\"25\" height=\"25\"/></a>";

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


///openapi
$openapikey="x-api-key: f2yehr9M22MyZnK7Z2cC7zvgrydyJkm1xE8xtpiJ_LEySf_FcA";
//$openapikey="x-api-key: XXCDbsBFAwjVXCjaVEqEDBhFT1ghs61xxQ1vynQbdUsMqVs8SQ";

//efactura
$site_client_id='098a25a50510f33f18c8e7cc37747e8a7e3ee71dcca0e565';
$site_client_secret='1ec059fa1c391ebfff90d8039e812403404efa9e53757e8a7e3ee71dcca0e565';
$site_client_token='ewogICJhbGciOiJSUzUxMiIsCiAgImtpZCI6ImFuYWZfMjAyM18yMDI0Igp9.ewogICJ0b2tlbl90eXBlIjoiQmVhcmVyIiwKICAic2NvcGUiOiJjbGllbnRhcHBpZCBpc3N1ZXIgcm9sZSBzZXJpYWwiLAogICJzY29wZV9kYXRhIjpbCiAgICB7CiAgICAgICAgImlkIjoiY2xpZW50YXBwaWQiLAogICAgICAgICJ2YWx1ZSI6IjA5OGEyNWE1MDUxMGYzM2YxOGM4ZTdjYzM3NzQ3ZThhN2UzZWU3MWRjY2EwZTU2NSIKICAgIH0sCiAgICB7CiAgICAgICAgImlkIjoiaXNzdWVyIiwKICAgICAgICAidmFsdWUiOiJjZXJ0U0lHTiIKICAgIH0sCiAgICB7CiAgICAgICAgImlkIjoicm9sZSIsCiAgICAgICAgInZhbHVlIjoiSEVMTE8sRUZBQ1RVUkEsRVRSQU5TUE9SVCxTUlZfRUZBQ1RVUkEiCiAgICB9LAogICAgewogICAgICAgICJpZCI6InNlcmlhbCIsCiAgICAgICAgInZhbHVlIjoiMjI6MGY6YTc6NGY6Njg6N2U6ZTE6NTY6ZWU6Y2Q6MWY6NzAiCiAgICB9CiAgXSwKICAiaXNzIjoiaHR0cHM6Ly9sb2dpbmNlcnQuYW5hZi5ybyIsCiAgImNsaWVudGFwcGlkIjoiMDk4YTI1YTUwNTEwZjMzZjE4YzhlN2NjMzc3NDdlOGE3ZTNlZTcxZGNjYTBlNTY1IiwKICAiZWZhY3R1cmEiOiJFRkFDVFVSQUBTUlZfRUZBQ1RVUkEiLAogICJldHJhbnNwb3J0IjoiRVRSQU5TUE9SVCIsCiAgImhlbGxvIjoiSEVMTE8iLAogICJpc3N1ZXIiOiJjZXJ0U0lHTiIsCiAgInJvbGVzIjoiSEVMTE9ARUZBQ1RVUkFARVRSQU5TUE9SVEBTUlZfRUZBQ1RVUkEiLAogICJzZXJpYWwiOiIyMjowZjphNzo0Zjo2ODo3ZTplMTo1NjplZTpjZDoxZjo3MCIsCiAgInN1YiI6IjIyMGZhNzRmNjg3ZWUxNTZlZWNkMWY3MCIsCiAgImp0aSI6IjM0YzkyYzk5MDc3ZjUzZmU2YjNjN2M5MjcxOWJjZDU4ZjI5MTViYTgyNjQzNzkzMDI0NzcwZDUwZTFiNGZiNDUiLAogICJpYXQiOjE3NTM4MTUzOTYsCiAgImV4cCI6MTc2MTU5MTM5NiwKICAibmJmIjoxNzUzODE1MDk2Cn0.zIGyfgSBz46IsOY7VywxkB3HNzZN46jIXeFiqSMeNTprQkdieqbJv8UVMeyFQjj422YciulcQ81Er7HjHYw8kjF3eUZAzKeSuH5L_ogAKePg5cz2DSZCF5Z0Wbuc6bnd8_184xRWC4aiGQOBlH5wjlqQ7TfDPxEW4bi03sRp4PjA_6fBGsj65_rFpcng_hoZbGYZu8bRJE4VlBKCzMNAm3b5A9PvzKrHFbgfI9oJ44AVusNWpZ7OwGz0mVt2_W3wMr4ywhD1AsSRTOE7OVYaBznzkWcqDtjBkLESDDQXfMCsVBHsKTNWkUF0YuC_rA8ybeBoF4k1NZ5iN5RC5zWtQg';
$site_client_refresh='4eXFRAz1k1FC3Nti7XNEn6ANl5ndf_tWbLTrG_EMlpGRSDazOzBq6IC9bjxB-Xjt_L_LfiYICX-cSJQrD08btRAHRSw8gtxhQKuB3cIa45qrTJcuDXiEd07-V2lB9xFoG0VX5K3W3Gm64XE0zH_vGk4DwcNjpPsxpignjXOy4W_G-yfHZ18_N1WV-hwumTFaOFhjsC-Fz7fygIYkrE4IEa3lp-DlUnpHGagblSrpvn-IM-Yl4c-JGlisGdJzB2a4Prg9w-9HN5_EGyssAj5ILoCJBTAEbrdmAFDyiM9v5I8lzAORj6Y41m9ttmO6VSxmbc5YhGtBmyZ0S4-7w-_WbQlTEqXpBL-QVs_-UOyV-a7ExT9M95RJdf1PBGqEtqW-uu45WkTVOamlpSB6A07ZNVZ_8JIsQdfZyGnAl4DFL_oeIBAAHEuhNwAhz7qiU7_3o1Weq28uc0avk4BWYppvUs3FcYjMwJ2v8A7ooLdzh_ahSMTbpcyWTnYoLnkJMcBU_6XtV-nJNwhfuxHnWx-gMPWN-km_DSEVhWDgdc8AfJs-_RXhAjXx5Z9wk6Fiacqsb0KdzA0gsHsZL3GCawTdr3M_P1cAHj9Z0qaBTMx5Ugu3ZqmTzpyFmI0PeNGPLhiNqdowMyWHe0LOfwv_KbmSONlRsANCBDJTplsHWyhKP4ucVdverroJWROv1EDM5EKxqR4rynMgugNetgwmUFXb9XCbPdLyO4DvPaOhO5snpoRLmpArjjCeAmejJ47abonpAh9hSeSnF-5LzsLCUVK5uP37TUf4HzSbud5JAak0NnokAd0BEbE18NY5qe3Yv595ON3Dez4BmHd1DXgj76J1Z3bJnNQOsURiBE-Q6v-su95jmU1dA4Ia4hVvsS907-nzHHDnnrj0mU24F7Cy3j8Dg-DxiMPdO2XOfqVl43DP9Puw0K3YqvzRHpFYesfaG_cTkIfi7L5M645oZS5XCWm_IRtJQDyKrZpEao0-THj0ewQDj7zn5DO6ru_zruVuRVicx4u1mhgBqjx61S0vBB2q4XW2ZCwqu_MyABUMS7kKKEPPfi-r8tndDSPqUueG9vbA3Xd0ROEdcU_hSL9w-iGkEKSfGdjF3yL4jnhqMIXRiU-ekjw1pdsnKvh7xGyBcXYSvcgx52HzfJeDckk0QQsCRIGBy6we-w0GjO6WFvwqqL4SVkRTXUbpGUZbvCFRXMAdb4mW7WRHgfeylgQsp_tYWqTZjx9JYf_GQeW7_14U8PvlZxEZokktU3Ple247I_AT6yvwSqjj1fW6UXiIGLMGEU2tzceBszlaB5m17GGeMW_Fh1fBJzfFiTauj66818w8Fnll56nbYI7hrEL3UVtfe4Mt0A-umlTztMasutYL5EAToJIyVM2kqCq7lIYhhjRrTE9FECI6G9CZ18wSe2kiszo0hXfa6-sRPxjVjvcmBXDalQa4ztIqDQizsS9UwViGjzARBdc';

$expiration_date='2025-10-28';

$authorize_url='https://logincert.anaf.ro/anaf-oauth2/v1/authorize';
$token_url='https://logincert.anaf.ro/anaf-oauth2/v1/token';
$redirect_uri='https://crm.consaltis.ro/admin/managetoken.php';
$status_url='https://api.anaf.ro/prod/FCTEL/rest/stareMesaj?id_incarcare=';
$download_url='https://api.anaf.ro/prod/FCTEL/rest/descarcare?id=';
$upload_url='https://api.anaf.ro/prod/FCTEL/rest/upload?standard=UBL&cif=';
$messages_url='https://api.anaf.ro/prod/FCTEL/rest/listaMesajeFactura?zile=60&cif=';


//invoicing
$siteInvoicingCode='PLUS';
$siteCompanyLegalName='SC CERT PLUS SRL';
$siteCompanyLegalAddress='Strada Nerva Traian nr. 3, etaj 10, biroul 3, sector 3, București, 031041';
$siteVATNumber='RO48179521';
$siteVATStatus='TVA normal';
$VATRegime='0';
$VATDueCode='3';
$siteVATMain='19%';
$siteCIF='48179521';
$siteCompanyRegistrationNr='J2023009169400';
$siteCompanySocialCapital='500 lei';
$siteCompanyPhones='Tel.: 031 432 7883/0722 575 390';
$siteFirstAccount='Banca Transilvania: RO35 BTRL RONC RT06 7338 6801';
$siteBankAccount='RO35BTRLRONCRT0673386801';
$siteTrezoAccount='RO35BTRLRONCRT0673386801';
$siteBankAccounts=array(
"<strong>Banca Transilvania:</strong> RO35 BTRL RONC RT06 7338 6801",
);

//VAT
$vatstatus="TVA la încasare";
$vatcote="19";

//shop
$useraccount=0; //0 no user account, 1 with user account
$vatprc="1.19";
$vatrat="0.19";
$transportprice='25';
$paidtransport="0"; //0 = free transport, 1 paid
$transportlimit="400"; //order limit for free transport
$transportprice="25";
$transportvatrat="0.09";

//training
$ttype=0; //0 external, 1 internal, 2 both

//freedays
$holidays=array(
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