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
        $this->line = 0;
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
                    $this->buffer = [];
                    return Constantes::$NUM_INT;
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

                    return Constantes::$NUM_FLOAT;
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

                return Constantes::$NUM_FLOAT;

            } elseif(self::$reader == "(") {
                $this->count();
                self::$reader = fgetc($file);

                return Constantes::$ABRE_PARENTESE;

            } elseif(self::$reader == ")") {
                $this->count();
                self::$reader = fgetc($file);

                return Constantes::$FECHA_PARENTESE;

            } elseif(self::$reader == "{") {
                $this->count();
                self::$reader = fgetc($file);

                return Constantes::$ABRE_CHAVE;

            } elseif(self::$reader == "}") {
                $this->count();
                self::$reader = fgetc($file);

                return Constantes::$FECHA_CHAVE;

            } elseif(self::$reader == ",") {
                $this->count();
                self::$reader = fgetc($file);

                return Constantes::$VIRGULA;

            } elseif(self::$reader == ";") {
                $this->count();
                self::$reader = fgetc($file);

                return Constantes::$PONTO_VIRGULA;

            } elseif(self::$reader == "+") {
                $this->count();
                self::$reader = fgetc($file);

                return Constantes::$ADICAO;

            } elseif(self::$reader == "-") {
                $this->count();
                self::$reader = fgetc($file);

                return Constantes::$SUBTRACAO;

            } elseif(self::$reader == "*") {
                $this->count();
                self::$reader = fgetc($file);

                return Constantes::$MULTIPLICACAO;

            } elseif(self::$reader == "/") {
                $this->count();
                self::$reader = fgetc($file);

                if (self::$reader == "/") {
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
                    $this->buffer = [];
                    return Constantes::$DIVISAO;
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
                return Constantes::$CHAR;

            } elseif(self::$reader == "!") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                if (self::$reader == "=") {
                    $this->count();
                    $this->buffer = [];
                    self::$reader = fgetc($file);

                    return Constantes::$DIFERENTE;
                }

                throw new \Exception( "Erro na linha: {$this->line}, coluna: {$this->column}. Erro exclamação sozinha, espera-se um '=' depois dela. \n");

            } elseif(self::$reader == ">") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                if (self::$reader == "=") {

                    $this->addBuffer();
                    $this->count();
                    self::$reader = fgetc($file);

                    return Constantes::$MAIOR_IGUAL;
                }

                return Constantes::$MAIOR;

            } elseif(self::$reader == "<") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                if (self::$reader == "=") {

                    $this->addBuffer();
                    $this->count();
                    self::$reader = fgetc($file);

                    return Constantes::$MENOR_IGUAL;
                }

                return Constantes::$MENOR;

            } elseif(self::$reader == "=") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                if (self::$reader == "=") {
                    $this->addBuffer();
                    $this->count();
                    self::$reader = fgetc($file);

                    return Constantes::$COMPARACAO;
                }

                $this->buffer = [];
                return Constantes::$ATRIBUICAO;

            } elseif($this->isLetter(self::$reader) or self::$reader == "_") {

                $this->addBuffer();
                $this->count();
                self::$reader = fgetc($file);

                while ($this->isLetter(self::$reader) or is_numeric(self::$reader) or self::$reader == "_") {
                    $this->addBuffer();
                    $this->count();
                    $check = $this->checkReservedWord($this->buffer, true);

                    if (!empty($check)) {
                        $this->buffer = [];
                        self::$reader = fgetc($file);
                        return $check;
                    }

                    self::$reader = fgetc($file);
                    continue;
                }

                $this->buffer = [];
                return $this->checkReservedWord($this->buffer);
            } elseif (feof($file)) {
                echo 'fim de arquivo'; exit;
            } else {
                echo 'caractere nao reconhecido'; exit;
            }

        } catch (\Exception $ex) {
            echo $ex->getMessage(); exit;
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

    function checkReservedWord(array $buffer, $flag = false)
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