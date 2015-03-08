<?php
namespace scripts;
use classes\LiteRequestProcessor;
use classes\StandardRequestProcessor;
use classes\DatabaseConfig;
use classes\DatabaseQueries;
require_once 'classes/StandardRequestProcessor.php';
require_once 'classes/LiteRequestProcessor.php';
require_once 'classes/DatabaseQueries.php';

$methods = array('get', 'post');
$requiredParams = array('token', 'capacidade', 'fichas', 'timeout');

$processFunction = function(LiteRequestProcessor $liteProcessor) {
	$params = $liteProcessor->getParams();
	$db = new DatabaseQueries($liteProcessor->getMysqlLink());
	return $db->criarJogo($params->get('token'), $params->get('capacidade'),
			$params->get('fichas'), $params->get('timeout'));
};

$requestProcessor = new StandardRequestProcessor($methods, $requiredParams, $processFunction);
$requestProcessor->process();

?>