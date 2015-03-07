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
$requiredParams = array('token');

$processFunction = function(LiteRequestProcessor $liteProcessor) {
	$params = $liteProcessor->getParams();
	$db = new DatabaseQueries($liteProcessor->getMysqlLink());
	$db->logout($params->get('token'));
};

$requestProcessor = new StandardRequestProcessor($methods, $requiredParams, $processFunction);
$requestProcessor->process();

?>