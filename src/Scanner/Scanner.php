<?php

namespace Compiler\Scanner;

use Compiler\Util\Util;

class Scanner
{
    private $line;
    private $column;
    private $lexeme;

    public function __construct()
    {
        $this->line = 1;
        $this->column = 1;
        $this->lexeme = [];
    }

    public function scan()
    {
        $file = fopen (__DIR__. '/../codigo.txt', 'r');

        if(!$file) {
            echo "ERROR to open file"; exit;
        }

        while(!feof($file))
        {
            $char = fgetc($file);

            $blankSpace = Util::isBlankSpace($char);

            if($blankSpace) {
                if($char == ' '){
                    $this->column++;
                    continue;
                } elseif ($char == '\n'){
                    $this->column = 0;
                    $this->line++;
                    continue;
                } else {
                    $this->column = $this->column+4;
                    continue;
                }
            }

            $dot = Util::isDot($char);

            if($dot) {
                echo 'dot';
            }

            if($char == "(") {
                array_push($this->lexeme, ['type' => 1, 'token' => $char]);
                continue;
            }

            if($char == ")") {
                array_push($this->lexeme, ['type' => 2, 'token' => $char]);
                continue;
            }

            if($char == "{") {
                array_push($this->lexeme, ['type' => 3, 'token' => $char]);
                continue;
            }

            if($char == "}") {
                array_push($this->lexeme, ['type' => 4, 'token' => $char]);
                continue;
            }

            if($char == ";") {
                array_push($this->lexeme, ['type' => 5, 'token' => $char]);
                continue;
            }

            if($char == ",") {
                array_push($this->lexeme, ['type' => 3, 'token' => $char]);
                continue;
            }
        }

        echo '<pre>';
        print_r($this->lexeme); exit;

        fclose($file);
    }
}