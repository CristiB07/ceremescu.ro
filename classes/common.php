<?php
//update 8.01.2025

// Connect to server and select databse.
$conn=mysqli_connect("p:$host", "$username", "$password", "$db_name");
//mysqli_query($conn,"SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
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
    $badchars = array(
                    "insert",  "INSERT", "select", "SELECT", "update", "UPDATE", "delete", "DELETE", "distinct", "DISTINCT", "having", "HAVING", "truncate", "TRUNCATE", "replace", "REPLACE",
                    "handler", "HANDLER", "like", "LIKE", " as ", " AS ", " or ", " OR ", "procedure", "limit", "order by", "group by", "asc", " ASC ", "desc", " DESC ",
            );
    foreach($_POST as $value)
    {
      if(in_array($value, $badchars))
      {
        die("SQL Injection Detected\n<br />\nIP: ".$_SERVER['REMOTE_ADDR']);
      }
      else
      {
        $check = preg_split("//", $value, -1, PREG_SPLIT_OFFSET_CAPTURE);
        foreach($check as $char)
		{
          if(in_array($char, $badchars))
			{
            die("SQL Injection Detected\n<br />\nIP: ".$_SERVER['REMOTE_ADDR']);
			}
		}
	  }
	}
}

//Romanize numbers
function romanize($number) {
	if ($number==0)
	{$romanized=0;}
Else {
    $romanized = number_format($number,2,',','.');
}
    return $romanized;
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
	 		$xml = new SimpleXMLElement($this->xmlDocument);
	 		
	 		$this->date=$xml->Header->PublishingDate;
	 		
	 		foreach($xml->Body->Cube->Rate as $line)	
	 		{ 		 			
	 			$this->currency[]=array("name"=>$line["currency"], "value"=>$line, "multiplier"=>$line["multiplier"]);
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

 	?>