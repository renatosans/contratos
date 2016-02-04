<?php

class CustomerRevenue{
    var $cardCode   = "";
    var $revenue    = 0;


    function CustomerRevenue($cardCode, $revenue) {
        $this->cardCode    = $cardCode;
        $this->revenue     = $revenue;
    }

}

?>
