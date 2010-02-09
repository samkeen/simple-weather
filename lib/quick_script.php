<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$x=new SimpleXMLElement(file_get_contents('http://weather.yahooapis.com/forecastrss?w=2475687'));
echo "";
//    ? echo "y"
//    : "n";
////        $xml = file_get_contents('http://weather.yahooapis.com/forecastrss?w=2475687');
//        $xml = file_get_contents(dirname(__FILE__).'/test.xml');
//        $matches = null;
//        preg_match_all(self::FORECAST_PATTERN, $xml, $matches);
//        self::digest_matchs($matches);
//        $xml = self::tweet_text();
////        $xml = new SimpleXMLElement($suff);
//        return $xml;
//    }
