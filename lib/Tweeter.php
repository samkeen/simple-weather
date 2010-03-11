<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
//class Tweeter {
//    public function tweet_text() {
//        $weather = $this->weather;
//        $today = array_shift($weather);
//        $tweet_txt = "Today({$today['day']}) lo:{$today['temp_low']} hi:{$today['temp_high']} {$today['weather_txt']}";
//        foreach ($weather as $weather_day) {
//            $tweet_txt .= "\n({$weather_day['day']}) lo:{$weather_day['temp_low']} hi:{$weather_day['temp_high']} {$weather_day['weather_txt']}";
//        }
//        $tweet_txt .= "\nsrc:Yahoo Weather";
//        return $tweet_txt;
//    }
//}
?>
<?php
/*
 * Downloaded from : http://www.digimantra.com
 * Script: To send tweet/update on twitter.com
 * License: Open for any purpose, some rights reserved :P
 * Testing : Basic testing done
 * Guarantee: Not at all, its all open source :)
*/
class Tweeter {
    const API_URL_STATUS="http://api.twitter.com/1/statuses/update.xml";
    private $credentials = array('username'=>null,'password'=>null);
    private $output="";
    private $debug=false;

    //prepares a credential array on the basis of credits provided
    // __test__test__
    function __construct($account_name, $password) {
        $this->credentials['username'] = $account_name;
        $this->credentials['password'] = $password;
    }

    private function build_twitter_uri($context, $action, $response_format=self::DEFAULT_RESPONSE_FORMAT) {
		if ($action==self::VERB_GET) {
			$uri = implode('/',array(self::BASE_REST_URI,$context)).".{$response_format}";
		} else {
			$uri = implode('/',array(self::BASE_REST_URI,$context,$action)).".{$response_format}";
		}
		return $uri;
	}

    function tweet($msg) {
        require dirname(__FILE__)."/Curler.php";
        $curler = new Curler();
        $curler->post(
                self::API_URL_STATUS,
                array("status"=>$msg),
                $this->credentials
        );


    }
    function output($show=false) {
        if($show) {
            header ("content-type: text/xml");
            echo $this->output;
        }
        else
            return $this->output;
    }
    function set_debug() {
        $this->debug=true;
    }
}
  