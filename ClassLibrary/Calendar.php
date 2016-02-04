<?php

class Calendar{

    // construtor
    function Calendar() {

    }

    // Obtem o nome do mês a partir de sua representação numérica
    function GetMonthName($month) {
        switch ($month)
        {
            case 1:  return 'Janeiro';
            case 2:  return 'Fevereiro';
            case 3:  return 'Março';
            case 4:  return 'Abril';
            case 5:  return 'Maio';
            case 6:  return 'Junho';
            case 7:  return 'Julho';
            case 8:  return 'Agosto';
            case 9:  return 'Setembro';
            case 10: return 'Outubro';
            case 11: return 'Novembro';
            case 12: return 'Dezembro';
            default: return "Nenhum";
        }
    }

    // Monta as opções de mês para exibir em uma caixa de seleção
    function GetMonthOptions($selected) {
        $options = '';
        $months = array(1=>'Janeiro', 2=>'Fevereiro', 3=>'Março', 4=>'Abril', 5=>'Maio', 6=>'Junho', 7=>'Julho', 8=>'Agosto', 9=>'Setembro', 10=>'Outubro', 11=>'Novembro', 12=>'Dezembro');

        foreach ($months as $index => $name) {
            $attrib = ($selected == $index) ? 'selected' : '' ;
            $options .= '<option value="'.$index.'" '.$attrib.' >'.$name.'</option>';
        }

        return $options;
    }

}

?>
