<?php

require __DIR__ . '/../vendor/autoload.php';

use Compiler\Scanner\Scanner;

$scanner = new Scanner();
$scanner->scan();