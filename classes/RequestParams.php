<?php
namespace classes;

class RequestParams {
	
	private $params;
	
	public function __construct($params){
		if(is_array($params))
		$this->params = $params;
		else throw new \Exception('Os parametros precisam ser um array');
	}
	
	public function get($name) {
		$param = null;
		if(isset($this->params[$name])){
			$param = mysql_real_escape_string($this->params[$name]);
		}
		return $param;
	}
}
?>