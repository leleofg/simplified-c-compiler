<?php

namespace Compiler\Scanner;

use Compiler\Util\Util;

class Scanner
{
    private $line;
    private $column;

    public function scanner()
    {
        $file = fopen (__DIR__. '/../codigo.txt', 'r');

        if(!$file) {
            echo "ERROR to open file"; exit;
        }

        $line = 1;
        $column = 1;

        while(!feof($file))
        {
            $char = fgetc($file);

            $blankSpace = Util::isBlankSpace($char);

            if($blankSpace) {
                if($char == ' '){
                    $column++;
                } elseif ($char == '\n'){
                    $column = 0;
                    $line++;
                } else {
                    $column = $column+4;
                }
            } else {
                $dot = Util::isDot($char);
            }

            echo $column;
            echo $line; exit;

        }

        fclose($file);
    }
}