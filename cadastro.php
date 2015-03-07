<?php
use classes\RequestHandler;
use classes\RequiredParamsValidator;
require_once 'classes/RequestHandler.php';
require_once 'classes/RequiredParamsValidator.php';
header('Content-Type: application/json');

$requestHandler = new RequestHandler(array('get', 'post'));
$params = $requestHandler->getRequestParams();
if($params) {
	$validator = new RequiredParamsValidator(array('usuario', 'senha'));
	if($validator->validate($params)){
		processarRequisicao();
	} else {
		echo '{erro: '.$validator->getErroMsg().'}';
	}
} else {
	echo '{erro: metodo invalido}';
}

function processarRequisicao() {
	//Codigo aqui
	echo 'processando cadastro';
}

?>