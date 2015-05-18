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
$requiredParams = array('jogoId');

$processFunction = function(LiteRequestProcessor $liteProcessor) {
	$params = $liteProcessor->getParams();
	$db = new DatabaseQueries($liteProcessor->getMysqlLink());
	print_r($db->getJogandoById($params->get('jogoId')));
};

$requestProcessor = new StandardRequestProcessor($methods, $requiredParams, $processFunction);
$requestProcessor->process();

?>