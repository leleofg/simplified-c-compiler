<?php

namespace Compiler\Parser;

use Compiler\Scanner\Scanner;
use Compiler\Constantes;

class Parser
{
    public $scanner;
    public $file;
    private $scope = 0;
    private $tableSymbols = [];
    private $op = [];

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

            if($id['id'] != Constantes::$PR_INT) {
                throw new \Exception( "ERRO! Programa espera um int. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id['id'] != Constantes::$PR_MAIN) {
                throw new \Exception( "ERRO! Programa espera um main. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id['id'] != Constantes::$ABRE_PARENTESE) {
                throw new \Exception( "ERRO! Programa espera um abre parentese. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id['id'] != Constantes::$FECHA_PARENTESE) {
                throw new \Exception( "ERRO! Programa espera um fecha parentese. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id['id'] != Constantes::$ABRE_CHAVE) {
                throw new \Exception( "ERRO! Programa espera um abre chave. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->block($id);

            if(!empty($id)) {
                throw new \Exception( "ERRO! Código após bloco main. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

        } catch (\Exception $ex) {
            echo $ex->getMessage(); die();
        }
    }

    private function block(array $id)
    {
        $this->scope++;

        if($id['id'] != Constantes::$ABRE_CHAVE) {
            throw new \Exception( "ERRO! Programa espera um abre chave. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $id = $this->scanner->scan($this->file);

        while($id['id'] == Constantes::$PR_INT or $id['id'] == Constantes::$PR_FLOAT or $id['id'] == Constantes::$PR_CHAR) {
            $id = $this->variable($id);
        }

        while($id['id'] == Constantes::$IDENTIFICADOR or $id['id'] == Constantes::$PR_IF or $id['id'] == Constantes::$PR_ELSE or $id['id'] == Constantes::$ABRE_CHAVE or $id['id'] == Constantes::$PR_WHILE or $id['id'] == Constantes::$PR_DO) {
            $id = $this->command($id);
        }

        if($id['id'] != Constantes::$FECHA_CHAVE) {
            throw new \Exception( "ERRO! Programa espera um fecha chave. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $this->deleteTableSymbols();
        $this->scope--;

        return $this->scanner->scan($this->file);
    }

    private function variable(array $id)
    {
        if($id['id'] != Constantes::$PR_INT and $id['id'] != Constantes::$PR_FLOAT and $id['id'] != Constantes::$PR_CHAR) {
            throw new \Exception( "ERRO, na declaração de variável. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $typeVariable = $id['id'];

        $id = $this->scanner->scan($this->file);

        if($id['id'] == Constantes::$IDENTIFICADOR) {

            $this->findInTableSymbols($id['lexeme']);
            array_push($this->tableSymbols, ['id' => $typeVariable, 'lexeme' => $id['lexeme'], 'scope' => $this->scope]);

            $id = $this->scanner->scan($this->file);

            while($id['id'] == Constantes::$VIRGULA) {
                $id = $this->scanner->scan($this->file);

                if($id['id'] != Constantes::$IDENTIFICADOR) {
                    throw new \Exception( "ERRO, na declaração das variáveis. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
                }

                $this->findInTableSymbols($id['lexeme']);
                array_push($this->tableSymbols, ['id' => $typeVariable, 'lexeme' => $id['lexeme'], 'scope' => $this->scope]);
                $id = $this->scanner->scan($this->file);
            }

            if($id['id'] != Constantes::$PONTO_VIRGULA) {
                throw new \Exception( "ERRO, falta ponto e virgula para finalizar a declaração de variável. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            return $this->scanner->scan($this->file);
        }
    }

    private function command(array $id)
    {
        if($id['id'] == Constantes::$IDENTIFICADOR) {
            return $this->assignment($id);
        }

        if($id['id'] == Constantes::$ABRE_CHAVE) {
            return $this->block($id);
        }

        if($id['id'] == Constantes::$PR_DO or $id['id'] == Constantes::$PR_WHILE) {
            return $this->iteration($id);
        }

        if($id['id'] == Constantes::$PR_IF) {
            $id = $this->scanner->scan($this->file);

            if($id['id'] != Constantes::$ABRE_PARENTESE) {
                throw new \Exception( "ERRO! Espera-se um abre parentese. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->relationalExpr();

            if($id['id'] != Constantes::$FECHA_PARENTESE) {
                throw new \Exception( "ERRO! Espera-se um abre parentese. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id['id'] == Constantes::$ABRE_CHAVE or $id['id'] == Constantes::$IDENTIFICADOR or $id['id'] == Constantes::$PR_IF or $id['id'] == Constantes::$PR_ELSE or $id['id'] == Constantes::$PR_WHILE or $id['id'] == Constantes::$PR_DO) {
                return $this->command($id);
            }
        }

        if($id['id'] == Constantes::$PR_ELSE) {
            $id = $this->scanner->scan($this->file);

            if($id['id'] == Constantes::$IDENTIFICADOR or $id['id'] == Constantes::$PR_IF or $id['id'] == Constantes::$PR_ELSE or $id['id'] == Constantes::$ABRE_CHAVE or $id['id'] == Constantes::$PR_WHILE or $id['id'] == Constantes::$PR_DO) {
                return $this->command($id);
            }

            throw new \Exception( "ERRO! comando inválido. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }
    }

    private function assignment(array $id)
    {
        if($id['id'] != Constantes::$IDENTIFICADOR) {
            throw new \Exception( "ERRO, falta IDENTIFICADOR para a atribuição. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $op1 = $this->searchInTableSymbols($id['lexeme']);

        $id = $this->scanner->scan($this->file);

        if($id['id'] != Constantes::$ATRIBUICAO) {
            throw new \Exception( "ERRO, falta atribuição. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $id = $this->aritExpr();

        $aux = end($this->op);
        $op2 = $this->searchInTableSymbols($aux['lexeme']);

        if(($op1 == Constantes::$NUM_INT or $op1 == Constantes::$PR_INT) and ($op2 == Constantes::$NUM_FLOAT || $op2 == Constantes::$PR_FLOAT)) {
            throw new \Exception( "ERRO! Não pode atribuir um float a um int. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $this->op = [];

        if($id['id'] != Constantes::$PONTO_VIRGULA) {
            throw new \Exception( "ERRO, falta um ponto e virgula. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        return $this->scanner->scan($this->file);
    }

    private function iteration(array $id)
    {
        if($id['id'] == Constantes::$PR_DO) {
            $id = $this->scanner->scan($this->file);

            $id = $this->command($id);

            if($id['id'] != Constantes::$PR_WHILE) {
                throw new \Exception( "ERRO, falta while do DO. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id['id'] != Constantes::$ABRE_PARENTESE) {
                throw new \Exception( "ERRO, falta um abre parenteses depois do while. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->relationalExpr();

            if($id['id'] != Constantes::$FECHA_PARENTESE) {
                throw new \Exception( "ERRO, falta um fecha parentese depois de uma expressao relacional. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            $id = $this->scanner->scan($this->file);

            if($id['id'] != Constantes::$PONTO_VIRGULA) {
                throw new \Exception( "ERRO, falta um ponto e vírgula logo após o fecha parentese depois de uma expressao relacional. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            return $this->scanner->scan($this->file);
        }

        $id = $this->scanner->scan($this->file);

        if($id['id'] != Constantes::$ABRE_PARENTESE) {
            throw new \Exception( "ERRO, falta um abre parenteses depois do while. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $id = $this->relationalExpr();

        if($id['id'] != Constantes::$FECHA_PARENTESE) {
            throw new \Exception( "ERRO, falta um fecha parentese depois de uma expressao relacional. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        $id = $this->scanner->scan($this->file);

        if($id['id'] == Constantes::$IDENTIFICADOR or $id['id'] == Constantes::$PR_IF or $id['id'] == Constantes::$PR_ELSE or $id['id'] == Constantes::$ABRE_CHAVE or $id['id'] == Constantes::$PR_WHILE or $id['id'] == Constantes::$PR_DO) {
            return $this->command($id);
        }
    }

    private function relationalExpr()
    {
        $id = $this->aritExpr();

        if($id['id'] == Constantes::$COMPARACAO or $id['id'] == Constantes::$DIFERENTE or $id['id'] == Constantes::$MAIOR or $id['id'] == Constantes::$MENOR or $id['id'] == Constantes::$MAIOR_IGUAL or $id['id'] == Constantes::$MENOR_IGUAL) {

            $id2 = $this->aritExpr();

            if(count($this->op) > 1) {
                $this->semantic($this->op, $id);
            }

            return $id2;
        }

        throw new \Exception( "ERRO! Falta um operador relacional. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
    }

    private function aritExpr()
    {
        $id = $this->term();
        $aux = $this->aritExprAux($id);

        if(is_null($aux)) {
            return $id;
        }

        $this->semantic($this->op, $id);
        return $aux;
    }

    private function aritExprAux($id)
    {
        if($id['id'] != Constantes::$ADICAO and $id['id'] != Constantes::$SUBTRACAO) {
            return null;
        }

        $id = $this->term();
        $aux = $this->aritExprAux($id);

        if(is_null($aux)) {
            return $id;
        }

        $this->semantic($this->op, $id);
        return $aux;
    }

    private function term()
    {
        $id = $this->factor();

        if($id['id'] == Constantes::$ADICAO or $id['id'] == Constantes::$SUBTRACAO) {
            $id = $this->aritExprAux($id);
        }

        $aux = $this->auxTerm($id);

        if(is_null($aux)) {
            return $id;
        }

        $this->semantic($this->op, $id);
        return $aux;
    }

    private function auxTerm($id)
    {
        if($id['id'] != Constantes::$MULTIPLICACAO and $id['id'] != Constantes::$DIVISAO) {
            return null;
        }

        $id = $this->factor();
        $aux = $this->auxTerm($id);

        if(is_null($aux)) {
            return $id;
        }

        $this->semantic($this->op, $id);
        return $aux;
    }

    private function factor()
    {
        $id = $this->scanner->scan($this->file);

        if($id['id'] == Constantes::$ABRE_PARENTESE) {
            $id = $this->aritExpr();

            if($id['id'] == Constantes::$FECHA_PARENTESE) {
                return $this->scanner->scan($this->file);
            }

            throw new \Exception( "ERRO! Falta parentese. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
        }

        array_push($this->op, $id);

        if ($id['id'] == Constantes::$IDENTIFICADOR) {
            $this->searchInTableSymbols($id['lexeme']);
            return $this->scanner->scan($this->file);
        }

        if ($id['id'] == Constantes::$NUM_INT or $id['id'] == Constantes::$NUM_FLOAT or $id['id'] == Constantes::$CHAR) {
            return $this->scanner->scan($this->file);
        }
    }

    private function findInTableSymbols($lexema)
    {
        if(!empty($this->tableSymbols)) {
            foreach($this->tableSymbols as $tb) {
                if($tb['scope'] == $this->scope && $tb['lexeme'] == $lexema) {
                    throw new \Exception( "ERRO! Variável com o mesmo nome no mesmo escopo. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
                }
            }
        }
    }

    private function deleteTableSymbols()
    {
        foreach($this->tableSymbols as $tb) {
            if($tb['scope'] == $this->scope) {
                array_pop($this->tableSymbols);
            }
        }
    }

    private function searchInTableSymbols($lexeme)
    {
        foreach($this->tableSymbols as $tb) {
            if($tb['lexeme'] == $lexeme) {
                return $tb['id'];
            }
        }

        throw new \Exception( "ERRO! Variável não declarada. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
    }

    private function semantic(array $op, $operator)
    {
        if(empty($op)) {
            return;
        }

//        echo $op[0]['lexeme'] . " - " . $op[1]['lexeme'] . "\n";

        if($op[0]['id'] == Constantes::$IDENTIFICADOR) {
            $op1 = $this->searchInTableSymbols($op[0]['lexeme']);
        } else {
            $op1 = $op[0]['id'];
        }

        if($op[1]['id'] == Constantes::$IDENTIFICADOR) {
            $op2 = $this->searchInTableSymbols($op[1]['lexeme']);
        } else {
            $op2 = $op[1]['id'];
        }

//        echo $op1 . " - " . $op2 . "\n";

        if($op1 == Constantes::$NUM_INT or $op1 == Constantes::$PR_INT) {
            if($operator['id'] == Constantes::$DIVISAO && ($op2 == Constantes::$NUM_INT or $op2 == Constantes::$PR_INT)) {
                throw new \Exception("ERRO! Operação entre inteiro com '/' resulta em um float. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            if ($op2 == Constantes::$CHAR or $op2 == Constantes::$PR_CHAR) {
                throw new \Exception("ERRO! Tipos incompatíveis. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }

            if ($op2 == Constantes::$NUM_FLOAT or $op2 == Constantes::$PR_FLOAT) {

            }
        }

        if($op1 == Constantes::$CHAR or $op1 == Constantes::$PR_CHAR) {
            if($op2 != Constantes::$CHAR && $op2 != Constantes::$PR_CHAR) {
                throw new \Exception("ERRO! Tipos incompatíveis. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }
        }

        if($op1 == Constantes::$PR_FLOAT or $op1 == Constantes::$NUM_FLOAT) {
            if($op2 == Constantes::$NUM_INT or $op2 == Constantes::$PR_INT) {

            } else {
                throw new \Exception("ERRO! Tipos incompatíveis. Erro na linha: {$this->scanner->getLine()}, coluna: {$this->scanner->getColumn()}. \n");
            }
        }

        foreach ($this->op as $key => $value) {
            if($value['lexeme'] == $op[0]['lexeme'] or $value['lexeme'] == $op[1]['lexeme']) {
                unset($this->op[$key]);
            }
        }

        $this->op = array_values($this->op);
    }
}