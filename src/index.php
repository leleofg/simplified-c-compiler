<?php

require __DIR__ . '/../vendor/autoload.php';

use Compiler\Scanner\Scanner;

$fileName = $argv[1];

$file = fopen (__DIR__. '/'. $fileName, 'r');

$scanner = new Scanner();
$scan = $scanner->scan($file);

var_dump($scan);
echo "OK =) \n";

//chamar o parser aqui

fclose($file);