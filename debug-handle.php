<?php
require 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();



