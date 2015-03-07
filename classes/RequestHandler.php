<?php
namespace classes;
require_once 'classes/RequestParams.php';

class RequestHandler {
	
	private $methods;
	
	public function __construct($methods) {
		if(!is_array($methods))
			$methods = array($methods);
		$this->methods = $methods;
	}
	
	public function getRequestParams() {
		$requestMethod = $_SERVER['REQUEST_METHOD'];
		foreach ($this->methods as $method) {
			if(strtoupper($method) == strtoupper($requestMethod)) {
				return new RequestParams($GLOBALS["_$requestMethod"]); 
			}
		}
	}
	
}
?>