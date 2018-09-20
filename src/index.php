<?php

require __DIR__ . '/../vendor/autoload.php';

use Compiler\Scanner\Scanner;

if(!isset($argv[1])) {
    echo "Você precisa informar o nome do arquivo a ser lido: 'codigo.txt' \n";
    die();
}

$fileName = $argv[1];

$caminho = __DIR__. '/'. $fileName;

if (!file_exists($caminho)) {
    echo "O arquivo '{$fileName}' não existe \n";
    die();
}

$file = fopen ($caminho, 'r');

$scanner = new Scanner();
$scan = $scanner->scan($file);

//chamar o parser aqui

fclose($file);