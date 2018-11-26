<?php

namespace Compiler\Scanner;

use Compiler\Constantes;

class Scanner
{
    private $line;
    private $column;
    private $buffer;
    private static $reader;

    public function __construct()
    {
        $this->line = 1;
        $this->column = 0;
        $this->buffer = [];
        self::$reader = null;
    }

    public function scan($file)
    {
        try {
            if(is_null(self::$reader)) {
                $this->count();
                self::$reader = fgetc($file);
            }

            while(ord(self::$reader) == 32 or ord(self::$reader) == 10 or ord(self::$reader) == 9) {
                $this->count();
                self::$reader = fgetc($file);
            }

            if(is_numeric(self::$reader)) {
                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                while(is_numeric(self::$reader)) {
                    $this->addBuffer();
                    $this->count();
                    self::$reader = fgetc($file);
                }

                if(self::$reader != ".") {
                    $bufferAux = $this->buffer;
                    $this->buffer = [];
                    return $this->returnLexeme(Constantes::$NUM_INT, $bufferAux);
                }

                if(self::$reader == ".") {
                    $this->addBuffer();
                    $this->count();
                    self::$reader = fgetc($file);

                    if(!is_numeric(self::$reader)) {
                        throw new \Exception("ERRO: FLOAT MAL FORMADO. Erro na linha: {$this->line}, coluna: {$this->column}. \n");
                    }

                    while(is_numeric(self::$reader)) {
                        $this->addBuffer();
                        $this->count();
                        self::$reader = fgetc($file);
                    }

                    $bufferAux = $this->buffer;
                    $this->buffer = [];
                    return $this->returnLexeme(Constantes::$NUM_FLOAT, $bufferAux);
                }

            } elseif(self::$reader == ".") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                if(!is_numeric(self::$reader)) {
                    throw new \Exception("Depois de um ponto, precisa de ser um número para ser um float bem formado. Erro na linha: {$this->line}, coluna: {$this->column}. \n");
                }

                while(is_numeric(self::$reader)) {
                    $this->addBuffer();
                    $this->count();
                    self::$reader = fgetc($file);
                }

                $bufferAux = $this->buffer;
                $this->buffer = [];
                return $this->returnLexeme(Constantes::$NUM_FLOAT, $bufferAux);

            } elseif(self::$reader == "(") {
                $this->count();
                self::$reader = fgetc($file);

                return $this->returnLexeme(Constantes::$ABRE_PARENTESE, $this->buffer);

            } elseif(self::$reader == ")") {
                $this->count();
                self::$reader = fgetc($file);

                return $this->returnLexeme(Constantes::$FECHA_PARENTESE, $this->buffer);

            } elseif(self::$reader == "{") {
                $this->count();
                self::$reader = fgetc($file);

                return $this->returnLexeme(Constantes::$ABRE_CHAVE, $this->buffer);

            } elseif(self::$reader == "}") {
                $this->count();
                self::$reader = fgetc($file);

                return $this->returnLexeme(Constantes::$FECHA_CHAVE, $this->buffer);

            } elseif(self::$reader == ",") {
                $this->count();
                self::$reader = fgetc($file);

                return $this->returnLexeme(Constantes::$VIRGULA, $this->buffer);

            } elseif(self::$reader == ";") {
                $this->count();
                self::$reader = fgetc($file);

                return $this->returnLexeme(Constantes::$PONTO_VIRGULA, $this->buffer);

            } elseif(self::$reader == "+") {
                $this->count();
                self::$reader = fgetc($file);

                return $this->returnLexeme(Constantes::$ADICAO, $this->buffer);

            } elseif(self::$reader == "-") {
                $this->count();
                self::$reader = fgetc($file);

                return $this->returnLexeme(Constantes::$SUBTRACAO, $this->buffer);

            } elseif(self::$reader == "*") {
                $this->count();
                self::$reader = fgetc($file);

                return $this->returnLexeme(Constantes::$MULTIPLICACAO, $this->buffer);

            } elseif(self::$reader == "/") {
                $this->count();
                self::$reader = fgetc($file);

                if (self::$reader == "/") {
                    $this->line++;
                    while (true) {
                        self::$reader = fgetc($file);

                        if (ord(self::$reader) == 10 or feof($file)) {
                            self::$reader = fgetc($file);
                            return $this->scan($file);
                        }
                    }
                } elseif (self::$reader == "*") {
                    while (true) {
                        $this->count();
                        self::$reader = fgetc($file);

                        if (self::$reader == "*") {
                            $this->count();
                            self::$reader = fgetc($file);

                            if (self::$reader == "/" or feof($file)) {
                                self::$reader = fgetc($file);
                                return $this->scan($file);
                            }
                        }
                    }
                } else {
                    $bufferAux = $this->buffer;
                    $this->buffer = [];
                    return $this->returnLexeme(Constantes::$DIVISAO, $bufferAux);
                }

            } elseif(self::$reader == "'") {
                $this->count();
                self::$reader = fgetc($file);

                $this->count();
                self::$reader = fgetc($file);

                if(self::$reader != "'") {
                    throw new \Exception("Erro na linha: {$this->line}, coluna: {$this->column}. Caractere mal formado. \n");
                }

                self::$reader = fgetc($file);
                return $this->returnLexeme(Constantes::$CHAR, $this->buffer);

            } elseif(self::$reader == "!") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                if (self::$reader == "=") {
                    $this->count();
                    $bufferAux = $this->buffer;
                    $this->buffer = [];
                    self::$reader = fgetc($file);

                    return $this->returnLexeme(Constantes::$DIFERENTE, $bufferAux);
                }

                throw new \Exception( "Erro na linha: {$this->line}, coluna: {$this->column}. Erro exclamação sozinha, espera-se um '=' depois dela. \n");

            } elseif(self::$reader == ">") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                if (self::$reader == "=") {
                    $this->count();
                    $bufferAux = $this->buffer;
                    $this->buffer = [];
                    self::$reader = fgetc($file);

                    return $this->returnLexeme(Constantes::$MAIOR_IGUAL, $bufferAux);
                }

                $bufferAux = $this->buffer;
                $this->buffer = [];
                return $this->returnLexeme(Constantes::$MAIOR, $bufferAux);

            } elseif(self::$reader == "<") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                if (self::$reader == "=") {

                    $this->count();
                    $bufferAux = $this->buffer;
                    $this->buffer = [];
                    self::$reader = fgetc($file);

                    return $this->returnLexeme(Constantes::$MENOR_IGUAL, $bufferAux);
                }

                $bufferAux = $this->buffer;
                $this->buffer = [];
                return $this->returnLexeme(Constantes::$MENOR, $bufferAux);

            } elseif(self::$reader == "=") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                if (self::$reader == "=") {

                    $this->count();
                    $bufferAux = $this->buffer;
                    $this->buffer = [];
                    self::$reader = fgetc($file);

                    return $this->returnLexeme(Constantes::$COMPARACAO, $bufferAux);
                }

                $bufferAux = $this->buffer;
                $this->buffer = [];
                return $this->returnLexeme(Constantes::$ATRIBUICAO, $bufferAux);

            } elseif($this->isLetter(self::$reader) or self::$reader == "_") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                while ($this->isLetter(self::$reader) or is_numeric(self::$reader) or self::$reader == "_") {
                    $this->addBuffer();
                    $this->count();
                    $check = $this->checkReservedWord($this->buffer, true);

                    if (!empty($check)) {
                        $bufferAux = $this->buffer;
                        $this->buffer = [];
                        self::$reader = fgetc($file);
//                        if($bufferAux[0] == 'f' && $bufferAux[1] == 'o') {
//                            print_r($bufferAux); exit;
//                        }
                        return $this->returnLexeme($check, $bufferAux);
//                        return $check;
                    }

                    self::$reader = fgetc($file);
                    continue;
                }

                $bufferAux = $this->buffer;
                $this->buffer = [];
                $id = $this->checkReservedWord($this->buffer);
                return $this->returnLexeme($id, $bufferAux);
            } elseif (feof($file)) {
                die();
            } else {
                throw new \Exception( "Erro na linha: {$this->line}, coluna: {$this->column}. Caractere não reconhecido. \n");
            }

        } catch (\Exception $ex) {
            echo $ex->getMessage(); die();
        }
    }

    private function addBuffer()
    {
        array_push($this->buffer, self::$reader);
    }

    private function count(): void
    {
        if (ord(self::$reader) == 32) { //espaço em branco
            $this->column++;
        } elseif (ord(self::$reader) == 10) { //enter
            $this->column = 0;
            $this->line++;
        } elseif (ord(self::$reader) == 9) { //tab
            $this->column = $this->column + 4;
        } else {
            $this->column++;
        }
    }

    private function isLetter(): bool
    {
        if(ord(self::$reader) >= 65 && ord(self::$reader) <= 90 or ord(self::$reader) >= 97 && ord(self::$reader) <= 122) {
            return true;
        }

        return false;
    }

    private function checkReservedWord(array $buffer, $flag = false)
    {
        $lexeme = implode("", $buffer);

        switch ($lexeme) {
            case "main":
                return Constantes::$PR_MAIN;
            case "if":
                return Constantes::$PR_IF;
            case "else":
                return Constantes::$PR_ELSE;
            case "while":
                return Constantes::$PR_WHILE;
            case "do":
                return Constantes::$PR_DO;
            case "for":
                return Constantes::$PR_FOR;
            case "int":
                return Constantes::$PR_INT;
            case "float":
                return Constantes::$PR_FLOAT;
            case "char":
                return Constantes::$PR_CHAR;
            default:
                if($flag) {
                    return [];
                }
                return Constantes::$IDENTIFICADOR;
        }
    }

    private function returnLexeme(int $id, array $buffer): array
    {
        return ['id' => $id, 'lexeme' => implode("", $buffer)];
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }
}