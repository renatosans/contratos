<?php

class EquipmentModelStats{
    var $id           = 0;
    var $modelo       = "";
    var $fabricante   = "";
    var $revenue          = 0;
    var $equipmentCount   = 0;
    var $serviceCallCount = 0;
    var $tempoTotalAtendimento = 0;


    function EquipmentModelStats($id, $modelo, $fabricante) {
        $this->id          = $id;
        $this->model       = $modelo;
        $this->fabricante  = $fabricante;
    }

}

?>
