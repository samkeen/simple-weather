<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Curler {
    private $response_info = null;
    private $default_post_options = array(
        'CURLOPT_FAILONERROR'         => '1',
        'CURLOPT_FOLLOWLOCATION'    => 1, //allow redirects
        'CURLOPT_RETURNTRANSFER'    => 1, // return into a variable
        'CURLOPT_TIMEOUT'                => 10, // times out after n seconds
        'CURLOPT_POST'                     => 1 // set POST method
    );

    public function post($url, $post_fields=array(), array $authenticate=null) {
        $authenticate = is_array($authenticate)
            ?$authenticate
            :array('username'=>null,'password'=>null);
        $handle = curl_init();    // initialize curl handle
//        curl_setopt_array($handle, $this->default_post_options);
		$post_fields_string = $this->request_fields_string($post_fields);
//        var_dump($post_fields_string);die;
		curl_setopt($handle, CURLOPT_URL, $url); // set url to post to
		curl_setopt($handle, CURLOPT_FAILONERROR, 1);
		curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
		curl_setopt($handle, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($handle, CURLOPT_TIMEOUT, 10); // times out after 4s
		curl_setopt($handle, CURLOPT_POST, 1); // set POST method
		curl_setopt($handle, CURLOPT_POSTFIELDS, $post_fields_string); // add POST fields
		if ($authenticate) {
			if (!empty($authenticate['username'])&&!empty($authenticate['password'])) {
				curl_setopt($handle,CURLOPT_USERPWD,$authenticate['username'] . ":" . $authenticate['password']);
			} else {
				curl_close($handle);
				throw new Exception("Authentication Credentials Not set".__METHOD__);
			}
		}
		$result = curl_exec($handle); // run the whole process
		$this->response_info = curl_getinfo($handle);
		curl_close($handle);
        echo "result:\n";
		var_dump($result);
        echo "\n==============\n";
        var_dump($this->response_info);
	}

	public function get($url, $get_parameters=null, array $authenticate=null) {
        $authenticate = is_array($authenticate)
            ?$authenticate
            :array('username'=>null,'password'=>null);
        $handle = curl_init();    // initialize curl handle
		curl_setopt($handle, CURLOPT_URL,
                $url.$this->get_request_fields_string($get_parameters)); // set url to post to
		curl_setopt($handle, CURLOPT_FAILONERROR, 1);
		curl_setopt($handle, CURLOPT_HEADER, 1);
		curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
		curl_setopt($handle, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($handle, CURLOPT_TIMEOUT, 3); // times out after 4s
		if ($authenticate) {
            var_dump($authenticate);
			if (!empty($authenticate['username'])&&!empty($authenticate['password'])) {
				curl_setopt($handle,CURLOPT_USERPWD,$authenticate['username'] . ":" . $authenticate['password']);
			} else {
				curl_close($handle);
				throw new Exception("Authentication Credentials Not set".__METHOD__);
			}
		}
		$result = curl_exec($handle); // run the whole process
		$this->response_info = curl_getinfo($handle);
		curl_close($handle);
        echo "result:\n";
		var_dump($result);
        echo "\n==============\n";
        var_dump($this->response_info);
	}
    private function get_request_fields_string($request_fields, $encode=true) {
		$url_params = $this->request_fields_string($request_fields, $encode);
		return $url_params ? "?{$url_params}" : '';
	}
	private function request_fields_string($request_fields, $encode=true) {
		$post_fields_string = null;
		if(!empty($request_fields)) {
			// if we are given a string, break it apart so we can perform encoding on it
			if ( ! is_array($request_fields)) {
				$request_fields = ''.trim($request_fields,'?&');
				$request_fields = explode('=',$request_fields);
			}
			// encode the keys and values
			if($encode) {
				foreach ($request_fields as $key => $value) {
					$post_fields_string[] = urlencode($key).'='.urlencode($value);
				}
			} else {
				foreach ($request_fields as $key => $value) {
					$post_fields_string[] = "{$key}={$value}";
				}
			}
			// link the key=val pairs with & to complete the string.
			$post_fields_string = implode('&',$post_fields_string);
		}
		return $post_fields_string;
	}
}
?>
