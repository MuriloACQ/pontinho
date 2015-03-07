<?php
namespace exec;
use classes\StandardRequestProcessor;
require_once 'classes/StandardRequestProcessor.php';

$methods = array('get', 'post');
$requiredParams = array('usuario', 'senha');

$processFunction = function() {
	//codigo aqui
	echo 'processando...';
};

$requestProcessor = new StandardRequestProcessor($methods, $requiredParams, $processFunction);
$requestProcessor->process();

?>