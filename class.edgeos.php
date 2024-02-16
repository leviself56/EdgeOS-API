<?php
/**
 * @project:	EdgeOS REST API
 * @access:		Thu Feb 15 14:34:00 CST 2023
 * @author:		Levi Self <levi@airlinkrb.com>
 **/

class EdgeOS {

	public $ip;
	public $token;
	public $api_url;

	public function __construct($ip, $user, $pass) {
		$this->api_url	= "https://".$ip."/api/v1.0";
		$this->ip		= $ip;
		$this->token 	= $this->login($ip, $user, $pass);
	}

    private function login($ip, $user, $pass) {
		$login_url = $this->api_url."/user/login";

        $credentials = json_encode(array(
            "username"  =>  $user,
            "password"  =>  $pass
        ));

        $headers = array(
            "Content-Type: application/json",
            "Accept: application/json",
            "Referer: https://$ip/"
        );

		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL, $login_url);
		curl_setopt($cURL, CURLOPT_POSTFIELDS, $credentials);
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURL, CURLOPT_HEADER, true);
		curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($cURL, CURLOPT_AUTOREFERER, true);
		curl_setopt($cURL, CURLOPT_HTTPHEADER, $headers);

		$user_agent	=	"Php/7.0 (Debian)";
		curl_setopt($cURL, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($cURL, CURLINFO_HEADER_OUT, false);
        curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($cURL, CURLOPT_ENCODING, "");
        curl_setopt($cURL, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				
		$result     = curl_exec($cURL);
    	$header     = curl_getinfo($cURL);
    	curl_close($cURL);

        preg_match('#x-auth-token: ([^\s]+)#i', $result, $token);
        if (isset($token[1]) && $token[1] != '') {
            return $token[1];
        } else {
			return false;
        }
	}

	private function query($type, $url, $payload=null) {
		$cURL = curl_init();	
		$complete_url = $this->api_url.$url;
		curl_setopt($cURL, CURLOPT_URL, $complete_url);

		switch ($type) {
			case "GET":
				curl_setopt($cURL, CURLOPT_HTTPGET, true);
				break;
			case "POST":
				curl_setopt($cURL, CURLOPT_POST, true);
				break;
			case "PATCH":
				curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, 'PATCH');
				break;
			case "PUT":
				curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, 'PUT');
				break;
			case "DELETE":
				curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
			default:
				curl_setopt($cURL, CURLOPT_HTTPGET, true);
		}

		if (isset($payload)) {
			curl_setopt($cURL, CURLOPT_POSTFIELDS, json_encode($payload));
		}
	
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Accept: application/json',
			'x-auth-token: '.$this->token));
		curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($cURL, CURLOPT_ENCODING, true);
		curl_setopt($cURL, CURLOPT_AUTOREFERER, true);
			
		$user_agent	=	"Php/7.0 (Debian)";
		curl_setopt($cURL, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($cURL, CURLINFO_HEADER_OUT, true);
					
		$result = curl_exec($cURL);
		$header  = curl_getinfo($cURL);
		curl_close($cURL);
	
		$json = json_decode($result, true);
	
		// ERROR CATCHING
		if ($header['http_code'] == "400" || $header['http_code'] == "404" || $header['http_code'] == "409") {
			$error = array("error" =>	array(
				"http_code"	=>	$header['http_code'],
				"url"		=>	$header['url'],
				"header"	=>	$header,
				"result"	=>	$result
			));
			return $error;
		}
		return $json;
	}

	public function GetSFPs() {
		$data = $this->query("GET", "/statistics");
		if (!isset($data[0])) {
			return false;
		}
		$sfp_data = array();
		foreach ($data[0]['interfaces'] as $interface) {
			if (isset($interface['statistics']['sfp'])) {
				$sfp_data[$interface['id']]	=	$interface['statistics']['sfp'];
			}
		}
		if (!empty($sfp_data)) {
			return $sfp_data;
		} else {
			return false;
		}
	}

	public function GetInterfaces() {
		$data = $this->query("GET", "/interfaces");
		if (!isset($data[0])) {
			return false;
		}
		$result = array();
		$i = 0;
		foreach ($data as $interface) {
			$result[$i]['id']			=	$interface['identification']['id'];
			$result[$i]['name']		=	$interface['identification']['name'];
			$result[$i]['mac']		=	$interface['identification']['mac'];
			$result[$i]['enabled']	=	$interface['status']['enabled'];
			$result[$i]['plugged']	=	$interface['status']['plugged'];
			$result[$i]['currentSpeed']=	$interface['status']['currentSpeed'];
			$result[$i]['speed']		=	$interface['status']['speed'];
			$result[$i]['mtu']		=	$interface['status']['mtu'];
			$result[$i]['cableLength']=	$interface['status']['cableLength'];
			if (isset($interface['port']['sfp'])) {
				$result[$i]['sfp_present']	=	$interface['port']['sfp']['present'];
				$result[$i]['sfp_vendor']	=	$interface['port']['sfp']['vendor'];
				$result[$i]['sfp_serial']	=	$interface['port']['sfp']['serial'];
				$result[$i]['sfp_txFault']	=	$interface['port']['sfp']['txFault'];
				$result[$i]['sfp_los']		=	$interface['port']['sfp']['los'];
			}
			$i++;
		}
		if (!empty($result)) {
			return $result;
		} else {
			return false;
		}
	}

	public function GetSystemInfo() {
		$data = $this->query("GET", "/statistics");
		if (!isset($data[0])) {
			return false;
		}
		$data1 = $this->query("GET", "/device");
		if (!isset($data1['identification'])) {
			return false;
		}
		$result = array(
			"cpu_usage"				=>	$data[0]['device']['cpu'][0]['usage'],
			"cpu_temp"				=>	$data[0]['device']['cpu'][0]['temperature'],
			"ram_usage"				=>	$data[0]['device']['ram']['usage'],
			"ram_free"				=>	$data[0]['device']['ram']['free'],
			"ram_total"				=>	$data[0]['device']['ram']['total'],
			"fan_speed_1"			=>	$data[0]['device']['fanSpeeds'][0]['value'],
			"fan_speed_2"			=>	$data[0]['device']['fanSpeeds'][1]['value'],
			"uptime"				=>	$data[0]['device']['uptime'],
			"device_mac"			=>	$data1['identification']['mac'],
			"device_model"			=>	$data1['identification']['model'],
			"device_firmware"		=>	$data1['identification']['firmwareVersion'],
			"device_fallbackIp"		=>	$data1['capabilities']['device']['defaultFallbackAddress']
		);
		return $result;
	}
}
?>
