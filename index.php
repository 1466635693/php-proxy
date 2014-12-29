<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('PROXY_LOG_FILE', __DIR__ . '/proxy.log');
Class Proxy{
	protected $log_file = PROXY_LOG_FILE;
	
	public function request(){
		$method = $_SERVER['REQUEST_METHOD'];
		$body = file_get_contents('php://input');
		$url = $_SERVER['REQUEST_URI'];
		$logs = array();
	
		$logs[] = sprintf("[%s]", date('Y-m-d H:i:s'));
		$logs[] = "Url: $url";
		$logs[] = "Method: $method";
		if($body){
			$logs[] = "Body: $body";
		}
		
		$headers = $this->get_all_headers();
		unset($headers['Accept-Encoding']);
		$header = array();
		foreach($headers as $k => $v){
			$header []= "$k: $v";
		}
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_HEADER, TRUE); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		//如果post，加上post内容
		if(strtoupper($method) == 'POST'){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		}
		$response = curl_exec($ch); 
		if($response === false){
			$logs[]= curl_error ($ch);
		}
		curl_close($ch); 
		list($response_header, $response_body) = explode("\r\n\r\n", $response, 2);
		foreach(explode("\r\n", $response_header) as $h){
			if(stripos(strtolower($h), 'Transfer-Encoding') === 0){
				continue;
			}
			if(stripos(strtolower($h), 'content-type') === 0){
				$content_type = trim(array_pop(explode(':', $h)));
				$logs []= $h;
				if(stripos($content_type, 'text') !== false or stripos($content_type, 'json') !== false){
					$logs[] = "Response: $response_body";
				}
			}
			header($h);
		}
		echo $response_body;
		$logs[] = "=============================================================================================\n\n";
		foreach($logs as $log){
			$this->log($log);
		}
	}
	
	/**
	 * 
	 * @param type $hostname
	 * @return type
	 */
	protected function get_host_by_name($hostname){
		return $this->hostnames[$hostname];
	}
	
	protected function log($message){
		file_put_contents($this->log_file, $message . "\n", FILE_APPEND);
	}
	protected function get_all_headers(){
		return apache_request_headers();
	}
}
$proxy = new Proxy();
$response = $proxy->request();