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
        try {
            $char = $this->lookAhead($file);

            while(ord($char) == 32 or ord($char) == 10 or ord($char) == 9) {
                $char = $this->lookAhead($file);
            }
            if(is_numeric($char)) {
                $this->addBuffer($char);
                $char = $this->lookAhead($file);

                while(is_numeric($char)) {
                    $this->addBuffer($char);
                    $char = $this->lookAhead($file);
                }

                if($char != ".") { // se for qualquer coisa diferente de ponto ele retorna
                    return $this->returnLexeme(Constantes::$NUM_INT, $this->buffer);
                }

                if($char == ".") { //se for um ponto, pode ser um float
                    $this->addBuffer($char);
                    $char = $this->lookAhead($file);

                    if(!is_numeric($char)) { //se depois do ponto nao for um número é um float mal formado
                        throw new \Exception("Erro na linha: {$this->line}, coluna: {$this->column}. \n");
                    }

                    while(is_numeric($char)) {
                        $this->addBuffer($char);
                        $char = $this->lookAhead($file);
                    }

                    return $this->returnLexeme(Constantes::$NUM_FLOAT, $this->buffer);
                }

            } elseif($char == ".") {

                $this->addBuffer($char);
                $char = $this->lookAhead($file);

                if(!is_numeric($char)) { //se não for um dígito depois do ponto já pode retornar o erro
                    throw new \Exception("Erro na linha: {$this->line}, coluna: {$this->column}. \n");
                }

                while(is_numeric($char)) {
                    $this->addBuffer($char);
                    $char = $this->lookAhead($file);
                }

                return $this->returnLexeme(Constantes::$NUM_FLOAT, $this->buffer);

            } elseif($char == ")") {

                $this->addBuffer($char);
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$FECHA_PARENSETE, $this->buffer);

            } elseif($char == "(") {

                $this->addBuffer($char);
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$ABRE_PARENTESE, $this->buffer);

            } elseif($char == "{") {

                $this->addBuffer($char);
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$ABRE_CHAVE, $this->buffer);

            } elseif($char == "}") {

                $this->addBuffer($char);
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$FECHA_CHAVE, $this->buffer);

            } elseif($char == ",") {

                $this->addBuffer($char);
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$VIRGULA, $this->buffer);

            } elseif($char == ";") {

                $this->addBuffer($char);
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$PONTO_VIRGULA, $this->buffer);

            } elseif($char == "+") {

                $this->addBuffer($char);
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$ADICAO, $this->buffer);

            } elseif($char == "-") {

                $this->addBuffer($char);
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$SUBTRACAO, $this->buffer);

            } elseif($char == "*") {

                $this->addBuffer($char);
                $this->lookAhead($file);
                return $this->returnLexeme(Constantes::$MULTIPLICACAO, $this->buffer);

            } elseif($char == "/") {
                $char = $this->lookAhead($file);

                if ($char == "/") {
                    while (true) {
                        $char = $this->lookAhead($file);

                        if (ord($char) == 10 or feof($file)) {
                            return $this->scan($file);
                        }
                    }
                } elseif ($char == "*") {
                    while (true) {
                        $char = $this->lookAhead($file);

                        if ($char == "*") {
                            $char = $this->lookAhead($file);

                            if ($char == "/" or feof($file)) {
                                return $this->scan($file);
                            }
                        }
                    }
                } else {
                    return $this->returnLexeme(Constantes::$DIVISAO, "/");
                }

            } elseif($char == "'") {
                $this->addBuffer($char);
                $char = $this->lookAhead($file);

                $this->addBuffer($char);
                $char = $this->lookAhead($file);

                if($char != "'") {
                    throw new \Exception("Erro na linha: {$this->line}, coluna: {$this->column}. Caractere mal formado. \n");
                }

                $this->addBuffer($char);
                $this->lookAhead($file);

                return $this->returnLexeme(Constantes::$CHAR, $this->buffer);

            } elseif($char == "!") {

                $this->addBuffer($char);
                $char = $this->lookAhead($file);

                if ($char == "=") {
                    $this->addBuffer($char);
                    $this->lookAhead($file);
                    return $this->returnLexeme(Constantes::$DIFERENTE, $this->buffer);
                }

                throw new \Exception( "Erro na linha: {$this->line}, coluna: {$this->column}. Erro exclamação sozinha, espera-se um '=' depois dela. \n");

            } elseif($char == ">") {

                $this->addBuffer($char);
                $char = $this->lookAhead($file);

                if ($char == "=") {
                    $this->addBuffer($char);
                    $this->lookAhead($file);
                    return $this->returnLexeme(Constantes::$MAIOR_IGUAL, $this->buffer);
                }

                return $this->returnLexeme(Constantes::$MAIOR, $this->buffer);

            } elseif($char == "<") {

                $this->addBuffer($char);
                $char = $this->lookAhead($file);

                if ($char == "=") {
                    $this->addBuffer($char);
                    $this->lookAhead($file);
                    return $this->returnLexeme(Constantes::$MENOR_IGUAL, $this->buffer);
                }

                return $this->returnLexeme(Constantes::$MENOR, $this->buffer);

            } elseif($char == "=") {

                $this->addBuffer($char);
                $char = $this->lookAhead($file);

                if ($char == "=") {
                    $this->addBuffer($char);
                    $this->lookAhead($file);
                    return $this->returnLexeme(Constantes::$COMPARACAO, $this->buffer);
                }

                return $this->returnLexeme(Constantes::$ATRIBUICAO, $this->buffer);

            } elseif($this->isLetter($char) or $char == "_") {

                $this->addBuffer($char);
                $char = $this->lookAhead($file);

                while ($this->isLetter($char) or is_numeric($char) or $char == "_") {
                    $this->addBuffer($char);
                    $char = $this->lookAhead($file);
                }

                return $this->checkReservedWord($this->buffer);
            }

        } catch (\Exception $ex) {
            echo $ex->getMessage(); exit;
        }
    }
    
    private function addBuffer(string $char)
    {
        array_push($this->buffer, $char);
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

    private function returnLexeme(int $id, $buffer): array
    {
        if(is_array($buffer)) {
            return ['id' => $id, 'lexeme' => implode("", $buffer)];
        }

        return ['id' => $id, 'lexeme' => $buffer];
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