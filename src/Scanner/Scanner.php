<?php

namespace Compiler\Scanner;

use Constantes;

class Scanner
{
    private $line = 0;
    private $column = 0;

    public function scan($file)
    {
        try {

            $char = $this->lookAhead($file);

            while($this->isBlankSpace($char)) {
                $char = $this->lookAhead($file);
            }
            if(is_numeric($char)){
                $char = $this->lookAhead($file);

                while(is_numeric($char)) {
                    $char = $this->lookAhead($file);
                }
                if($char == '.') {
                    echo 'ponto dps de um numero: ';
                    var_dump($char); exit;
                }

            } else {
                var_dump($this->column);
                var_dump($this->line);
                var_dump($char); exit;
            }

        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function lookAhead(&$file)
    {
        $char = fgetc($file);

        if ($char == ' ') {
            $this->column++;
            return $char;
        } elseif ($char == '\n') {
            $this->column = 0;
            $this->line++;
            return $char;
        } elseif ($char == '\t') {
            $this->column = $this->column + 4;
            return $char;
        } else {
            $this->column++;
            return $char;
        }
    }

    private function isBlankSpace(string $char): bool
    {
        if($char == ' ' || $char == '\n' || $char == '\t') {
            return true;
        }

        return false;
    }
}