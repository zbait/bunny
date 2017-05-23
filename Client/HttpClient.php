<?php

namespace Bunny\Client;

/**
 * HTTP Client
 */
class HttpClient {

    /**
	 * HTTP get request
	 *
	 * @param string $url 请求URL
     * @param int $timeOut 超时时间
     * @param int $connectTimeOut 连接超时时间
     *
	 * @return array content array('status' => true|false, 'content' => '', 'code' => '')
	 */
	public static function get(string $url, int $timeOut = 5, int $connectTimeOut = 5) :array {
		$oCurl = curl_init();
		if (stripos($url, "http://")!==FALSE || stripos($url, "https://") !== FALSE) {
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $connectTimeOut);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
        $error = curl_error($oCurl);
		curl_close ($oCurl);
		if (intval($aStatus["http_code"]) == 200) {
			return array(
					'status' => true,
					'content' => $sContent,
					'code' => $aStatus ["http_code"],
			);
		} else {
			return array(
					'status' => false,
					'content' => json_encode(array("error" => $error, "url" => $url)),
					'code' => $aStatus ["http_code"],
			);
		}
	}

    /**
	 * HTTP post request
	 *
	 * @param string $url 请求URL
     * @param string|array $param 请求参数
     * @param int $timeOut 超时时间
     * @param int $connectTimeOut 连接超时时间
     *
	 * @return array content array('status' => true|false, 'content' => '', 'code' => '')
	 */
	public static function http_post(string $url, $param, int $timeOut = 5, int $connectTimeOut = 5) :array {
		$oCurl = curl_init();
		if(stripos($url, "http://")!==FALSE || stripos($url, "https://") !== FALSE) {
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		if(is_string ($param)) {
			$strPOST = $param;
		}else{
			$aPOST = array();
			foreach($param as $key => $val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST = join ("&", $aPOST);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POST, true);
		curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
		curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $connectTimeOut);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
        $error = curl_error($oCurl);
		curl_close ($oCurl );
		if(intval($aStatus["http_code"]) == 200) {
			return array(
					'status' => true,
					'content' => $sContent,
					'code' => $aStatus ["http_code"],
			);
		}else{
			return array(
					'status' => false,
					'content' => json_encode(array("error" => $error, "url" => $url)),
					'code' => $aStatus ["http_code"],
			);
		}
	}
}