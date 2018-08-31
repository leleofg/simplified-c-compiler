<?php

require __DIR__ . '/../vendor/autoload.php';

use Compiler\Scanner\Scanner;

$fileName = $argv[1];

$file = fopen (__DIR__. '/'. $fileName, 'r');

$scanner = new Scanner();
$scanner->scan($file);

echo 'Sem erro';

fclose($file);