<?php

#ifndef KPHP
require_once __DIR__.'/../../../vendor/autoload.php';
#endif

require_once __DIR__.'/../FfiExampleClass.php';

$instance= new \Sigmalab\ExampleFFI\FfiExampleClass();
$instance->processWithCallback("some data", function($sender, $payload){
	echo "received: $payload\n";
});