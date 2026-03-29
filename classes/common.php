<?php
// Disable PCRE JIT on macOS if security restrictions prevent executable memory allocation
// this avoids the warning about "Allocation of JIT memory failed".
@ini_set('pcre.jit', '0');

//update 8.01.2025

// Connect to server and select databse.
$conn=mysqli_connect("p:$host", "$username", "$password", "$db_name");
//mysqli_query($conn,"SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
// Guard all helper function and class definitions to avoid redeclare on multiple includes
if (!function_exists('ezpub_query')) {
function ezpub_query($conn, $query ){
        $result = mysqli_query($conn, $query);

    return $result;
}
  function ezpub_inserted_id($conn) {
  $nume=mysqli_insert_id($conn);
  return $nume;
  }
  function ezpub_num_rows($result) {
  If (Isset($result)) {
  $nume=mysqli_num_rows($result);
  return $nume;
  }}  
  function ezpub_num_fields($result) {
  If (Isset($result)) {
  $nume=mysqli_num_fields($result);
  return $nume;
  }}

    function ezpub_fetch_array($result) {
  If (Isset($result)) {
  $rs=mysqli_fetch_array($result, MYSQLI_ASSOC);
  return $rs;
  }}
    function ezpub_fetch_row($result) {
  If (Isset($result)) {
  $rs=mysqli_fetch_row($result);
  return $rs;
  }}
  function ezpub_error($conn) {
  $error=mysqli_error($conn);
  return $error;
  }
  function ezpub_result($result, $i, $column) {
  If (Isset($result)) {
  $rs=mysqli_result($result,$i, $column);
  return $rs;
  }}
  
// Anti-SQL Injection
function check_inject()
{
    // tokens to detect (lowercase/plain). Multi-word tokens or tokens with surrounding spaces are preserved
    $badTokens = array(
        'insert', 'select', 'update', 'delete', 'distinct', 'having', 'truncate', 'replace',
        'handler', 'procedure', 'limit', 'order by', 'group by', 'asc', 'desc',
        ' as ', ' or ', 'like'
    );

    // fields that legitimately contain HTML — skip heavy filtering for these
    $skipFields = array(
        'pagina_continut', 'produs_descriere', 'articol_continut', 'faq_a', 'pagina_descriere', 'produs_meta',
        'course_whatyouget', 'course_description'
    );

    // recursive walker that preserves keys so we can skip specific fields
    $walker = function($arr, $parentKey = '') use (&$walker, $badTokens, $skipFields) {
        foreach ($arr as $key => $val) {
            $fullKey = $parentKey === '' ? $key : $parentKey . '.' . $key;

            // If the field name is in skip list, skip validation
            if (in_array($key, $skipFields, true) || in_array($fullKey, $skipFields, true)) {
                continue;
            }

            if (is_array($val)) {
                $walker($val, $fullKey);
                continue;
            }

            if (!is_string($val)) continue;

            // strip HTML for checking and trim
            $value = trim(strip_tags($val));
            if ($value === '') continue;

            foreach ($badTokens as $bad) {
                if (strpos($bad, ' ') !== false) {
                    // multi-word token -> simple substring check
                    if (stripos($value, $bad) !== false) {
                        error_log("SQL injection block: field=$fullKey token=$bad ip=" . getRealIpAddr() . " value=" . substr($value,0,200));
                        die("SQL Injection Detected\n<br />\nIP: " . getRealIpAddr());
                    }
                } else {
                    // whole-word match to avoid false positives (e.g. 'selected' != 'select')
                    if (preg_match('/\b' . preg_quote($bad, '/') . '\b/i', $value)) {
                        error_log("SQL injection block: field=$fullKey token=$bad ip=" . getRealIpAddr() . " value=" . substr($value,0,200));
                        die("SQL Injection Detected\n<br />\nIP: " . getRealIpAddr());
                    }
                }
            }
        }
    };

    $walker($_POST);
}

// Minimal normalizer helpers: only strip legal forms and collapse spaces
if (!function_exists('normalize_company_name_for_search')) {
    function normalize_company_name_for_search($name) {
        if ($name === null) return '';
        $s = trim($name);
        if ($s === '') return '';

        $patterns = [
            '/\bS\.?R\.?L\.?\b/iu', '/\bSRL\b/iu', '/\bS\.?C\.?\b/iu', '/\bSC\b/iu',
            '/\bS\.?A\.?\b/iu', '/\bSA\b/iu', '/\bPFA\b/iu', '/\bI\.?I\.?\b/iu',
            '/\bIF\b/iu', '/\bSCA\b/iu', '/\bS\.?C\.?A\.?\b/iu', '/\bSCS\b/iu',
            '/\bFIRMA\b/iu', '/\bUNITATE\b/iu', '/\bROMANIA\b/iu'
        ];
        $s = preg_replace($patterns, ' ', $s);
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim($s);
    }
}

if (!function_exists('normalize_company_name_for_search_verbose')) {
    function normalize_company_name_for_search_verbose($name) {
        $steps = [];
        $steps['original'] = $name;
        $s = trim($name);
        $steps['trimmed'] = $s;
        $patterns = [
            '/\bS\.?R\.?L\.?\b/iu', '/\bSRL\b/iu', '/\bS\.?C\.?\b/iu', '/\bSC\b/iu',
            '/\bS\.?A\.?\b/iu', '/\bSA\b/iu', '/\bPFA\b/iu', '/\bI\.?I\.?\b/iu',
            '/\bIF\b/iu', '/\bSCA\b/iu', '/\bS\.?C\.?A\.?\b/iu', '/\bSCS\b/iu',
            '/\bFIRMA\b/iu', '/\bUNITATE\b/iu', '/\bROMANIA\b/iu'
        ];
        $s_after = preg_replace($patterns, ' ', $s);
        $s_after = preg_replace('/\s+/u', ' ', $s_after);
        $steps['after_remove_forms'] = trim($s_after);
        $steps['final'] = $steps['after_remove_forms'];
        return $steps;
    }
}

//Romanize numbers
function romanize($number) {
	if ($number==0)
	{$romanized=0;}
else {
    $romanized = number_format($number,2,',','.');
}
    return $romanized;
}

// Integer variant for presentation without decimals
function romanize_int($number) {
    if (empty($number) && $number !== 0 && $number !== '0') {
        return '';
    }
    if ($number == 0 || $number === '0') {
        return '0';
    }
    return number_format((int)$number, 0, ',', '.');
}

// Generate random string
function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Get file icon based on file extension
function getFileIcon($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    switch ($extension) {
        case 'pdf':
            return 'far fa-file-pdf';
        case 'doc':
        case 'docx':
            return 'far fa-file-word';
        case 'xls':
        case 'xlsx':
            return 'far fa-file-excel';
        case 'ppt':
        case 'pptx':
            return 'far fa-file-powerpoint';
        case 'zip':
        case 'rar':
            return 'far fa-file-archive';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'far fa-file-image';
        case 'txt':
            return 'far fa-file-alt';
        default:
            return 'far fa-file';
    }
}

//get real IP
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

//truncate 100 characters
function truncate($string,$length=100,$append="&hellip;") {
  $string = trim($string);

  if(strlen($string) > $length) {
    $string = wordwrap($string, $length);
    $string = explode("\n", $string, 2);
    $string = $string[0] . $append;
  }

  return $string;
}

//trailing
function includeTrailingCharacter($string, $character)
{
    if (strlen($string) > 0) {
        if (substr($string, -1) !== $character) {
            return $string . $character;
        } else {
            return $string;
        }
    } else {
        return $character;
    }
}

function includeTrailingBackslash($string)
{
    return includeTrailingCharacter($string, '/');
}

//delete older files
function delete_older_than($dir, $max_age) {
  $list = array();
  
  $limit = time() - $max_age;
  
  $dir = realpath($dir);
  
  if (!is_dir($dir)) {
    return;
  }
  
  $dh = opendir($dir);
  if ($dh === false) {
    return;
  }
  
  while (($file = readdir($dh)) !== false) {
    $file = $dir . '/' . $file;
    if (!is_file($file)) {
      continue;
    }
    
    if (filemtime($file) < $limit) {
      $list[] = $file;
      unlink($file);
    }
    
  }
  closedir($dh);
  return $list;

}

function gzCompressFile($source, $level = 9){ 
    $dest = $source . '.gz'; 
    $mode = 'wb' . $level; 
    $error = false; 
    if ($fp_out = gzopen($dest, $mode)) { 
        if ($fp_in = fopen($source,'rb')) { 
            while (!feof($fp_in)) 
                gzwrite($fp_out, fread($fp_in, 1024 * 512)); 
            fclose($fp_in); 
        } else {
            $error = true; 
        }
        gzclose($fp_out); 
    } else {
        $error = true; 
    }
    if ($error)
        return false; 
    else
        return $dest; 
}

//curs bnr
class CursBNR
 	{
 		/**
		 * xml document
		 * @var string
		 */
 		var $xmlDocument = "";
 		
 		
 		/**
		 * exchange date
		 * BNR date format is Y-m-d
		 * @var string
		 */
 		var $date = "";
 		
 		
 		/**
		 * currency
		 * @var associative array
		 */
 		var $currency = array();
 		
 		
 		/**
		 * cursBnrXML class constructor
		 *
		 * @access		public
		 * @param 		$url		string
		 * @return		void
		 */
		function __construct($url)
		{
			$this->xmlDocument = file_get_contents($url);
			$this->parseXMLDocument();
		}
 		
		/**
		 * parseXMLDocument method
		 *
		 * @access		public
		 * @return 		void
		 */
		function parseXMLDocument()
		{
		// Prevent XXE attacks - libxml_disable_entity_loader() deprecated in PHP 8.0+
		// External entity loading is disabled by default in PHP 8.0+
		if (PHP_VERSION_ID < 80000) {
			libxml_disable_entity_loader(true);
		}
		
		// Validate XML content before parsing (avoid PCRE/JIT — use simple string check)
		$trimmed = ltrim($this->xmlDocument);
		if (empty($this->xmlDocument) || stripos($trimmed, '<?xml') !== 0) {
			throw new Exception('Invalid XML response from BNR: ' . substr($this->xmlDocument, 0, 100));
		}
		
		try {
			$xml = new SimpleXMLElement($this->xmlDocument, LIBXML_NONET | LIBXML_NOCDATA);
			
			$this->date=$xml->Header->PublishingDate;
			
			foreach($xml->Body->Cube->Rate as $line)	
			{ 		 			
				$this->currency[]=array("name"=>$line["currency"], "value"=>$line, "multiplier"=>$line["multiplier"]);
			}
		} catch (Exception $e) {
			throw new Exception('Failed to parse XML: ' . $e->getMessage());
		}
	}

	/**
		 * getCurs method
		 * 
		 * get current exchange rate: example getExchangeRate("USD")
		 * 
		 * @access		public
		 * @return 		double
		 */
		function getExchangeRate($currency)
		{
			foreach($this->currency as $line)
			{
				if($line["name"]==$currency)
				{
					return $line["value"];
				}
			}
			
			return "Incorrect currency!";
		}
 	}

  // scan directory
	function scanDirectories($rootDir, $allData=array()) {
    // set filenames invisible if you want
    $invisibleFileNames = array(".", "..", ".htaccess", ".htpasswd");
    // run through content of root directory
    $dirContent = scandir($rootDir);
    foreach($dirContent as $key => $content) {
        // filter all files not accessible
        $path = $rootDir.'/'.$content;
        if(!in_array($content, $invisibleFileNames)) {
            // if content is file & readable, add to array
            if(is_file($path) && is_readable($path)) {
                // save file name with path
                $allData[] = $path;
            // if content is a directory and readable, add path and name
            }elseif(is_dir($path) && is_readable($path)) {
                // recursive callback to open new directory
                $allData = scanDirectories($path, $allData);
            }
        }
    }
    return $allData;
}
//truncate 200 characters
function truncateinvoiceitem($string,$length=195,$append="...") {
  $string = trim($string);

  if(strlen($string) > $length) {
    $string = wordwrap($string, $length);
    $string = explode("\n", $string, 2);
    $string = $string[0] . $append;
  }

  return $string;
}
//truncate 300 characters
function truncateblogarticle($string,$length=295,$append="...") {
  $string = trim($string);

  if(strlen($string) > $length) {
    $string = wordwrap($string, $length);
    $string = explode("\n", $string, 2);
    $string = $string[0] . $append;
  }

  return $string;
}

	//replace special characters
	
function sanitarization($string) {
	If (!isset($string)) $string='aaa';
	$search=array("&", "'", "/");
	$replace=array("&amp;", "&apos;", "-");
	$output=str_replace($search, $replace, $string);
	return ($output);
} 	
//remove tags from einvoice
function removeLeftPartOfColonsFromArray($array) {
    $newArray = [];
    foreach ($array as $key => $value) {
        // Split the key at the colon and take the right part
        $newKey = explode(':', $key, 2)[1] ?? $key;
        
        // If the value is an array, recursively apply the function
        if (is_array($value)) {
            $value = removeLeftPartOfColonsFromArray($value);
        }
        
        $newArray[$newKey] = $value;
    }
    return $newArray;
}

//parse efactra xml
 
function xml2array($contents, $get_attributes=1, $priority = 'tag') {
    if(!$contents) return array();

    if(!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";
        return array();
    }

    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);

    if(!$xml_values) return;//Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference

    //Go through the tags.
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
    foreach($xml_values as $data) {
        unset($attributes,$value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data);//We could use the array by itself, but this cooler.

        $result = array();
		$attributes_data = array();
        
        if(isset($value)) {
            if($priority == 'tag') $result = $value;
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
        }

        //Set the attributes too.
        if(isset($attributes) and $get_attributes) {
            foreach($attributes as $attr => $val) {
                if($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }

        //See tag status and do the needed.
        if($type == "open") {//The starting of the tag '<tag>'
            $parent[$level-1] = &$current;
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                $repeated_tag_index[$tag.'_'.$level] = 1;

                $current = &$current[$tag];

            } else { //There was another element with the same tag name

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    $repeated_tag_index[$tag.'_'.$level]++;
                } else {//This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag.'_'.$level] = 2;
                    
                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                        unset($current[$tag.'_attr']);
                    }

                }
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                $current = &$current[$tag][$last_item_index];
            }

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if(!isset($current[$tag])) { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag.'_'.$level] = 1;
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

            } else { //If taken, put all things inside a list(array)
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                    // ...push the new element into that array.
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    
                    if($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag.'_'.$level]++;

                } else { //If it is not an array...
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $get_attributes) {
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }
                        
                        if($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                }
            }

        } elseif($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level-1];
        }
    }
    
    return($xml_array);
}	

 	

    function getUserIP() {
    if( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
            $addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($addr[0]);
        } else {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    else {
        return $_SERVER['REMOTE_ADDR'];
    }
}
// Funcție pentru conversie format românesc în numeric standard
function parseRomanianNumber($value) {
    // Elimină spații albe
    $value = trim($value);
    if ($value === '') return $value;
    // Normalizează caractere spațiu non-break
    $value = str_replace(array("\xc2\xa0", "\u00A0"), '', $value);

    // Dacă are virgulă, tratăm formatul românesc "1.234,56"
    if (strpos($value, ',') !== false) {
        $value = str_replace('.', '', $value); // elimină separatoarele de mii
        $value = str_replace(',', '.', $value); // virgula devine separator zecimal
        return $value;
    }

    // Dacă are doar puncte, decidem dacă sunt separatoare de mii sau separator zecimal
    if (strpos($value, '.') !== false) {
        $parts = explode('.', $value);
        $decimalPart = end($parts);
        // Dacă partea de după ultimul punct are exact 3 cifre și există cel puțin două grupuri, e probabil separator de mii
        if (strlen($decimalPart) === 3 && count($parts) > 1) {
            $value = str_replace('.', '', $value);
            return $value;
        }
        // Altfel, presupunem că punctul este separator zecimal și păstrăm formatul
        return $value;
    }

    // Nu există nici separatoare, returnăm ca atare
    return $value;
}

// Helper global pentru a returna o valoare numerică compatibilă SQL sau NULL
if (!function_exists('sql_decimal_or_null')) {
    /**
     * sql_decimal_or_null
     * Acceptă formatul românesc pentru numere (ex: "1.234,56" sau "0,18")
     * și returnează fie NULL, fie un literal numeric potrivit pentru inserție în SQL (fără ghilimele).
     * Dacă valoarea nu poate fi convertită, se întoarce un string securizat în ghilimele ca ultim resort.
     *
     * @param mixed $val
     * @return string
     */
    function sql_decimal_or_null($val) {
        if ($val === '' || $val === null) {
            return 'NULL';
        }
        // Normalizează și curăță valoarea
        $v = trim($val);
        // Normalizează caractere spațiu non-break și alte spații
        $v = str_replace(array("\xc2\xa0", "\u00A0", "\u00A0"), '', $v);
        // Folosește funcția dedicată dacă există (converteste "1.234,56" -> "1234.56")
        if (function_exists('parseRomanianNumber')) {
            $v = parseRomanianNumber($v);
        } else {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        }
        // Elimină spațiile rămase
        $v = str_replace(' ', '', $v);

        // Înlocuiește eventuale virgule sau variante de virgulă rămase cu punct
        $v = str_replace(array(',', '‚', '，'), '.', $v);

        // Dacă stringul începe cu "." (ex: ".12") adaugă zero pentru compatibilitate SQL
        if (strlen($v) > 0 && $v[0] === '.') {
            $v = '0' . $v;
        }
        if (strlen($v) > 1 && substr($v, 0, 2) === '-.') {
            $v = str_replace('-.', '-0.', $v);
        }

        // Dacă este numeric, returnează ca atare (fără ghilimele)
        if (is_numeric($v)) {
            return $v;
        }

        // Încercare de curățare suplimentară: păstrează doar cifre, punct și minus
        $v2 = preg_replace('/[^0-9\.\-]/', '', $v);
        // Dacă a rămas un punct la început (.12) adaugă zero
        if ($v2 !== '' && $v2[0] === '.') {
            $v2 = '0' . $v2;
        }
        if ($v2 !== '' && is_numeric($v2)) {
            return $v2;
        }

        // Ca ultim resort, scapă și returnează quoted string
        return "'" . addslashes($val) . "'";
    }
}

/**
 * Normalizează diacriticele românești (caractere vechi → caractere corecte)
 * Înlocuiește Ş cu Ș și Ţ cu Ț (sedilă → virgulă)
 * 
 * @param string $text Text de normalizat
 * @return string Text cu diacritice corecte
 */
function normalizeDiacritice($text) {
    if (empty($text)) {
        return $text;
    }
    
    // Înlocuiește caractere vechi cu caractere corecte românești
    $replacements = [
        'Ş' => 'Ș', // S cu sedilă → S cu virgulă (majusculă)
        'ş' => 'ș', // s cu sedilă → s cu virgulă (minusculă)
        'Ţ' => 'Ț', // T cu sedilă → T cu virgulă (majusculă)
        'ţ' => 'ț'  // t cu sedilă → t cu virgulă (minusculă)
    ];
    
    return str_replace(array_keys($replacements), array_values($replacements), $text);
}
} // end if !function_exists('ezpub_query')
?>