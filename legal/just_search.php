<?php
include '../settings.php';
include '../classes/common.php';

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}

$uid=$_SESSION['uid'];
$code=$_SESSION['code'];

$strPageTitle = "Căutare legislație - legislatie.just.ro";
// allow more time for remote SOAP calls from web context
@set_time_limit(60);
// The header and page rendering are executed only when not in CLI test mode.
// Define JUST_SEARCH_NO_RENDER to include this file for its functions only.

function callSoap($endpoint, $action, $xml, $contentType = 'text/xml; charset=utf-8')
{
    $ch = curl_init($endpoint);
    $headers = [];
    // For SOAP 1.2 the action can be part of the Content-Type header
    if (stripos($contentType, 'application/soap+xml') !== false) {
      if (stripos($contentType, 'action=') === false && !empty($action)) {
        $contentType = $contentType . '; action="' . $action . '"';
      }
      $headers[] = 'Content-Type: ' . $contentType;
    } else {
      $headers[] = 'Content-Type: ' . $contentType;
      if (!empty($action)) $headers[] = 'SOAPAction: "' . $action . '"';
    }
    // avoid Expect: 100-continue which some servers reject
    $headers[] = 'Expect:';

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    // keep connect timeout short and overall request modest
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    // follow redirects (the service may redirect HTTP->HTTPS)
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MasterApp-just_search/1.0');
    // ensure POST is preserved on redirect (some servers redirect and change to GET)
    if (defined('CURL_REDIR_POST_ALL')) {
      curl_setopt($ch, CURLOPT_POSTREDIR, CURL_REDIR_POST_ALL);
    }
    // enforce HTTP/1.1
    if (defined('CURL_HTTP_VERSION_1_1')) curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    $resp = curl_exec($ch);
    $errno = curl_errno($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);

    // don't call curl_close() due to deprecation in PHP 8.5
    return ['response' => $resp, 'error' => $err, 'errno' => $errno, 'info' => $info, 'content_type' => $contentType];
}

function getToken()
{
  // Use SOAP address from WSDL
  $endpoint = 'http://legislatie.just.ro/apiws/FreeWebService.svc/SOAP';
  $wsdl = 'http://legislatie.just.ro/apiws/FreeWebService.svc?wsdl';
  $action = 'http://tempuri.org/IFreeWebService/GetToken';

    // Try native SoapClient first (more compatible with SOAP servers)
    if (class_exists('SoapClient')) {
        try {
            $opts = [
                'location' => $endpoint,
                'uri' => 'http://tempuri.org/',
                'trace' => 1,
                'exceptions' => 1,
                'soap_version' => SOAP_1_1,
            ];
            $client = new SoapClient(null, $opts);
            $result = $client->__soapCall('GetToken', [], ['soapaction' => $action]);
            // $result may be an object or string
            if (is_object($result)) {
                $val = $result->GetTokenResult ?? null;
            } else {
                $val = $result;
            }
            if (!empty($val)) return ['token' => trim((string)$val)];
        } catch (Exception $e) {
            // continue to fallback to cURL
            $soapErr = $e->getMessage();
        }
    }

    // Fallback: build raw SOAP envelope and call via cURL
    $xml = <<<XML
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Header>
    <Action s:mustUnderstand="1" xmlns="http://schemas.microsoft.com/ws/2005/05/addressing/none">$action</Action>
  </s:Header>
  <s:Body>
    <GetToken xmlns="http://tempuri.org/" />
  </s:Body>
</s:Envelope>
XML;
    // Try the SOAP endpoint first, then WSDL URL, then legacy paths
    $endpoints = [
      $endpoint,
      $wsdl,
      'http://legislatie.just.ro/apiws',
      'http://legislatie.just.ro/apiws/',
    ];

    $attempts = [];

    // If the caller requests both NumarPagina=0 and RezultatePagina=0, prefer sending raw SOAP via cURL
    // before using SoapClient. The service example uses zeros and may respond differently.
    if ((int)$numarPagina === 0 && (int)$rezultatePagina === 0) {
      $curlEndpoints = [$endpoint, preg_replace('#^http:#i', 'https:', $endpoint)];
      foreach ($curlEndpoints as $ep) {
        $res = callSoap($ep, $action, $xml, 'text/xml; charset=utf-8');
        $resp = $res['response'] ?? null;
        $info = $res['info'] ?? null;
        $attempt = ['method' => 'curl_post_zero', 'endpoint' => $ep, 'http_code' => $info['http_code'] ?? null, 'error' => $res['error'] ?? null];
        if (!empty($resp)) {
          $sx = @simplexml_load_string($resp);
          if ($sx !== false) {
            $nodes = $sx->xpath('//*[local-name()="Legi"]');
            if ($nodes) {
              $legi = [];
              foreach ($nodes as $node) {
                $entry = [];
                foreach ($node->children() as $child) {
                  $entry[$child->getName()] = trim((string)$child);
                }
                $legi[] = $entry;
              }
              $totalNodes = $sx->xpath('//*[contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"total") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"count") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"nr") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"numar")]');
              $total = null;
              if ($totalNodes && isset($totalNodes[0])) {
                $val = trim((string)$totalNodes[0]);
                if (is_numeric($val)) $total = (int)$val;
              }
              // If we got 10 results and no total, the service may be applying defaults.
              // Retry with SOAP 1.2 content-type which sometimes changes server behavior.
              if ($total === null && count($legi) === 10) {
                $res2 = callSoap($ep, $action, $xml, 'application/soap+xml; charset=utf-8');
                $resp2 = $res2['response'] ?? null;
                if (!empty($resp2)) {
                  $sx2 = @simplexml_load_string($resp2);
                  if ($sx2 !== false) {
                    $nodes2 = $sx2->xpath('//*[local-name()="Legi"]');
                    if ($nodes2) {
                      $legi2 = [];
                      foreach ($nodes2 as $node2) {
                        $entry2 = [];
                        foreach ($node2->children() as $child2) {
                          $entry2[$child2->getName()] = trim((string)$child2);
                        }
                        $legi2[] = $entry2;
                      }
                      $totalNodes2 = $sx2->xpath('//*[contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"total") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"count") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"nr") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"numar")]');
                      $total2 = null;
                      if ($totalNodes2 && isset($totalNodes2[0])) {
                        $val2 = trim((string)$totalNodes2[0]);
                        if (is_numeric($val2)) $total2 = (int)$val2;
                      }
                      // prefer the richer response if it includes a total
                      if ($total2 !== null) return ['results' => $legi2, 'raw' => $resp2, 'total' => $total2];
                      // otherwise if the second reply has different count, return it
                      if (count($legi2) !== count($legi)) return ['results' => $legi2, 'raw' => $resp2, 'total' => $total2];
                    }
                  }
                }
              }
              return ['results' => $legi, 'raw' => $resp, 'total' => $total];
            }
          }
          $attempt['raw'] = $resp;
        }
        $attempts[] = $attempt;
      }
    }

    // If both page and pageSize are set to 0, the service example sends zeros explicitly.
    // Some servers treat this specially (e.g., return totals or all results). Prefer sending raw XML via cURL first
    // to exactly match the example envelope when both values are zero.
    if ((int)$numarPagina === 0 && (int)$rezultatePagina === 0) {
      $curlEndpoints = [
        $endpoint,
        preg_replace('#^http:#i', 'https:', $endpoint),
      ];
      foreach ($curlEndpoints as $ep) {
        $res = callSoap($ep, $action, $xml, 'text/xml; charset=utf-8');
        $resp = $res['response'] ?? null;
        $info = $res['info'] ?? null;
        $attempt = ['method' => 'curl_post_zero', 'endpoint' => $ep, 'http_code' => $info['http_code'] ?? null, 'error' => $res['error'] ?? null];
        if (!empty($resp)) {
          $sx = @simplexml_load_string($resp);
          if ($sx !== false) {
            $nodes = $sx->xpath('//*[local-name()="Legi"]');
            if ($nodes) {
              $legi = [];
              foreach ($nodes as $node) {
                $entry = [];
                foreach ($node->children() as $child) {
                  $entry[$child->getName()] = trim((string)$child);
                }
                $legi[] = $entry;
              }
              $totalNodes = $sx->xpath('//*[contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"total") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"count") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"nr") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"numar")]');
              $total = null;
              if ($totalNodes && isset($totalNodes[0])) {
                $val = trim((string)$totalNodes[0]);
                if (is_numeric($val)) $total = (int)$val;
              }
              return ['results' => $legi, 'raw' => $resp, 'total' => $total];
            }
          }
          $attempt['raw'] = $resp;
        }
        $attempts[] = $attempt;
      }
    }
    foreach ($endpoints as $ep) {
      if (stripos($ep, '?wsdl') !== false) {
        // Try SoapClient WSDL on this URL
        try {
                $client = new SoapClient($ep, ['trace' => 1, 'exceptions' => 1]);
          $resSoap = $client->GetToken();
          $val = null;
          if (is_object($resSoap)) {
            $val = $resSoap->GetTokenResult ?? null;
          } else {
            $val = $resSoap;
          }
          if (!empty($val)) return ['token' => trim((string)$val)];
        } catch (Exception $e) {
          $attempts[] = ['endpoint' => $ep, 'error' => $e->getMessage()];
          continue;
        }
      } else {
        $res = callSoap($ep, $action, $xml);
        $resp = $res['response'] ?? null;
        $info = $res['info'] ?? null;
        $attempt = ['endpoint' => $ep, 'http_code' => $info['http_code'] ?? null, 'error' => $res['error'] ?? null];
        if (!empty($resp)) {
          $sx = @simplexml_load_string($resp);
          if ($sx !== false) {
            $nodes = $sx->xpath('//*[local-name()="GetTokenResult"]');
            if ($nodes && isset($nodes[0])) return ['token' => trim((string)$nodes[0])];
          }
          $attempt['raw'] = $resp;
        }
        $attempts[] = $attempt;
      }
    }

    return ['error' => 'All attempts failed', 'attempts' => $attempts, 'soapErr' => $soapErr ?? null];
}

// Try to fix common mojibake/double-encoding issues and convert to UTF-8
function fix_mojibake($s)
{
  if ($s === null || $s === '') return $s;
  // Quick normalize newlines first
  $s = str_replace(array("\r\n","\r"), "\n", $s);

  // If already valid UTF-8 and contains Romanian diacritics, assume ok
  if (mb_check_encoding($s, 'UTF-8')) {
    if (preg_match('/[ăĂâÂîÎșȘţŢțţ]/u', $s)) return $s;
    // if looks like UTF-8 but contains common mojibake markers, try decode
    if (preg_match('/[ÃÂÅÄ]/', $s)) {
      $dec = @utf8_decode($s);
      if ($dec !== false && mb_check_encoding($dec, 'UTF-8') && preg_match('/[ăĂâÂîÎșȘţŢ]/u', $dec)) return $dec;
    }
    return $s;
  }

  $candidates = [];
  // common single-byte encodings to try
  $encs = ['Windows-1250','CP1250','ISO-8859-2','CP1252','ISO-8859-1'];
  foreach ($encs as $enc) {
    $c = @mb_convert_encoding($s, 'UTF-8', $enc);
    if ($c !== false) $candidates[] = $c;
  }
  // try utf8_encode (latin1 -> utf8)
  $candidates[] = @utf8_encode($s);
  // try double-decoding patterns
  $candidates[] = @utf8_encode(utf8_decode($s));

  // scoring: count Romanian diacritics occurrences
  $best = $s;
  $bestScore = 0;
  foreach ($candidates as $cand) {
    if ($cand === false || $cand === null) continue;
    $score = 0;
    if (preg_match_all('/[ăĂâÂîÎșȘţŢ]/u', $cand, $m)) $score = count($m[0]);
    // prefer candidate with more diacritics
    if ($score > $bestScore) { $best = $cand; $bestScore = $score; }
    // fallback to first valid UTF-8 candidate if none scored
    if ($bestScore === 0 && $best === $s && mb_check_encoding($cand, 'UTF-8')) { $best = $cand; }
  }

  return $best;
}

// Further normalize text intended for modal display:
// - fix mojibake
// - normalize Unicode (NFC) when available
// - collapse multiple spaces, trim lines
// - collapse multiple blank lines
// - remove near-duplicate header lines (keep the one with more diacritics)
function normalize_modal_text($s)
{
  if ($s === null || $s === '') return $s;
  // fix common encoding issues first
  $s = fix_mojibake($s);
  // normalize unicode if extension present
  if (class_exists('Normalizer')) {
    $n = Normalizer::normalize($s, Normalizer::FORM_C);
    if ($n !== false && $n !== null) $s = $n;
  }

  // normalize newlines and split into lines
  $s = str_replace(array("\r\n","\r"), "\n", $s);
  $lines = preg_split('/\n/', $s);

  $clean = [];
  foreach ($lines as $ln) {
    // replace multiple whitespace with single space, then trim
    $ln = preg_replace('/[\t ]+/u', ' ', $ln);
    $ln = trim($ln);
    $clean[] = $ln;
  }

  // remove leading/trailing empty lines and collapse multiple empties
  $out = [];
  $prevEmpty = false;
  foreach ($clean as $ln) {
    if ($ln === '') {
      if ($prevEmpty) continue;
      $out[] = '';
      $prevEmpty = true;
    } else {
      $out[] = $ln;
      $prevEmpty = false;
    }
  }
  // remove leading/trailing blank entries
  while (!empty($out) && $out[0] === '') array_shift($out);
  while (!empty($out) && end($out) === '') array_pop($out);

  // If first two non-empty lines are near-duplicates, keep the one with more Romanian diacritics
  if (count($out) >= 2) {
    $first = $out[0];
    $second = $out[1];
    // create simplified ascii-ish versions for distance calculation
    $a = preg_replace('/\s+/u',' ', mb_strtolower($first));
    $b = preg_replace('/\s+/u',' ', mb_strtolower($second));
    $aStripped = @iconv('UTF-8', 'ASCII//TRANSLIT', $a) ?: $a;
    $bStripped = @iconv('UTF-8', 'ASCII//TRANSLIT', $b) ?: $b;
    $dist = levenshtein($aStripped, $bStripped);
    $len = max(1, min(mb_strlen($aStripped), mb_strlen($bStripped)));
    // if distance is small relative to length, consider them duplicates
    if ($dist <= max(3, (int)($len * 0.12))) {
      // count diacritics in each
      $countA = preg_match_all('/[ăĂâÂîÎșȘţŢțţ]/u', $first, $mA) ? count($mA[0]) : 0;
      $countB = preg_match_all('/[ăĂâÂîÎșȘţŢțţ]/u', $second, $mB) ? count($mB[0]) : 0;
      if ($countB > $countA) {
        // prefer second: remove first
        array_shift($out);
      } else {
        // prefer first: remove second
        array_splice($out, 1, 1);
      }
    }
  }

  // rejoin with single newlines (preserve paragraph breaks where blank line exists)
  $final = implode("\n", $out);
  // common mojibake sequence replacements (cover frequent double-decoding artifacts)
  $map = array(
    'Å¢' => 'ț', 'Å£' => 'ș', 'Å' => 'Ș', 'Å' => 'ș',
    'Ä' => 'ă', 'Ä‚' => 'Ă',
    'Ã¢' => 'â', 'Ã‚' => 'Â', 'Ã®' => 'î', 'ÃŽ' => 'Î',
    'Ã' => 'Â', 'Ã' => 'Ă',
    'Ã' => 'Î', 'ÃNÂ' => 'NÂ',
    // additional common mojibake fragments
    'È' => 'ș', 'È' => 'Ș', 'È' => 'ț', 'È' => 'Ț',
    'Ä ' => 'ă', 'Ä' => 'ă', 'Ä ' => 'ș', 'Äª' => 'î', 'Ä' => 'ă',
    'Ã' => 'Ă',
    // phrase-specific fixes
    ' deÈe' => ' deșe', 'obligaÈa' => 'obligația', 'referÄ la' => 'referă la'
  );
  $final = strtr($final, $map);
  // heuristic: ensure header-like markers appear on their own lines
  $final = preg_replace('/\s+(EMITENT)\b/u', "\n$1", $final);
  $final = preg_replace('/\s+(PUBLICAT(?:\s+ÎN)?)\b/u', "\n$1", $final);
  $final = preg_replace('/\s+(MONITORUL\s+OFICIAL)\b/u', "\n$1", $final);
  // ensure a newline after MONITORUL OFICIAL if followed by 'nr.' or a number
  $final = preg_replace('/(MONITORUL\s+OFICIAL\b[^\n]*)(\s+nr\.|\s+nr\b)/iu', "$1\n$2", $final);
  return $final;
}


function searchLegislation($tokenKey, $numarPagina = 0, $rezultatePagina = 0, $searchAn = null, $searchNumar = null, $searchText = null, $searchTitlu = null)
{
  // SOAP endpoint from WSDL
  $endpoint = 'http://legislatie.just.ro/apiws/FreeWebService.svc/SOAP';
  $wsdl = 'http://legislatie.just.ro/apiws/FreeWebService.svc?wsdl';
  $action = 'http://tempuri.org/IFreeWebService/Search';

  // prepare SearchModel - use nil attributes for empty values
    $SearchAn = is_null($searchAn) ? '<d4p1:SearchAn i:nil="true" />' : '<d4p1:SearchAn>' . htmlspecialchars($searchAn) . '</d4p1:SearchAn>';
    $SearchNumar = is_null($searchNumar) ? '<d4p1:SearchNumar i:nil="true" />' : '<d4p1:SearchNumar>' . htmlspecialchars($searchNumar) . '</d4p1:SearchNumar>';
    $SearchText = is_null($searchText) ? '<d4p1:SearchText i:nil="true" />' : '<d4p1:SearchText>' . htmlspecialchars($searchText) . '</d4p1:SearchText>';
    $SearchTitlu = is_null($searchTitlu) ? '<d4p1:SearchTitlu i:nil="true" />' : '<d4p1:SearchTitlu>' . htmlspecialchars($searchTitlu) . '</d4p1:SearchTitlu>';

    $xml = <<<XML
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Header>
    <Action s:mustUnderstand="1" xmlns="http://schemas.microsoft.com/ws/2005/05/addressing/none">$action</Action>
  </s:Header>
  <s:Body>
    <Search xmlns="http://tempuri.org/">
      <SearchModel xmlns:d4p1="http://schemas.datacontract.org/2004/07/FreeWebService" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
        <d4p1:NumarPagina>$numarPagina</d4p1:NumarPagina>
        <d4p1:RezultatePagina>$rezultatePagina</d4p1:RezultatePagina>
        $SearchAn
        $SearchNumar
        $SearchText
        $SearchTitlu
      </SearchModel>
      <tokenKey>$tokenKey</tokenKey>
    </Search>
  </s:Body>
</s:Envelope>
XML;

    $attempts = [];

    // 1) Try SoapClient using the WSDL
    if (class_exists('SoapClient')) {
        try {
            $client = new SoapClient($wsdl, ['trace' => 1, 'exceptions' => 1]);
            $params = ['SearchModel' => [
                'NumarPagina' => $numarPagina,
                'RezultatePagina' => $rezultatePagina,
                'SearchAn' => $searchAn,
                'SearchNumar' => $searchNumar,
                'SearchText' => $searchText,
                'SearchTitlu' => $searchTitlu,
            ], 'tokenKey' => $tokenKey];
            $resSoap = $client->__soapCall('Search', [$params]);

            // Handle possible return shapes: XML string, or parsed object/array
            if (is_string($resSoap)) {
              $xmlResp = $resSoap;
              $sx = @simplexml_load_string($xmlResp);
              if ($sx !== false) {
                $nodes = $sx->xpath('//*[local-name()="Legi"]');
                $legi = [];
                if ($nodes) {
                  foreach ($nodes as $node) {
                    $entry = [];
                    foreach ($node->children() as $child) {
                      $entry[$child->getName()] = trim((string)$child);
                    }
                    $legi[] = $entry;
                  }
                }
            $totalNodes = $sx->xpath('//*[contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"total") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"count") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"nr") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"numar")]');
            $total = null;
            if ($totalNodes && isset($totalNodes[0])) {
              $val = trim((string)$totalNodes[0]);
              if (is_numeric($val)) $total = (int)$val;
            }
            return ['results' => $legi, 'raw' => $xmlResp, 'total' => $total];
              }
            } elseif (is_object($resSoap) || is_array($resSoap)) {
              // convert to array and search for 'Legi' nodes
              $arr = json_decode(json_encode($resSoap), true);
              $found = [];
              $walker = function($v) use (&$walker, &$found) {
                if (is_array($v)) {
                  foreach ($v as $k => $val) {
                    if (strtolower($k) === 'legi') {
                      if (is_array($val)) {
                        // val may be list of entries or single
                        if (array_values($val) === $val) {
                          foreach ($val as $entry) {
                            if (is_array($entry)) $found[] = $entry;
                          }
                        } else {
                          $found[] = $val;
                        }
                      }
                    } else {
                      $walker($val);
                    }
                  }
                }
              };
              $walker($arr);
              if (!empty($found)) return ['results' => $found, 'raw' => json_encode($arr)];
            }
        } catch (Exception $e) {
            $attempts[] = ['method' => 'soap_wsdl', 'endpoint' => $wsdl, 'error' => $e->getMessage()];
        }
    }

    // 2) Single cURL POST to the SOAP address (http then https)
    $curlEndpoints = [
        $endpoint,
        preg_replace('#^http:#i', 'https:', $endpoint),
    ];
    foreach ($curlEndpoints as $ep) {
        $res = callSoap($ep, $action, $xml, 'text/xml; charset=utf-8');
        $resp = $res['response'] ?? null;
        $info = $res['info'] ?? null;
        $attempt = ['method' => 'curl_post', 'endpoint' => $ep, 'http_code' => $info['http_code'] ?? null, 'error' => $res['error'] ?? null];
        if (!empty($resp)) {
            $sx = @simplexml_load_string($resp);
            if ($sx !== false) {
                $nodes = $sx->xpath('//*[local-name()="Legi"]');
                if ($nodes) {
                    $legi = [];
                    foreach ($nodes as $node) {
                        $entry = [];
                        foreach ($node->children() as $child) {
                            $entry[$child->getName()] = trim((string)$child);
                        }
                        $legi[] = $entry;
                    }
            $totalNodes = $sx->xpath('//*[contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"total") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"count") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"nr") or contains(translate(local-name(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"numar")]');
            $total = null;
            if ($totalNodes && isset($totalNodes[0])) {
              $val = trim((string)$totalNodes[0]);
              if (is_numeric($val)) $total = (int)$val;
            }
            return ['results' => $legi, 'raw' => $resp, 'total' => $total];
                }
            }
            $attempt['raw'] = $resp;
        }
        $attempts[] = $attempt;
    }

    return ['error' => 'All attempts failed', 'attempts' => $attempts];
}

// Handle requests and rendering (skip when JUST_SEARCH_NO_RENDER is defined)
if (!defined('JUST_SEARCH_NO_RENDER')) {
  include '../dashboard/header.php';

  $message = '';
  $results = [];
  $tokenVal = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // token retrieval is automatic during search; explicit token button removed.
    if (isset($_POST['search'])) {
      // token is hidden from the user; always obtain automatically
      $token = '';
      if (empty($token)) {
        $t = getToken();
        if (isset($t['error'])) {
          $message = 'Nu s-a putut obține token-ul: ' . $t['error'];
          if (isset($t['raw']) && !empty($t['raw'])) $message .= "\nRaw: " . htmlspecialchars($t['raw']);
          if (isset($t['info']) && is_array($t['info'])) {
            $info = $t['info'];
            $http = $info['http_code'] ?? '';
            $ct = $info['content_type'] ?? '';
            $redir = $info['redirect_url'] ?? '';
            $message .= "\nHTTP info: code=$http, content_type=$ct, redirect_url=$redir";
          }
          $token = '';
        } elseif (isset($t['token'])) {
          $token = $t['token'];
          $tokenVal = $token;
        }
      }
      if (empty($token)) {
        // token still not available -> abort search
      } else {
            // Defaults: request page=0 and rezultate=0 to match service example
            $page = 0;
            $per = 0;
            $an = strlen(trim($_POST['SearchAn'])) ? trim($_POST['SearchAn']) : null;
            $numar = strlen(trim($_POST['SearchNumar'])) ? trim($_POST['SearchNumar']) : null;
            $text = strlen(trim($_POST['SearchText'])) ? trim($_POST['SearchText']) : null;
            $titlu = strlen(trim($_POST['SearchTitlu'])) ? trim($_POST['SearchTitlu']) : null;
            $out = searchLegislation($token, $page, $per, $an, $numar, $text, $titlu);
            // capture raw and attempts for rendering/debugging
            $last_raw = $out['raw'] ?? null;
            $last_attempts = $out['attempts'] ?? null;
            $last_total = $out['total'] ?? null;
            // If service reported token invalid/expired, try to refresh token once and retry
            if (isset($out['error'])) {
              $combined = '';
              if (!empty($last_attempts) && is_array($last_attempts)) $combined .= json_encode($last_attempts);
              if (!empty($last_raw) && is_string($last_raw)) $combined .= ' ' . substr($last_raw,0,1000);
              if (stripos($combined, 'TOKEN INVALID') !== false || stripos($combined, 'REGENERATI TOKEN') !== false || stripos($combined, 'EXPIRAT') !== false) {
                $t2 = getToken();
                if (isset($t2['token']) && !empty($t2['token'])) {
                  $token = $t2['token'];
                  $tokenVal = $token;
                  $out = searchLegislation($token, $page, $per, $an, $numar, $text, $titlu);
                  $last_raw = $out['raw'] ?? $last_raw;
                  $last_attempts = $out['attempts'] ?? $last_attempts;
                  $last_total = $out['total'] ?? $last_total;
                }
              }
            }
                if (isset($out['error'])) {
                  // Friendly message when service returned generic failure
                  $errStr = is_string($out['error']) ? $out['error'] : '';
                  if (stripos($errStr, 'All attempts failed') !== false) {
                    $message = 'Căutarea dumneavoastră nu a găsit nici un rezultat.';
                  } else {
                    $message = 'Eroare: ' . $errStr;
                    if (isset($out['raw']) && !empty($out['raw'])) $message .= "\nRaw: " . htmlspecialchars($out['raw']);
                    if (isset($out['attempts'])) {
                      $message .= "\nDetalii încercări: " . htmlspecialchars(json_encode($out['attempts']));
                    }
                    if (isset($out['info']) && is_array($out['info'])) {
                      $info = $out['info'];
                      $http = $info['http_code'] ?? '';
                      $ct = $info['content_type'] ?? '';
                      $redir = $info['redirect_url'] ?? '';
                      $message .= "\nHTTP info: code=$http, content_type=$ct, redirect_url=$redir";
                    }
                  }
                } else {
                  $results = $out['results'];
                  $tokenVal = $token;
                  // Normalize Text encoding and line endings for each result
                  if (is_array($results)) {
                    foreach ($results as $ri => $rv) {
                      if (isset($rv['Text']) && is_string($rv['Text'])) {
                        $text = $rv['Text'];
                        // Replace escaped Windows newlines and various newline representations with \n
                        $text = str_replace(array('\\r\\n','\\n','\r\n','\r','\n'), "\n", $text);
                        // Trim surrounding whitespace
                        $text = trim($text);
                        // Normalize text for modal display (encoding, whitespace, duplicate headers)
                        $text = normalize_modal_text($text);
                        $results[$ri]['Text'] = $text;
                      }
                    }
                  }
              // show total if provided by the service
              if (isset($last_total) && is_numeric($last_total)) {
                $message = 'Total rezultate (raportate de serviciu): ' . (int)$last_total;
              } elseif (isset($out['total']) && is_numeric($out['total'])) {
                $message = 'Total rezultate (raportate de serviciu): ' . (int)$out['total'];
              }
              if (empty($results)) {
                if (empty($message)) $message = 'Căutare executată, dar nu s-au găsit rezultate.';
                if (isset($last_raw) && !empty($last_raw)) $message .= "\nRaw: " . htmlspecialchars(substr($last_raw,0,2000));
                if (isset($out['info']) && is_array($out['info'])) {
                  $info = $out['info'];
                  $http = $info['http_code'] ?? '';
                  $ct = $info['content_type'] ?? '';
                  $redir = $info['redirect_url'] ?? '';
                  $message .= "\nHTTP info: code=$http, content_type=$ct, redirect_url=$redir";
                }
              }
            }
        }
    }
    }

    ?>
    <div class="grid-x grid-padding-x">
  <div class="large-12 cell">
    <h1><?php echo $strPageTitle; ?></h1>
    <?php if ($message): ?>
      <div class="callout alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php /* debug output removed */ ?>

    <form method="post" action="just_search.php">
      <div class="grid-x grid-padding-x">
        <div class="large-3 cell">
          <label>An (SearchAn)<br>
            <input type="text" name="SearchAn" value="<?php echo isset($_POST['SearchAn']) ? htmlspecialchars($_POST['SearchAn']) : '' ?>" />
          </label>
          <label>Numar (SearchNumar)<br>
            <input type="text" name="SearchNumar" value="<?php echo isset($_POST['SearchNumar']) ? htmlspecialchars($_POST['SearchNumar']) : '' ?>" />
          </label>
        </div>
        <div class="large-3 cell"><label>Text (SearchText)<br><input type="text" name="SearchText" value="<?php echo isset(
          		$_POST['SearchText']) ? htmlspecialchars($_POST['SearchText']) : '' ?>" /></label></div>
        <div class="large-3 cell">
          <label>Titlu (SearchTitlu)<br><input type="text" name="SearchTitlu" value="<?php echo isset($_POST['SearchTitlu']) ? htmlspecialchars($_POST['SearchTitlu']) : '' ?>" /></label>
          <div style="margin-top:0.5rem">
            <button type="submit" name="search" class="button success">Caută</button>
          </div>
        </div>
      </div>
      
    </form>

    <?php if (!empty($results)): ?>
      <h2>Rezultate: <?php echo count($results); ?></h2>
      <table>
        <thead>
          <tr><th>Tip</th><th>Numar</th><th>Titlu</th><th>DataVigoare</th><th>Emitent</th><th>Publicatie</th><th>Detalii</th><th>Link</th></tr>
        </thead>
        <tbody>
        <?php foreach ($results as $r): ?>
          <tr>
            <td><?php echo htmlspecialchars(isset($r['TipAct']) ? $r['TipAct'] : ''); ?></td>
            <td><?php echo htmlspecialchars(isset($r['Numar']) ? $r['Numar'] : ''); ?></td>
            <td><?php echo htmlspecialchars(isset($r['Titlu']) ? $r['Titlu'] : ''); ?></td>
            <td><?php echo htmlspecialchars(isset($r['DataVigoare']) ? $r['DataVigoare'] : ''); ?></td>
            <td><?php echo htmlspecialchars(isset($r['Emitent']) ? $r['Emitent'] : ''); ?></td>
            <td><?php echo htmlspecialchars(isset($r['Publicatie']) ? $r['Publicatie'] : ''); ?></td>
            <td>
              <?php
                $dataAttr = base64_encode(json_encode($r, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
              ?>
              <button class="button small open-law" data-open="lawModal" data-item='<?php echo $dataAttr; ?>'>Detalii</button>
            </td>
            <td>
              <?php if (!empty($r['LinkHtml'])): ?>
                <a href="<?php echo htmlspecialchars($r['LinkHtml']); ?>" target="_blank" rel="noopener noreferrer">Vezi</a>
              <?php else: ?>
                &nbsp;
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
  
<div class="large reveal" id="lawModal" data-reveal>
  <h1 class="modal-title"></h1>
  <p class="lead" style="display:none"></p>
  <div class="modal-body" style="max-height:60vh;overflow:auto;white-space:pre-wrap;margin-bottom:1rem"></div>
  <div style="display:flex;gap:0.5rem;align-items:center">
    <button id="saveLaw" class="button success">Salvează</button>
    <button class="button secondary" data-close aria-label="Închide">Închide</button>
  </div>
  <div id="saveStatus" style="margin-top:0.75rem;display:none;" class="callout"></div>
  <button class="close-button" data-close aria-label="Close modal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
</div>

<?php include '../bottom.php'; ?>
<?php
} // end render block

?>

<script>
document.addEventListener('DOMContentLoaded', function(){
  // When a Detalii button is clicked, populate modal then open Reveal programmatically
  document.querySelectorAll('.open-law').forEach(function(btn){
    btn.addEventListener('click', function(e){
      e.preventDefault();
      var raw = this.getAttribute('data-item');
      var item = {};
      try {
        // atob returns a binary string; convert to proper UTF-8 before JSON.parse
        var decoded = atob(raw);
        try {
          var jsonStr = decodeURIComponent(escape(decoded));
        } catch (e) {
          // fallback if escape/decodeURIComponent not available
          jsonStr = decoded;
        }
        item = JSON.parse(jsonStr);
      } catch (err) {}
      var titleEl = document.querySelector('#lawModal .modal-title');
      var leadEl = document.querySelector('#lawModal .lead');
      var bodyEl = document.querySelector('#lawModal .modal-body');
      titleEl.textContent = item.Titlu ? item.Titlu.trim() : (item.TipAct || 'Detalii');
      if (item.Titlu) { leadEl.style.display = 'none'; } else { leadEl.style.display = 'none'; }
      // Unescape any literal backslash-escaped newlines ("\\r\\n", "\\n") into real newlines
      function unescapeNewlines(s){
        if (!s) return '';
        try {
          return s.replace(/\\r\\n/g,'\n').replace(/\\n/g,'\n').replace(/\\r/g,'\n');
        } catch(e){ return s; }
      }
      var displayText = item.Text ? unescapeNewlines(item.Text) : '';
      bodyEl.textContent = displayText;
      document.getElementById('saveLaw').setAttribute('data-item', raw);
      // Open Reveal programmatically (requires jQuery + Foundation JS)
      if (typeof jQuery !== 'undefined' && typeof Foundation !== 'undefined') {
        var $modal = jQuery('#lawModal');
        var inst = $modal.data('reveal-instance');
        if (!inst) {
          inst = new Foundation.Reveal($modal);
          $modal.data('reveal-instance', inst);
          // ensure we clean up overlay/body classes when closed
          $modal.on('closed.zf.reveal', function(){
            // remove any stray overlay
            jQuery('.reveal-overlay').remove();
            jQuery('body').removeClass('is-reveal-open');
          });
        }
        inst.open();
      } else {
        // fallback: try to trigger the data-open behavior and ensure cleanup on close
        var openAttr = this.getAttribute('data-open');
        if (openAttr) {
          var target = document.getElementById(openAttr);
          if (target) {
            target.style.display = 'block';
            // add an overlay for fallback
            var overlay = document.createElement('div');
            overlay.className = 'reveal-overlay';
            document.body.appendChild(overlay);
            document.body.classList.add('is-reveal-open');
            // close handlers for fallback
            target.querySelectorAll('[data-close]').forEach(function(cb){
              cb.addEventListener('click', function(){
                target.style.display = 'none';
                if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay);
                document.body.classList.remove('is-reveal-open');
              });
            });
          }
        }
      }
    });
  });

  document.getElementById('saveLaw').addEventListener('click', function(){
    var raw = this.getAttribute('data-item');
    if (!raw) return;
    var item = {};
    try {
      var decoded = atob(raw);
      try {
        var jsonStr = decodeURIComponent(escape(decoded));
      } catch (e) {
        jsonStr = decoded;
      }
      item = JSON.parse(jsonStr);
    } catch(e) {}
    var fd = new FormData();
    fd.append('TipAct', item.TipAct || '');
    fd.append('Numar', item.Numar || '');
    fd.append('Titlu', item.Titlu || '');
    fd.append('DataVigoare', item.DataVigoare || '');
    fd.append('Emitent', item.Emitent || '');
    fd.append('Publicatie', item.Publicatie || '');
    fd.append('LinkHtml', item.LinkHtml || '');
    // ensure we send real newlines to the server
    function unescapeNewlines(s){
      if (!s) return '';
      try { return s.replace(/\\r\\n/g,'\n').replace(/\\n/g,'\n').replace(/\\r/g,'\n'); } catch(e) { return s; }
    }
    fd.append('Text', unescapeNewlines(item.Text || ''));

    fetch('save_law.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function(r){ return r.json(); })
      .then(function(j){
        var statusEl = document.getElementById('saveStatus');
        if (!statusEl) {
          if (j && j.success) {
            alert('Salvat cu succes (id=' + (j.id||'') + ')');
          } else {
            alert('Eroare la salvare: ' + (j.error || 'unknown'));
          }
          return;
        }
        statusEl.style.display = '';
        if (j && j.success) {
          statusEl.className = 'callout success';
          statusEl.textContent = 'Salvat cu succes (id=' + (j.id||'') + ')';
          // close modal after short delay
          setTimeout(function(){ var modal = document.getElementById('lawModal'); if(modal){ $(modal).foundation('close'); } }, 1200);
        } else {
          statusEl.className = 'callout alert';
          statusEl.textContent = 'Eroare la salvare: ' + (j.error || 'unknown');
        }
      }).catch(function(err){
        var statusEl = document.getElementById('saveStatus');
        if (statusEl) {
          statusEl.style.display = '';
          statusEl.className = 'callout alert';
          statusEl.textContent = 'Eroare la salvare: ' + err;
        } else {
          alert('Eroare la salvare: ' + err);
        }
      });
  });
});
</script>
