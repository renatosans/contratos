<?php

class EquipmentRevenue{
    var $serialNumber     = "";
    var $modelId          = 0;
    var $modelName        = "";
    var $manufacturerName = "";
    var $revenue          = 0;


    function EquipmentRevenue($serialNumber, $modelId, $modelName, $manufacturerName, $revenue){
        $this->serialNumber     = $serialNumber;
        $this->modelId          = $modelId;
        $this->modelName        = $modelName;
        $this->manufacturerName = $manufacturerName;
        $this->revenue          = $revenue;
    }

}

?>
