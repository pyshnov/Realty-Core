<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

use Pyshnov\Core\PyshnovKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

// dev
// test
// prod

$kernel = new PyshnovKernel($autoloader, 'dev', true);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);