<?php
namespace classes;
require_once 'classes/RequestHandler.php';
require_once 'classes/RequiredParamsValidator.php';
require_once 'classes/DatabaseConfig.php';
require_once 'classes/LiteRequestProcessor.php';
header('Content-Type: application/json');

class StandardRequestProcessor {
	
	private $requestHandler;
	private $params;
	private $validator;
	private $methods;
	private $requiredParams;
	private $processFunction;
	private $mysqlLink;
	
	public function __construct($methods, $requiredParams, \Closure $processFunction) {
		$this->methods = $methods;
		$this->requiredParams = $requiredParams;
		$this->processFunction = $processFunction;
		$this->mysqlLink = mysql_connect(DatabaseConfig::host, DatabaseConfig::user, DatabaseConfig::pass);
		if (!$this->mysqlLink) {
			die(mysql_error());
		}
	}
	
	public function process() {
		$this->requestHandler = new RequestHandler($this->methods);
		$this->params = $this->requestHandler->getRequestParams();
		if($this->params) {
			$this->validator = new RequiredParamsValidator($this->requiredParams);
			if($this->validator->validate($this->params)){
				$process = $this->processFunction;
				mysql_query('BEGIN', $this->mysqlLink);
				$result = $process(new LiteRequestProcessor($this));
				if($result) {
					mysql_query('COMMIT', $this->mysqlLink);
				} else {
					mysql_query('ROLLBACK', $this->mysqlLink);
				}
				mysql_close($this->mysqlLink);
			} else {
				echo '{erro: '.$this->validator->getErroMsg().'}';
			}
		} else {
			echo '{erro: metodo invalido}';
		}
	}
	
	public function getParams(){
		return $this->params;
	}
	
	public function getMysqlLink() {
		return $this->mysqlLink;
	}
	
}

?>