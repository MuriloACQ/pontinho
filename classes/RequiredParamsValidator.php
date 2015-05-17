<?php
namespace classes;

class RequiredParamsValidator {
	private $parametros;
	private $erroMsg;
	
	public function __construct($params){
		if(!is_array($params))
			$params = array($params);
		$this->parametros = $params;
	}
	
	public function validate(RequestParams $params) {
		$this->erroMsg = '';
		foreach ($this->parametros as $parametro) {
			if(!$params->get($parametro) && $params->get($parametro) !== '0'){
				$this->erroMsg .= "$parametro e obrigatorio & ";
			}
		}
		$this->erroMsg .= $this->erroMsg ? ';' : '';
		$this->erroMsg = str_replace(' & ;', '', $this->erroMsg);
		return !$this->erroMsg;
	}
	
	public function getErroMsg() {
		return $this->erroMsg;
	}
	
}

?>