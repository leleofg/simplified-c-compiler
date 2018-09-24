<?php

namespace Compiler\Parser;

use Compiler\Scanner\Scanner;
use Compiler\Constantes;

class Parser
{
    public $scanner;
    public $file;

    public function __construct($file)
    {
        $scanner = new Scanner();
        $this->scanner = $scanner;
        $this->file = $file;
    }

    public function parse()
    {
        try {
            $id = $this->scanner->scan($this->file);

            if($id != Constantes::$PR_INT) {
                throw new \Exception( "ERRO! Programa espera um int. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id != Constantes::$PR_MAIN) {
                throw new \Exception( "ERRO! Programa espera um main. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id != Constantes::$ABRE_PARENTESE) {
                throw new \Exception( "ERRO! Programa espera um abre parentese. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id != Constantes::$FECHA_PARENTESE) {
                throw new \Exception( "ERRO! Programa espera um fecha parentese. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id != Constantes::$ABRE_CHAVE) {
                throw new \Exception( "ERRO! Programa espera um abre chave. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $this->block($id);


        } catch (\Exception $ex) {
            echo $ex->getMessage(); exit;
        }
    }

    private function block(int $id)
    {
        if($id != Constantes::$ABRE_CHAVE) {
            throw new \Exception( "ERRO! Programa espera um abre chave. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $id = $this->scanner->scan($this->file);

        while($id == Constantes::$PR_INT or $id == Constantes::$PR_FLOAT or $id == Constantes::$PR_CHAR) {
            $id = $this->variable($id);
        }

        while($id == Constantes::$IDENTIFICADOR or $id == Constantes::$ABRE_CHAVE or $id == Constantes::$PR_WHILE or $id == Constantes::$PR_DO or $id == Constantes::$PR_IF or $id == Constantes::$PR_ELSE) {
            $this->command($id);
        }

        if($id != Constantes::$FECHA_CHAVE) {
            throw new \Exception( "ERRO! Programa espera um fecha chave. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $this->getId();
    }

    private function variable(int $id): int
    {
        if($id != Constantes::$PR_INT and $id != Constantes::$PR_FLOAT and $id != Constantes::$PR_CHAR) {
            throw new \Exception( "ERRO, na declaração de variável. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $id = $this->scanner->scan($this->file);

        if($id == Constantes::$IDENTIFICADOR) {
            $id = $this->scanner->scan($this->file);

            while($id == Constantes::$VIRGULA) {
                $id = $this->scanner->scan($this->file);

                if($id != Constantes::$IDENTIFICADOR) {
                    throw new \Exception( "ERRO, na declaração das variáveis. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
                }

                $id = $this->scanner->scan($this->file);
            }

            if($id != Constantes::$PONTO_VIRGULA) {
                throw new \Exception( "ERRO, falta ponto e virgula para finalizar a declaração de variável. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            return $this->scanner->scan($this->file);
        }

    }

    private function command(int $id)
    {
        if($id == Constantes::$IDENTIFICADOR) {
            $this->assignment($id);
        } elseif($id == Constantes::$ABRE_CHAVE) {
            $this->block($id);
        } elseif($id == Constantes::$PR_DO or $id == Constantes::$PR_WHILE) {
            $this->iteration($id);
        }
    }

    private function assignment(int $id)
    {
        if($id != Constantes::$IDENTIFICADOR) {
            throw new \Exception( "ERRO, falta IDENTIFICADOR para a atribuição. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $id = $this->scanner->scan($this->file);

        if($id != Constantes::$ATRIBUICAO) {
            throw new \Exception( "ERRO, falta atribuição. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $this->aritExpr();

        $id = $this->scanner->scan($this->file);

        if($id == Constantes::$PONTO_VIRGULA) {
            echo "ponto e virgula"; exit;
        }
    }

    private function aritExpr()
    {
        $this->term();
    }

    private function term()
    {
        $id = $this->factor();

        echo $id; exit;
    }

    private function factor()
    {
        $id = $this->scanner->scan($this->file);

        if($id == Constantes::$ABRE_PARENTESE) {
            $id = $this->scanner->scan($this->file);

            $this->aritExpr();
        } elseif ($id == Constantes::$IDENTIFICADOR) {

        } elseif ($id == Constantes::$NUM_INT or $id == Constantes::$NUM_FLOAT or $id == Constantes::$CHAR) {
            return $this->scanner->scan($this->file);
        }
    }
}