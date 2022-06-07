<?php

class EquipmentDTO{
    public $insID               = 0;
    public $manufacturerSN      = "";
    public $internalSN          = "";
    public $itemCode            = "";
    public $itemName            = "";
    public $customer            = "";
    public $custmrName          = "";
    public $contactPerson       = 0;
    public $addressType         = "";
    public $street              = "";
    public $streetNo            = "";
    public $building            = "";
    public $zip                 = "";
    public $block               = ""; 
    public $city                = "";
    public $state               = "";
    public $country             = "";
    public $instLocation        = "";
    public $status              = "";
    public $installationDate    = NULL;
    public $installationDocNum  = 0;
    public $counterInitialVal   = 0;
    public $removalDate         = NULL;
    public $removalDocNum       = 0;
    public $counterFinalVal     = 0;
    public $technician          = 0;
    public $model               = 0;
    public $capacity            = 0;
    public $sla                 = 0;
    public $comments            = "";
    public $salesPerson         = 0;


    function __construct(){

    }

}

?>
