<?php
namespace classes;
require_once 'classes/RequestHandler.php';
require_once 'classes/RequiredParamsValidator.php';
header('Content-Type: application/json');

class StandardRequestProcessor {
	
	private $requestHandler;
	private $params;
	private $validator;
	private $methods;
	private $requiredParams;
	private $processFunction;
	
	public function __construct($methods, $requiredParams, \Closure $processFunction) {
		$this->methods = $methods;
		$this->requiredParams = $requiredParams;
		$this->processFunction = $processFunction;
	}
	
	public function process() {
		$this->requestHandler = new RequestHandler($this->methods);
		$this->params = $this->requestHandler->getRequestParams();
		if($this->params) {
			$this->validator = new RequiredParamsValidator($this->requiredParams);
			if($this->validator->validate($this->params)){
				$process = $this->processFunction;
				$process();
			} else {
				echo '{erro: '.$this->validator->getErroMsg().'}';
			}
		} else {
			echo '{erro: metodo invalido}';
		}
	}
	
	public function getParams(){
		$this->params;
	}
	
}

?>