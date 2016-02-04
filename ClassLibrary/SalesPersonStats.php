<?php

class SalesPersonStats{
    var $index          = ""; 
    var $comissionRate  = 0;
    var $revenue        = 0;
    var $contractCount  = 0;
    var $contractArray  = NULL;


    function SalesPersonStats($index) {
        $this->index = $index;
    }

}

?>
