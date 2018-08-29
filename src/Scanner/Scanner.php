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
        try {
            $file = fopen (__DIR__. '/../codigo.txt', 'r');

            if(!$file) {
                throw new \Exception("ERROR - fail to read file");
            }

            while(!feof($file)) {
                $char = fgetc($file);

                $blankSpace = Util::isBlankSpace($char);

                if ($blankSpace) {
                    if ($char == ' ') {
                        $this->column++;
                        continue;
                    } elseif ($char == '\n') {
                        $this->column = 0;
                        $this->line++;
                        continue;
                    } else {
                        $this->column = $this->column + 4;
                        continue;
                    }
                }

                $dot = Util::isDot($char);

                if ($dot) {
                    echo 'dot';
                }

                if ($char == "(") {
                    array_push($this->lexeme, ['id' => 1, 'token' => $char]);
                    $this->column++;
                    continue;
                }

                if ($char == ")") {
                    array_push($this->lexeme, ['id' => 2, 'token' => $char]);
                    $this->column++;
                    continue;
                }

                if ($char == "{") {
                    array_push($this->lexeme, ['id' => 3, 'token' => $char]);
                    $this->column++;
                    continue;
                }

                if ($char == "}") {
                    array_push($this->lexeme, ['id' => 4, 'token' => $char]);
                    $this->column++;
                    continue;
                }

                if ($char == ";") {
                    array_push($this->lexeme, ['id' => 5, 'token' => $char]);
                    $this->column++;
                    continue;
                }

                if ($char == ",") {
                    array_push($this->lexeme, ['id' => 3, 'token' => $char]);
                    $this->column++;
                    continue;
                }
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }

        echo '<pre>';
        print_r($this->lexeme); exit;

        fclose($file);
    }
}