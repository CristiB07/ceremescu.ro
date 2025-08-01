<?php
// for SSL server certificates the commonName is the domain name to be secured
// for S/MIME email certificates the commonName is the owner of the email address
// location and identification fields refer to the owner of domain or email subject to be secured
$dn = array(
    "countryName" => "RO",
    "stateOrProvinceName" => "București",
    "localityName" => "Sector 3",
    "organizationName" => "Consaltis Consultanță și Audit SRL",
    "organizationalUnitName" => "Mediu",
    "commonName" => "Cristian Banu",
    "emailAddress" => "cristian.banu@consaltis.ro"
);

$Configs = array(      
     'config' => 'C:/Program Files/Common Files/SSL/openssl.cnf', 
    'digest_alg' => 'sha1',
    'x509_extensions' => 'v3_ca',
    'req_extensions' => 'v3_req',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'encrypt_key' => true,
    'encrypt_key_cipher' => OPENSSL_CIPHER_3DES
);

$privateKeyPass = 'abcd1234';
$numberOfDays   = 108;
  
$privateKey = openssl_pkey_new();
$csr = openssl_csr_new($dn, $privateKey);
  
// Create a csr file, change null
// to a filename to save
$sscert = openssl_csr_sign($csr, 
    null, $privateKey, $numberOfDays);
  
// On success $publicKey will 
// hold the PEM content 
openssl_x509_export($sscert, $publicKey);
  
// Export the privateKey as a PEM content
openssl_pkey_export($privateKey, 
        $privateKey, $privateKeyPass);
  
$filename = dirname(__FILE__) 
        . '/certificate.pfx';
  
// Parses the $privateKey and used 
// by openssl_pkcs12_export_to_file
$key = openssl_pkey_get_private(
    $privateKey, $privateKeyPass);
  
// Save the pfx file to $filename
openssl_pkcs12_export_to_file($sscert, 
    $filename, $key, $privateKeyPass);
?>