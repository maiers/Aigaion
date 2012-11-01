<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for encoding and decoding
| -------------------------------------------------------------------
|
|   The PHP functions "urlencode"/"urldecode" are not compatible with the 
|   Javascript functions "escape"/"unescape", "encodeURI"/"decodeURI", 
|   "encodeURIComponent"/"decodeURIComponent".
|
|   Here we have some PHP functions which behave like the corresponding 
|   Javascript functions. Those come in handy when dealing with Web 2.0 
|   Ajax applications.
|       
*/


/*
PHP URL encoding/decoding functions for Javascript interaction V2.0
(C) 2006 www.captain.at - all rights reserved
License: GPL
*/

function encodeURIComponent($string) {
   $result = "";
   for ($i = 0; $i < strlen($string); $i++) {
      $result .= encodeURIComponentbycharacter(urlencode($string[$i]));
   }
   return $result;
}

function encodeURIComponentbycharacter($char) {
   if ($char == '+') { return '%20'; }
   if ($char == '%21') { return '!'; }
   if ($char == '%27') { return "\'"; }
   if ($char == '%28') { return '('; }
   if ($char == '%29') { return ')'; }
   if ($char == '%2A') { return '*'; }
   if ($char == '%7E') { return '~'; }
   if ($char == '%80') { return '%E2%82%AC'; }
   if ($char == '%81') { return '%C2%81'; }
   if ($char == '%82') { return '%E2%80%9A'; }
   if ($char == '%83') { return '%C6%92'; }
   if ($char == '%84') { return '%E2%80%9E'; }
   if ($char == '%85') { return '%E2%80%A6'; }
   if ($char == '%86') { return '%E2%80%A0'; }
   if ($char == '%87') { return '%E2%80%A1'; }
   if ($char == '%88') { return '%CB%86'; }
   if ($char == '%89') { return '%E2%80%B0'; }
   if ($char == '%8A') { return '%C5%A0'; }
   if ($char == '%8B') { return '%E2%80%B9'; }
   if ($char == '%8C') { return '%C5%92'; }
   if ($char == '%8D') { return '%C2%8D'; }
   if ($char == '%8E') { return '%C5%BD'; }
   if ($char == '%8F') { return '%C2%8F'; }
   if ($char == '%90') { return '%C2%90'; }
   if ($char == '%91') { return '%E2%80%98'; }
   if ($char == '%92') { return '%E2%80%99'; }
   if ($char == '%93') { return '%E2%80%9C'; }
   if ($char == '%94') { return '%E2%80%9D'; }
   if ($char == '%95') { return '%E2%80%A2'; }
   if ($char == '%96') { return '%E2%80%93'; }
   if ($char == '%97') { return '%E2%80%94'; }
   if ($char == '%98') { return '%CB%9C'; }
   if ($char == '%99') { return '%E2%84%A2'; }
   if ($char == '%9A') { return '%C5%A1'; }
   if ($char == '%9B') { return '%E2%80%BA'; }
   if ($char == '%9C') { return '%C5%93'; }
   if ($char == '%9D') { return '%C2%9D'; }
   if ($char == '%9E') { return '%C5%BE'; }
   if ($char == '%9F') { return '%C5%B8'; }
   if ($char == '%A0') { return '%C2%A0'; }
   if ($char == '%A1') { return '%C2%A1'; }
   if ($char == '%A2') { return '%C2%A2'; }
   if ($char == '%A3') { return '%C2%A3'; }
   if ($char == '%A4') { return '%C2%A4'; }
   if ($char == '%A5') { return '%C2%A5'; }
   if ($char == '%A6') { return '%C2%A6'; }
   if ($char == '%A7') { return '%C2%A7'; }
   if ($char == '%A8') { return '%C2%A8'; }
   if ($char == '%A9') { return '%C2%A9'; }
   if ($char == '%AA') { return '%C2%AA'; }
   if ($char == '%AB') { return '%C2%AB'; }
   if ($char == '%AC') { return '%C2%AC'; }
   if ($char == '%AD') { return '%C2%AD'; }
   if ($char == '%AE') { return '%C2%AE'; }
   if ($char == '%AF') { return '%C2%AF'; }
   if ($char == '%B0') { return '%C2%B0'; }
   if ($char == '%B1') { return '%C2%B1'; }
   if ($char == '%B2') { return '%C2%B2'; }
   if ($char == '%B3') { return '%C2%B3'; }
   if ($char == '%B4') { return '%C2%B4'; }
   if ($char == '%B5') { return '%C2%B5'; }
   if ($char == '%B6') { return '%C2%B6'; }
   if ($char == '%B7') { return '%C2%B7'; }
   if ($char == '%B8') { return '%C2%B8'; }
   if ($char == '%B9') { return '%C2%B9'; }
   if ($char == '%BA') { return '%C2%BA'; }
   if ($char == '%BB') { return '%C2%BB'; }
   if ($char == '%BC') { return '%C2%BC'; }
   if ($char == '%BD') { return '%C2%BD'; }
   if ($char == '%BE') { return '%C2%BE'; }
   if ($char == '%BF') { return '%C2%BF'; }
   if ($char == '%C0') { return '%C3%80'; }
   if ($char == '%C1') { return '%C3%81'; }
   if ($char == '%C2') { return '%C3%82'; }
   if ($char == '%C3') { return '%C3%83'; }
   if ($char == '%C4') { return '%C3%84'; }
   if ($char == '%C5') { return '%C3%85'; }
   if ($char == '%C6') { return '%C3%86'; }
   if ($char == '%C7') { return '%C3%87'; }
   if ($char == '%C8') { return '%C3%88'; }
   if ($char == '%C9') { return '%C3%89'; }
   if ($char == '%CA') { return '%C3%8A'; }
   if ($char == '%CB') { return '%C3%8B'; }
   if ($char == '%CC') { return '%C3%8C'; }
   if ($char == '%CD') { return '%C3%8D'; }
   if ($char == '%CE') { return '%C3%8E'; }
   if ($char == '%CF') { return '%C3%8F'; }
   if ($char == '%D0') { return '%C3%90'; }
   if ($char == '%D1') { return '%C3%91'; }
   if ($char == '%D2') { return '%C3%92'; }
   if ($char == '%D3') { return '%C3%93'; }
   if ($char == '%D4') { return '%C3%94'; }
   if ($char == '%D5') { return '%C3%95'; }
   if ($char == '%D6') { return '%C3%96'; }
   if ($char == '%D7') { return '%C3%97'; }
   if ($char == '%D8') { return '%C3%98'; }
   if ($char == '%D9') { return '%C3%99'; }
   if ($char == '%DA') { return '%C3%9A'; }
   if ($char == '%DB') { return '%C3%9B'; }
   if ($char == '%DC') { return '%C3%9C'; }
   if ($char == '%DD') { return '%C3%9D'; }
   if ($char == '%DE') { return '%C3%9E'; }
   if ($char == '%DF') { return '%C3%9F'; }
   if ($char == '%E0') { return '%C3%A0'; }
   if ($char == '%E1') { return '%C3%A1'; }
   if ($char == '%E2') { return '%C3%A2'; }
   if ($char == '%E3') { return '%C3%A3'; }
   if ($char == '%E4') { return '%C3%A4'; }
   if ($char == '%E5') { return '%C3%A5'; }
   if ($char == '%E6') { return '%C3%A6'; }
   if ($char == '%E7') { return '%C3%A7'; }
   if ($char == '%E8') { return '%C3%A8'; }
   if ($char == '%E9') { return '%C3%A9'; }
   if ($char == '%EA') { return '%C3%AA'; }
   if ($char == '%EB') { return '%C3%AB'; }
   if ($char == '%EC') { return '%C3%AC'; }
   if ($char == '%ED') { return '%C3%AD'; }
   if ($char == '%EE') { return '%C3%AE'; }
   if ($char == '%EF') { return '%C3%AF'; }
   if ($char == '%F0') { return '%C3%B0'; }
   if ($char == '%F1') { return '%C3%B1'; }
   if ($char == '%F2') { return '%C3%B2'; }
   if ($char == '%F3') { return '%C3%B3'; }
   if ($char == '%F4') { return '%C3%B4'; }
   if ($char == '%F5') { return '%C3%B5'; }
   if ($char == '%F6') { return '%C3%B6'; }
   if ($char == '%F7') { return '%C3%B7'; }
   if ($char == '%F8') { return '%C3%B8'; }
   if ($char == '%F9') { return '%C3%B9'; }
   if ($char == '%FA') { return '%C3%BA'; }
   if ($char == '%FB') { return '%C3%BB'; }
   if ($char == '%FC') { return '%C3%BC'; }
   if ($char == '%FD') { return '%C3%BD'; }
   if ($char == '%FE') { return '%C3%BE'; }
   if ($char == '%FF') { return '%C3%BF'; }
   return $char;
}


function decodeURIComponent($string) {
   $result = "";
   for ($i = 0; $i < strlen($string); $i++) {
      $result .= urldecode(decodeURIComponentbycharacter($string[$i]));
   }
   return $result;
}

function decodeURIComponentbycharacter($char) {
   if ($char == '%20') { return '+'; }
   if ($char == '!') { return '%21'; }
   if ($char == "\'") { return '%27'; }
   if ($char == '(') { return '%28'; }
   if ($char == ')') { return '%29'; }
   if ($char == '*') { return '%2A'; }
   if ($char == '~') { return '%7E'; }
   if ($char == '%E2%82%AC') { return '%80'; }
   if ($char == '%C2%81') { return '%81'; }
   if ($char == '%E2%80%9A') { return '%82'; }
   if ($char == '%C6%92') { return '%83'; }
   if ($char == '%E2%80%9E') { return '%84'; }
   if ($char == '%E2%80%A6') { return '%85'; }
   if ($char == '%E2%80%A0') { return '%86'; }
   if ($char == '%E2%80%A1') { return '%87'; }
   if ($char == '%CB%86') { return '%88'; }
   if ($char == '%E2%80%B0') { return '%89'; }
   if ($char == '%C5%A0') { return '%8A'; }
   if ($char == '%E2%80%B9') { return '%8B'; }
   if ($char == '%C5%92') { return '%8C'; }
   if ($char == '%C2%8D') { return '%8D'; }
   if ($char == '%C5%BD') { return '%8E'; }
   if ($char == '%C2%8F') { return '%8F'; }
   if ($char == '%C2%90') { return '%90'; }
   if ($char == '%E2%80%98') { return '%91'; }
   if ($char == '%E2%80%99') { return '%92'; }
   if ($char == '%E2%80%9C') { return '%93'; }
   if ($char == '%E2%80%9D') { return '%94'; }
   if ($char == '%E2%80%A2') { return '%95'; }
   if ($char == '%E2%80%93') { return '%96'; }
   if ($char == '%E2%80%94') { return '%97'; }
   if ($char == '%CB%9C') { return '%98'; }
   if ($char == '%E2%84%A2') { return '%99'; }
   if ($char == '%C5%A1') { return '%9A'; }
   if ($char == '%E2%80%BA') { return '%9B'; }
   if ($char == '%C5%93') { return '%9C'; }
   if ($char == '%C2%9D') { return '%9D'; }
   if ($char == '%C5%BE') { return '%9E'; }
   if ($char == '%C5%B8') { return '%9F'; }
   if ($char == '%C2%A0') { return '%A0'; }
   if ($char == '%C2%A1') { return '%A1'; }
   if ($char == '%C2%A2') { return '%A2'; }
   if ($char == '%C2%A3') { return '%A3'; }
   if ($char == '%C2%A4') { return '%A4'; }
   if ($char == '%C2%A5') { return '%A5'; }
   if ($char == '%C2%A6') { return '%A6'; }
   if ($char == '%C2%A7') { return '%A7'; }
   if ($char == '%C2%A8') { return '%A8'; }
   if ($char == '%C2%A9') { return '%A9'; }
   if ($char == '%C2%AA') { return '%AA'; }
   if ($char == '%C2%AB') { return '%AB'; }
   if ($char == '%C2%AC') { return '%AC'; }
   if ($char == '%C2%AD') { return '%AD'; }
   if ($char == '%C2%AE') { return '%AE'; }
   if ($char == '%C2%AF') { return '%AF'; }
   if ($char == '%C2%B0') { return '%B0'; }
   if ($char == '%C2%B1') { return '%B1'; }
   if ($char == '%C2%B2') { return '%B2'; }
   if ($char == '%C2%B3') { return '%B3'; }
   if ($char == '%C2%B4') { return '%B4'; }
   if ($char == '%C2%B5') { return '%B5'; }
   if ($char == '%C2%B6') { return '%B6'; }
   if ($char == '%C2%B7') { return '%B7'; }
   if ($char == '%C2%B8') { return '%B8'; }
   if ($char == '%C2%B9') { return '%B9'; }
   if ($char == '%C2%BA') { return '%BA'; }
   if ($char == '%C2%BB') { return '%BB'; }
   if ($char == '%C2%BC') { return '%BC'; }
   if ($char == '%C2%BD') { return '%BD'; }
   if ($char == '%C2%BE') { return '%BE'; }
   if ($char == '%C2%BF') { return '%BF'; }
   if ($char == '%C3%80') { return '%C0'; }
   if ($char == '%C3%81') { return '%C1'; }
   if ($char == '%C3%82') { return '%C2'; }
   if ($char == '%C3%83') { return '%C3'; }
   if ($char == '%C3%84') { return '%C4'; }
   if ($char == '%C3%85') { return '%C5'; }
   if ($char == '%C3%86') { return '%C6'; }
   if ($char == '%C3%87') { return '%C7'; }
   if ($char == '%C3%88') { return '%C8'; }
   if ($char == '%C3%89') { return '%C9'; }
   if ($char == '%C3%8A') { return '%CA'; }
   if ($char == '%C3%8B') { return '%CB'; }
   if ($char == '%C3%8C') { return '%CC'; }
   if ($char == '%C3%8D') { return '%CD'; }
   if ($char == '%C3%8E') { return '%CE'; }
   if ($char == '%C3%8F') { return '%CF'; }
   if ($char == '%C3%90') { return '%D0'; }
   if ($char == '%C3%91') { return '%D1'; }
   if ($char == '%C3%92') { return '%D2'; }
   if ($char == '%C3%93') { return '%D3'; }
   if ($char == '%C3%94') { return '%D4'; }
   if ($char == '%C3%95') { return '%D5'; }
   if ($char == '%C3%96') { return '%D6'; }
   if ($char == '%C3%97') { return '%D7'; }
   if ($char == '%C3%98') { return '%D8'; }
   if ($char == '%C3%99') { return '%D9'; }
   if ($char == '%C3%9A') { return '%DA'; }
   if ($char == '%C3%9B') { return '%DB'; }
   if ($char == '%C3%9C') { return '%DC'; }
   if ($char == '%C3%9D') { return '%DD'; }
   if ($char == '%C3%9E') { return '%DE'; }
   if ($char == '%C3%9F') { return '%DF'; }
   if ($char == '%C3%A0') { return '%E0'; }
   if ($char == '%C3%A1') { return '%E1'; }
   if ($char == '%C3%A2') { return '%E2'; }
   if ($char == '%C3%A3') { return '%E3'; }
   if ($char == '%C3%A4') { return '%E4'; }
   if ($char == '%C3%A5') { return '%E5'; }
   if ($char == '%C3%A6') { return '%E6'; }
   if ($char == '%C3%A7') { return '%E7'; }
   if ($char == '%C3%A8') { return '%E8'; }
   if ($char == '%C3%A9') { return '%E9'; }
   if ($char == '%C3%AA') { return '%EA'; }
   if ($char == '%C3%AB') { return '%EB'; }
   if ($char == '%C3%AC') { return '%EC'; }
   if ($char == '%C3%AD') { return '%ED'; }
   if ($char == '%C3%AE') { return '%EE'; }
   if ($char == '%C3%AF') { return '%EF'; }
   if ($char == '%C3%B0') { return '%F0'; }
   if ($char == '%C3%B1') { return '%F1'; }
   if ($char == '%C3%B2') { return '%F2'; }
   if ($char == '%C3%B3') { return '%F3'; }
   if ($char == '%C3%B4') { return '%F4'; }
   if ($char == '%C3%B5') { return '%F5'; }
   if ($char == '%C3%B6') { return '%F6'; }
   if ($char == '%C3%B7') { return '%F7'; }
   if ($char == '%C3%B8') { return '%F8'; }
   if ($char == '%C3%B9') { return '%F9'; }
   if ($char == '%C3%BA') { return '%FA'; }
   if ($char == '%C3%BB') { return '%FB'; }
   if ($char == '%C3%BC') { return '%FC'; }
   if ($char == '%C3%BD') { return '%FD'; }
   if ($char == '%C3%BE') { return '%FE'; }
   if ($char == '%C3%BF') { return '%FF'; }
   return $char;
}

function encodeURI($string) {
   $result = "";
   for ($i = 0; $i < strlen($string); $i++) {
      $result .= encodeURIbycharacter(urlencode($string[$i]));
   }
   return $result;
}

function encodeURIbycharacter($char) {
   if ($char == '+') { return '%20'; }
   if ($char == '%21') { return '!'; }
   if ($char == '%23') { return '#'; }
   if ($char == '%24') { return '$'; }
   if ($char == '%26') { return '&'; }
   if ($char == '%27') { return "\'"; }
   if ($char == '%28') { return '('; }
   if ($char == '%29') { return ')'; }
   if ($char == '%2A') { return '*'; }
   if ($char == '%2B') { return '+'; }
   if ($char == '%2C') { return ','; }
   if ($char == '%2F') { return '/'; }
   if ($char == '%3A') { return ':'; }
   if ($char == '%3B') { return ';'; }
   if ($char == '%3D') { return '='; }
   if ($char == '%3F') { return '?'; }
   if ($char == '%40') { return '@'; }
   if ($char == '%7E') { return '~'; }
   if ($char == '%80') { return '%E2%82%AC'; }
   if ($char == '%81') { return '%C2%81'; }
   if ($char == '%82') { return '%E2%80%9A'; }
   if ($char == '%83') { return '%C6%92'; }
   if ($char == '%84') { return '%E2%80%9E'; }
   if ($char == '%85') { return '%E2%80%A6'; }
   if ($char == '%86') { return '%E2%80%A0'; }
   if ($char == '%87') { return '%E2%80%A1'; }
   if ($char == '%88') { return '%CB%86'; }
   if ($char == '%89') { return '%E2%80%B0'; }
   if ($char == '%8A') { return '%C5%A0'; }
   if ($char == '%8B') { return '%E2%80%B9'; }
   if ($char == '%8C') { return '%C5%92'; }
   if ($char == '%8D') { return '%C2%8D'; }
   if ($char == '%8E') { return '%C5%BD'; }
   if ($char == '%8F') { return '%C2%8F'; }
   if ($char == '%90') { return '%C2%90'; }
   if ($char == '%91') { return '%E2%80%98'; }
   if ($char == '%92') { return '%E2%80%99'; }
   if ($char == '%93') { return '%E2%80%9C'; }
   if ($char == '%94') { return '%E2%80%9D'; }
   if ($char == '%95') { return '%E2%80%A2'; }
   if ($char == '%96') { return '%E2%80%93'; }
   if ($char == '%97') { return '%E2%80%94'; }
   if ($char == '%98') { return '%CB%9C'; }
   if ($char == '%99') { return '%E2%84%A2'; }
   if ($char == '%9A') { return '%C5%A1'; }
   if ($char == '%9B') { return '%E2%80%BA'; }
   if ($char == '%9C') { return '%C5%93'; }
   if ($char == '%9D') { return '%C2%9D'; }
   if ($char == '%9E') { return '%C5%BE'; }
   if ($char == '%9F') { return '%C5%B8'; }
   if ($char == '%A0') { return '%C2%A0'; }
   if ($char == '%A1') { return '%C2%A1'; }
   if ($char == '%A2') { return '%C2%A2'; }
   if ($char == '%A3') { return '%C2%A3'; }
   if ($char == '%A4') { return '%C2%A4'; }
   if ($char == '%A5') { return '%C2%A5'; }
   if ($char == '%A6') { return '%C2%A6'; }
   if ($char == '%A7') { return '%C2%A7'; }
   if ($char == '%A8') { return '%C2%A8'; }
   if ($char == '%A9') { return '%C2%A9'; }
   if ($char == '%AA') { return '%C2%AA'; }
   if ($char == '%AB') { return '%C2%AB'; }
   if ($char == '%AC') { return '%C2%AC'; }
   if ($char == '%AD') { return '%C2%AD'; }
   if ($char == '%AE') { return '%C2%AE'; }
   if ($char == '%AF') { return '%C2%AF'; }
   if ($char == '%B0') { return '%C2%B0'; }
   if ($char == '%B1') { return '%C2%B1'; }
   if ($char == '%B2') { return '%C2%B2'; }
   if ($char == '%B3') { return '%C2%B3'; }
   if ($char == '%B4') { return '%C2%B4'; }
   if ($char == '%B5') { return '%C2%B5'; }
   if ($char == '%B6') { return '%C2%B6'; }
   if ($char == '%B7') { return '%C2%B7'; }
   if ($char == '%B8') { return '%C2%B8'; }
   if ($char == '%B9') { return '%C2%B9'; }
   if ($char == '%BA') { return '%C2%BA'; }
   if ($char == '%BB') { return '%C2%BB'; }
   if ($char == '%BC') { return '%C2%BC'; }
   if ($char == '%BD') { return '%C2%BD'; }
   if ($char == '%BE') { return '%C2%BE'; }
   if ($char == '%BF') { return '%C2%BF'; }
   if ($char == '%C0') { return '%C3%80'; }
   if ($char == '%C1') { return '%C3%81'; }
   if ($char == '%C2') { return '%C3%82'; }
   if ($char == '%C3') { return '%C3%83'; }
   if ($char == '%C4') { return '%C3%84'; }
   if ($char == '%C5') { return '%C3%85'; }
   if ($char == '%C6') { return '%C3%86'; }
   if ($char == '%C7') { return '%C3%87'; }
   if ($char == '%C8') { return '%C3%88'; }
   if ($char == '%C9') { return '%C3%89'; }
   if ($char == '%CA') { return '%C3%8A'; }
   if ($char == '%CB') { return '%C3%8B'; }
   if ($char == '%CC') { return '%C3%8C'; }
   if ($char == '%CD') { return '%C3%8D'; }
   if ($char == '%CE') { return '%C3%8E'; }
   if ($char == '%CF') { return '%C3%8F'; }
   if ($char == '%D0') { return '%C3%90'; }
   if ($char == '%D1') { return '%C3%91'; }
   if ($char == '%D2') { return '%C3%92'; }
   if ($char == '%D3') { return '%C3%93'; }
   if ($char == '%D4') { return '%C3%94'; }
   if ($char == '%D5') { return '%C3%95'; }
   if ($char == '%D6') { return '%C3%96'; }
   if ($char == '%D7') { return '%C3%97'; }
   if ($char == '%D8') { return '%C3%98'; }
   if ($char == '%D9') { return '%C3%99'; }
   if ($char == '%DA') { return '%C3%9A'; }
   if ($char == '%DB') { return '%C3%9B'; }
   if ($char == '%DC') { return '%C3%9C'; }
   if ($char == '%DD') { return '%C3%9D'; }
   if ($char == '%DE') { return '%C3%9E'; }
   if ($char == '%DF') { return '%C3%9F'; }
   if ($char == '%E0') { return '%C3%A0'; }
   if ($char == '%E1') { return '%C3%A1'; }
   if ($char == '%E2') { return '%C3%A2'; }
   if ($char == '%E3') { return '%C3%A3'; }
   if ($char == '%E4') { return '%C3%A4'; }
   if ($char == '%E5') { return '%C3%A5'; }
   if ($char == '%E6') { return '%C3%A6'; }
   if ($char == '%E7') { return '%C3%A7'; }
   if ($char == '%E8') { return '%C3%A8'; }
   if ($char == '%E9') { return '%C3%A9'; }
   if ($char == '%EA') { return '%C3%AA'; }
   if ($char == '%EB') { return '%C3%AB'; }
   if ($char == '%EC') { return '%C3%AC'; }
   if ($char == '%ED') { return '%C3%AD'; }
   if ($char == '%EE') { return '%C3%AE'; }
   if ($char == '%EF') { return '%C3%AF'; }
   if ($char == '%F0') { return '%C3%B0'; }
   if ($char == '%F1') { return '%C3%B1'; }
   if ($char == '%F2') { return '%C3%B2'; }
   if ($char == '%F3') { return '%C3%B3'; }
   if ($char == '%F4') { return '%C3%B4'; }
   if ($char == '%F5') { return '%C3%B5'; }
   if ($char == '%F6') { return '%C3%B6'; }
   if ($char == '%F7') { return '%C3%B7'; }
   if ($char == '%F8') { return '%C3%B8'; }
   if ($char == '%F9') { return '%C3%B9'; }
   if ($char == '%FA') { return '%C3%BA'; }
   if ($char == '%FB') { return '%C3%BB'; }
   if ($char == '%FC') { return '%C3%BC'; }
   if ($char == '%FD') { return '%C3%BD'; }
   if ($char == '%FE') { return '%C3%BE'; }
   if ($char == '%FF') { return '%C3%BF'; }
   return $char;
}


function decodeURI($string) {
   $result = "";
   for ($i = 0; $i < strlen($string); $i++) {
      $result .= urldecode(decodeURIbycharacter($string[$i]));
   }
   return $result;
}

function decodeURIbycharacter($char) {
   if ($char == '%20') { return '+'; }
   if ($char == '!') { return '%21'; }
   if ($char == '#') { return '%23'; }
   if ($char == '$') { return '%24'; }
   if ($char == '&') { return '%26'; }
   if ($char == "\'") { return '%27'; }
   if ($char == '(') { return '%28'; }
   if ($char == ')') { return '%29'; }
   if ($char == '*') { return '%2A'; }
   if ($char == '+') { return '%2B'; }
   if ($char == ',') { return '%2C'; }
   if ($char == '/') { return '%2F'; }
   if ($char == ':') { return '%3A'; }
   if ($char == ';') { return '%3B'; }
   if ($char == '=') { return '%3D'; }
   if ($char == '?') { return '%3F'; }
   if ($char == '@') { return '%40'; }
   if ($char == '~') { return '%7E'; }
   if ($char == '%E2%82%AC') { return '%80'; }
   if ($char == '%C2%81') { return '%81'; }
   if ($char == '%E2%80%9A') { return '%82'; }
   if ($char == '%C6%92') { return '%83'; }
   if ($char == '%E2%80%9E') { return '%84'; }
   if ($char == '%E2%80%A6') { return '%85'; }
   if ($char == '%E2%80%A0') { return '%86'; }
   if ($char == '%E2%80%A1') { return '%87'; }
   if ($char == '%CB%86') { return '%88'; }
   if ($char == '%E2%80%B0') { return '%89'; }
   if ($char == '%C5%A0') { return '%8A'; }
   if ($char == '%E2%80%B9') { return '%8B'; }
   if ($char == '%C5%92') { return '%8C'; }
   if ($char == '%C2%8D') { return '%8D'; }
   if ($char == '%C5%BD') { return '%8E'; }
   if ($char == '%C2%8F') { return '%8F'; }
   if ($char == '%C2%90') { return '%90'; }
   if ($char == '%E2%80%98') { return '%91'; }
   if ($char == '%E2%80%99') { return '%92'; }
   if ($char == '%E2%80%9C') { return '%93'; }
   if ($char == '%E2%80%9D') { return '%94'; }
   if ($char == '%E2%80%A2') { return '%95'; }
   if ($char == '%E2%80%93') { return '%96'; }
   if ($char == '%E2%80%94') { return '%97'; }
   if ($char == '%CB%9C') { return '%98'; }
   if ($char == '%E2%84%A2') { return '%99'; }
   if ($char == '%C5%A1') { return '%9A'; }
   if ($char == '%E2%80%BA') { return '%9B'; }
   if ($char == '%C5%93') { return '%9C'; }
   if ($char == '%C2%9D') { return '%9D'; }
   if ($char == '%C5%BE') { return '%9E'; }
   if ($char == '%C5%B8') { return '%9F'; }
   if ($char == '%C2%A0') { return '%A0'; }
   if ($char == '%C2%A1') { return '%A1'; }
   if ($char == '%C2%A2') { return '%A2'; }
   if ($char == '%C2%A3') { return '%A3'; }
   if ($char == '%C2%A4') { return '%A4'; }
   if ($char == '%C2%A5') { return '%A5'; }
   if ($char == '%C2%A6') { return '%A6'; }
   if ($char == '%C2%A7') { return '%A7'; }
   if ($char == '%C2%A8') { return '%A8'; }
   if ($char == '%C2%A9') { return '%A9'; }
   if ($char == '%C2%AA') { return '%AA'; }
   if ($char == '%C2%AB') { return '%AB'; }
   if ($char == '%C2%AC') { return '%AC'; }
   if ($char == '%C2%AD') { return '%AD'; }
   if ($char == '%C2%AE') { return '%AE'; }
   if ($char == '%C2%AF') { return '%AF'; }
   if ($char == '%C2%B0') { return '%B0'; }
   if ($char == '%C2%B1') { return '%B1'; }
   if ($char == '%C2%B2') { return '%B2'; }
   if ($char == '%C2%B3') { return '%B3'; }
   if ($char == '%C2%B4') { return '%B4'; }
   if ($char == '%C2%B5') { return '%B5'; }
   if ($char == '%C2%B6') { return '%B6'; }
   if ($char == '%C2%B7') { return '%B7'; }
   if ($char == '%C2%B8') { return '%B8'; }
   if ($char == '%C2%B9') { return '%B9'; }
   if ($char == '%C2%BA') { return '%BA'; }
   if ($char == '%C2%BB') { return '%BB'; }
   if ($char == '%C2%BC') { return '%BC'; }
   if ($char == '%C2%BD') { return '%BD'; }
   if ($char == '%C2%BE') { return '%BE'; }
   if ($char == '%C2%BF') { return '%BF'; }
   if ($char == '%C3%80') { return '%C0'; }
   if ($char == '%C3%81') { return '%C1'; }
   if ($char == '%C3%82') { return '%C2'; }
   if ($char == '%C3%83') { return '%C3'; }
   if ($char == '%C3%84') { return '%C4'; }
   if ($char == '%C3%85') { return '%C5'; }
   if ($char == '%C3%86') { return '%C6'; }
   if ($char == '%C3%87') { return '%C7'; }
   if ($char == '%C3%88') { return '%C8'; }
   if ($char == '%C3%89') { return '%C9'; }
   if ($char == '%C3%8A') { return '%CA'; }
   if ($char == '%C3%8B') { return '%CB'; }
   if ($char == '%C3%8C') { return '%CC'; }
   if ($char == '%C3%8D') { return '%CD'; }
   if ($char == '%C3%8E') { return '%CE'; }
   if ($char == '%C3%8F') { return '%CF'; }
   if ($char == '%C3%90') { return '%D0'; }
   if ($char == '%C3%91') { return '%D1'; }
   if ($char == '%C3%92') { return '%D2'; }
   if ($char == '%C3%93') { return '%D3'; }
   if ($char == '%C3%94') { return '%D4'; }
   if ($char == '%C3%95') { return '%D5'; }
   if ($char == '%C3%96') { return '%D6'; }
   if ($char == '%C3%97') { return '%D7'; }
   if ($char == '%C3%98') { return '%D8'; }
   if ($char == '%C3%99') { return '%D9'; }
   if ($char == '%C3%9A') { return '%DA'; }
   if ($char == '%C3%9B') { return '%DB'; }
   if ($char == '%C3%9C') { return '%DC'; }
   if ($char == '%C3%9D') { return '%DD'; }
   if ($char == '%C3%9E') { return '%DE'; }
   if ($char == '%C3%9F') { return '%DF'; }
   if ($char == '%C3%A0') { return '%E0'; }
   if ($char == '%C3%A1') { return '%E1'; }
   if ($char == '%C3%A2') { return '%E2'; }
   if ($char == '%C3%A3') { return '%E3'; }
   if ($char == '%C3%A4') { return '%E4'; }
   if ($char == '%C3%A5') { return '%E5'; }
   if ($char == '%C3%A6') { return '%E6'; }
   if ($char == '%C3%A7') { return '%E7'; }
   if ($char == '%C3%A8') { return '%E8'; }
   if ($char == '%C3%A9') { return '%E9'; }
   if ($char == '%C3%AA') { return '%EA'; }
   if ($char == '%C3%AB') { return '%EB'; }
   if ($char == '%C3%AC') { return '%EC'; }
   if ($char == '%C3%AD') { return '%ED'; }
   if ($char == '%C3%AE') { return '%EE'; }
   if ($char == '%C3%AF') { return '%EF'; }
   if ($char == '%C3%B0') { return '%F0'; }
   if ($char == '%C3%B1') { return '%F1'; }
   if ($char == '%C3%B2') { return '%F2'; }
   if ($char == '%C3%B3') { return '%F3'; }
   if ($char == '%C3%B4') { return '%F4'; }
   if ($char == '%C3%B5') { return '%F5'; }
   if ($char == '%C3%B6') { return '%F6'; }
   if ($char == '%C3%B7') { return '%F7'; }
   if ($char == '%C3%B8') { return '%F8'; }
   if ($char == '%C3%B9') { return '%F9'; }
   if ($char == '%C3%BA') { return '%FA'; }
   if ($char == '%C3%BB') { return '%FB'; }
   if ($char == '%C3%BC') { return '%FC'; }
   if ($char == '%C3%BD') { return '%FD'; }
   if ($char == '%C3%BE') { return '%FE'; }
   if ($char == '%C3%BF') { return '%FF'; }
   return $char;
}




function escape($string) {
   $result = "";
   for ($i = 0; $i < strlen($string); $i++) {
      $result .= escapebycharacter(urlencode($string[$i]));
   }
   return $result;
}

function escapebycharacter($char) {
   if ($char == '+') { return '%20'; }
   if ($char == '%2A') { return '*'; }
   if ($char == '%2B') { return '+'; }
   if ($char == '%2F') { return '/'; }
   if ($char == '%40') { return '@'; }
   if ($char == '%80') { return '%u20AC'; }
   if ($char == '%82') { return '%u201A'; }
   if ($char == '%83') { return '%u0192'; }
   if ($char == '%84') { return '%u201E'; }
   if ($char == '%85') { return '%u2026'; }
   if ($char == '%86') { return '%u2020'; }
   if ($char == '%87') { return '%u2021'; }
   if ($char == '%88') { return '%u02C6'; }
   if ($char == '%89') { return '%u2030'; }
   if ($char == '%8A') { return '%u0160'; }
   if ($char == '%8B') { return '%u2039'; }
   if ($char == '%8C') { return '%u0152'; }
   if ($char == '%8E') { return '%u017D'; }
   if ($char == '%91') { return '%u2018'; }
   if ($char == '%92') { return '%u2019'; }
   if ($char == '%93') { return '%u201C'; }
   if ($char == '%94') { return '%u201D'; }
   if ($char == '%95') { return '%u2022'; }
   if ($char == '%96') { return '%u2013'; }
   if ($char == '%97') { return '%u2014'; }
   if ($char == '%98') { return '%u02DC'; }
   if ($char == '%99') { return '%u2122'; }
   if ($char == '%9A') { return '%u0161'; }
   if ($char == '%9B') { return '%u203A'; }
   if ($char == '%9C') { return '%u0153'; }
   if ($char == '%9E') { return '%u017E'; }
   if ($char == '%9F') { return '%u0178'; }
   return $char;
}


function unescape($string) {
   $result = "";
   for ($i = 0; $i < strlen($string); $i++) {
      $result .= urldecode(unescapebycharacter($string[$i]));
   }
   return $result;
}

function unescapebycharacter($char) {
   if ($char == '%20') { return '+'; }
   if ($char == '*') { return '%2A'; }
   if ($char == '+') { return '%2B'; }
   if ($char == '/') { return '%2F'; }
   if ($char == '@') { return '%40'; }
   if ($char == '%u20AC') { return '%80'; }
   if ($char == '%u201A') { return '%82'; }
   if ($char == '%u0192') { return '%83'; }
   if ($char == '%u201E') { return '%84'; }
   if ($char == '%u2026') { return '%85'; }
   if ($char == '%u2020') { return '%86'; }
   if ($char == '%u2021') { return '%87'; }
   if ($char == '%u02C6') { return '%88'; }
   if ($char == '%u2030') { return '%89'; }
   if ($char == '%u0160') { return '%8A'; }
   if ($char == '%u2039') { return '%8B'; }
   if ($char == '%u0152') { return '%8C'; }
   if ($char == '%u017D') { return '%8E'; }
   if ($char == '%u2018') { return '%91'; }
   if ($char == '%u2019') { return '%92'; }
   if ($char == '%u201C') { return '%93'; }
   if ($char == '%u201D') { return '%94'; }
   if ($char == '%u2022') { return '%95'; }
   if ($char == '%u2013') { return '%96'; }
   if ($char == '%u2014') { return '%97'; }
   if ($char == '%u02DC') { return '%98'; }
   if ($char == '%u2122') { return '%99'; }
   if ($char == '%u0161') { return '%9A'; }
   if ($char == '%u203A') { return '%9B'; }
   if ($char == '%u0153') { return '%9C'; }
   if ($char == '%u017E') { return '%9E'; }
   if ($char == '%u0178') { return '%9F'; }
   return $char;
}

?>