<?php

class Text{
    var $value;

    // construtor
    function Text($value){
        $this->value = $value;
    }

    // resume um texto cortando fora os caracteres excedentes, trunca a última palavra
    function Truncate($limit){
        $text = $this->value;

        if(strlen($text)>$limit){
            $text = substr($text, 0, $limit-3);
            $text .= "...";
        }

        return $text;
    }

}

?>
