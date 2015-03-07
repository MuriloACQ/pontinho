<?php
namespace classes;
require_once 'classes/StandardRequestProcessor.php';

class LiteRequestProcessor {
	
	private $params;
	private $mysqlLink;
	
	public function __construct(StandardRequestProcessor $stdReqProcessor) {
		$this->params = $stdReqProcessor->getParams();
		$this->mysqlLink = $stdReqProcessor->getMysqlLink();
	}
	
	public function getParams() {
		return $this->params;
	}
	
	public function getMysqlLink() {
		return $this->mysqlLink;
	}
}
?>