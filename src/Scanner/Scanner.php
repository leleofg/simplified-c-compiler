<?php

namespace Compiler\Scanner;

use Compiler\Constantes;

class Scanner
{
    private $line;
    private $column;
    private $buffer;

    public function __construct()
    {
        $this->line = 0;
        $this->column = 0;
        $this->buffer = [];
    }

    public function scan($file)
    {
        echo "scan \n";
        try {
            $char = $this->lookAhead($file);

            while(ord($char) == 32 or ord($char == 10) or ord($char) == 9) {
                $char = $this->lookAhead($file);
            }
            if(is_numeric($char)) { // pode ser um inteiro ou um float
                $this->buffer[] = $char;
                $char = $this->lookAhead($file);

                while(is_numeric($char)) {
                    $this->buffer[] = $char;
                    $char = $this->lookAhead($file);
                }

                if($char != ".") { // se for qualquer coisa diferente do ponto ele retorna
                    return $this->returnLexeme(Constantes::$NUM_INT, $this->buffer);
                }

                if($char == ".") { //se for um ponto, pode ser um float
                    $this->buffer[] = $char;
                    $char = $this->lookAhead($file);

                    if(!is_numeric($char)) { //se depois do ponto nao for um número é um float mal formado
                        throw new \Exception("Erro na linha: {$this->line} e na coluna: {$this->column}\n");
                    }

                    while(is_numeric($char)) {
                        $this->buffer[] = $char;
                        $char = $this->lookAhead($file);
                    }

                    return $this->returnLexeme(Constantes::$NUM_FLOAT, $this->buffer);
                }

            } elseif($char == ".") {

                $this->buffer[] = $char;
                $char = $this->lookAhead($file);

                if(!is_numeric($char)) { //se não for um dígito depois do ponto já pode retornar o erro
                    throw new \Exception("Erro na linha: {$this->line} e na coluna: {$this->column}\n");
                }

                while(is_numeric($char)) {
                    $this->buffer[] = $char;
                    $char = $this->lookAhead($file);
                }

                return $this->returnLexeme(Constantes::$NUM_FLOAT, $this->buffer);

            } elseif($lexeme = $this->checkSpecialCharacter($char)) {
                return $lexeme;

            } elseif($this->isLetter($char) or $char == "_") {

                $this->buffer[] = $char;
                $char = $this->lookAhead($file);

                while($this->isLetter($char) or is_numeric($char) or $char == "_") {
                    $this->buffer[] = $char;
                    $char = $this->lookAhead($file);
                }

                return $this->checkReservedWord($this->buffer);
            } elseif($char == "/") {
                $char = $this->lookAhead($file);

                if($char == "/") {
                    while(true) {
                        $char = $this->lookAhead($file);

                        if(ord($char) == 10 or feof($file)) {
                            return $this->scan($file);
                        }
                    }
                }
            } else {
                var_dump($this->line, $this->column);
                var_dump($char);
                echo "não é nada \n"; exit;
            }

        } catch (\Exception $ex) {
            echo $ex->getMessage(); exit;
        }
    }

    private function lookAhead(&$file): string
    {
        $char = fgetc($file);

        if (ord($char) == 32) { //espaço em branco
            $this->column++;
        } elseif (ord($char) == 10) { //enter
            $this->column = 0;
            $this->line++;
        } elseif (ord($char) == 9) { //tab
            $this->column = $this->column + 4;
        } else {
            $this->column++;
        }

        return $char;
    }

    private function returnLexeme(int $id, array $buffer): array
    {
        return ['id' => $id, 'lexeme' => implode("", $buffer)];
    }

    private function checkSpecialCharacter(string $char)
    {
        switch($char) {
            case ")":
                $this->buffer[] = $char;
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$FECHA_PARENSETE, $this->buffer);
            case "(":
                $this->buffer[] = $char;
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$ABRE_PARENTESE, $this->buffer);
            case "{":
                $this->buffer[] = $char;
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$ABRE_CHAVE, $this->buffer);
            case "}":
                $this->buffer[] = $char;
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$FECHA_CHAVE, $this->buffer);
            case ",":
                $this->buffer[] = $char;
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$VIRGULA, $this->buffer);
            case ";":
                $this->buffer[] = $char;
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$PONTO_VIRGULA, $this->buffer);
            default:
                return false;
        }
    }

    private function isLetter($char): bool
    {
        if(ord($char ) >= 65 && ord($char) <= 90 or ord($char) >= 97 && ord($char) <= 122) {
            return true;
        }

        return false;
    }

    function checkReservedWord(array $buffer): array
    {
        $lexeme = implode("", $buffer);

        switch ($lexeme) {
            case "main":
                return $this->returnLexeme(Constantes::$PR_MAIN, $this->buffer);
            case "if":
                return $this->returnLexeme(Constantes::$PR_IF, $this->buffer);
            case "else":
                return $this->returnLexeme(Constantes::$PR_ELSE, $this->buffer);
            case "while":
                return $this->returnLexeme(Constantes::$PR_WHILE, $this->buffer);
            case "do":
                return $this->returnLexeme(Constantes::$PR_DO, $this->buffer);
            case "for":
                return $this->returnLexeme(Constantes::$PR_FOR, $this->buffer);
            case "int":
                return $this->returnLexeme(Constantes::$PR_INT, $this->buffer);
            case "float":
                return $this->returnLexeme(Constantes::$PR_FLOAT, $this->buffer);
            case "char":
                return $this->returnLexeme(Constantes::$PR_CHAR, $this->buffer);
            default:
                return $this->returnLexeme(Constantes::$IDENTIFICADOR, $this->buffer);
        }
    }
}