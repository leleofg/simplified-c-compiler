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
            echo $ex->getMessage(); die();
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

        while($id == Constantes::$IDENTIFICADOR or $id == Constantes::$PR_IF or $id == Constantes::$PR_ELSE or $id == Constantes::$ABRE_CHAVE or $id == Constantes::$PR_WHILE or $id == Constantes::$PR_DO) {
            $id = $this->command($id);
        }

        if($id != Constantes::$FECHA_CHAVE) {
            throw new \Exception( "ERRO! Programa espera um fecha chave. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        return $this->scanner->scan($this->file);
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
            return $this->assignment($id);
        }

        if($id == Constantes::$ABRE_CHAVE) {
            return $this->block($id);
        }

        if($id == Constantes::$PR_DO or $id == Constantes::$PR_WHILE) {
            return $this->iteration($id);
        }

        if($id == Constantes::$PR_IF) {
            $id = $this->scanner->scan($this->file);

            if($id != Constantes::$ABRE_PARENTESE) {
                throw new \Exception( "ERRO! Espera-se um abre parentese. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->relationalExpr();

            if($id != Constantes::$FECHA_PARENTESE) {
                throw new \Exception( "ERRO! Espera-se um abre parentese. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id == Constantes::$IDENTIFICADOR or $id == Constantes::$PR_IF or $id == Constantes::$PR_ELSE or $id == Constantes::$ABRE_CHAVE or $id == Constantes::$PR_WHILE or $id == Constantes::$PR_DO) {
                $id = $this->command($id);
            }
        }

        if($id == Constantes::$PR_ELSE) {
            $id = $this->scanner->scan($this->file);

            if($id == Constantes::$IDENTIFICADOR or $id == Constantes::$PR_IF or $id == Constantes::$PR_ELSE or $id == Constantes::$ABRE_CHAVE or $id == Constantes::$PR_WHILE or $id == Constantes::$PR_DO) {
                return $this->command($id);
            }

            throw new \Exception( "ERRO! comando inválido. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
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

        $id = $this->aritExpr();

        if($id == Constantes::$PONTO_VIRGULA) {
            return $this->scanner->scan($this->file);
        }
    }

    private function iteration(int $id)
    {
        if($id == Constantes::$PR_DO) {
            $id = $this->scanner->scan($this->file);

            $id = $this->command($id);

            if($id != Constantes::$PR_WHILE) {
                throw new \Exception( "ERRO, falta while do DO. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id != Constantes::$ABRE_PARENTESE) {
                throw new \Exception( "ERRO, falta um abre parenteses depois do while. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->relationalExpr();

            if($id != Constantes::$FECHA_PARENTESE) {
                throw new \Exception( "ERRO, falta um fecha parentese depois de uma expressao relacional. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id != Constantes::$PONTO_VIRGULA) {
                throw new \Exception( "ERRO, falta um ponto e vírgula logo após o fecha parentese depois de uma expressao relacional. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            return $this->scanner->scan($this->file);
        }

        $id = $this->scanner->scan($this->file);

        if($id != Constantes::$ABRE_PARENTESE) {
            throw new \Exception( "ERRO, falta um abre parenteses depois do while. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $id = $this->relationalExpr();

        if($id != Constantes::$FECHA_PARENTESE) {
            throw new \Exception( "ERRO, falta um fecha parentese depois de uma expressao relacional. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $id = $this->scanner->scan($this->file);

        if($id == Constantes::$IDENTIFICADOR or $id == Constantes::$PR_IF or $id == Constantes::$PR_ELSE or $id == Constantes::$ABRE_CHAVE or $id == Constantes::$PR_WHILE or $id == Constantes::$PR_DO) {
            return $this->command($id);
        }
    }

    private function relationalExpr()
    {
        $id = $this->aritExpr();

        if($id == Constantes::$COMPARACAO or $id == Constantes::$DIFERENTE or $id == Constantes::$MAIOR or $id == Constantes::$MENOR or $id == Constantes::$MAIOR_IGUAL or $id == Constantes::$MENOR_IGUAL) {
            return $this->aritExpr();
        }
    }

    private function aritExpr()
    {
        $id = $this->term();
        $aux = $this->aritExprAux($id);

        if(is_null($aux)) {
            return $id;
        }

        return $aux;
    }

    private function aritExprAux($id)
    {
        if($id != Constantes::$ADICAO and $id != Constantes::$SUBTRACAO) {
            return null;
        }

        $id = $this->term();
        $aux = $this->aritExprAux($id);

        if(is_null($aux)) {
            return $id;
        }

        return $aux;
    }

    private function term()
    {
        $id = $this->factor();

        if($id == Constantes::$ADICAO or $id == Constantes::$SUBTRACAO) {
            $id = $this->aritExprAux($id);
        }

        $aux = $this->auxTerm($id);

        if(is_null($aux)) {
            return $id;
        }

        return $aux;
    }

    private function auxTerm($id)
    {
        if($id != Constantes::$MULTIPLICACAO and $id != Constantes::$DIVISAO) {
            return null;
        }

        $id = $this->factor();
        $aux = $this->auxTerm($id);

        if(is_null($aux)) {
            return $id;
        }

        return $aux;
    }

    private function factor()
    {
        $id = $this->scanner->scan($this->file);

        if($id == Constantes::$ABRE_PARENTESE) {
            $this->aritExpr();
            return $this->scanner->scan($this->file);
        }

        if ($id == Constantes::$IDENTIFICADOR or $id == Constantes::$NUM_INT or $id == Constantes::$NUM_FLOAT or $id == Constantes::$CHAR) {
            return $this->scanner->scan($this->file);
        }
    }
}