<?php

class EquipmentOperator{
    var $businessPartnerCode = "";
    var $businessPartnerName = "";
    var $operatorName        = "";
    var $equipmentCode       = 0;
    var $serialNumber        = "";
    var $equipmentStatus     = "";
    var $telephoneNumber     = "";


    function EquipmentOperator($sqlserverConnection, $businessPartnerCode, $equipmentCode){

        // Cria os objetos de mapeamento objeto relacional
        $businessPartnerDAO = new BusinessPartnerDAO($sqlserverConnection);
        $businessPartnerDAO->showErrors = 1;
        $equipmentDAO = new EquipmentDAO($sqlserverConnection);
        $equipmentDAO->showErrors = 1;
        $contactPersonDAO = new ContactPersonDAO($sqlserverConnection);
        $contactPersonDAO->showErrors = 1;

        // Recupera os dados do parceiro de negócios
        $businessPartner = $businessPartnerDAO->RetrieveRecord($businessPartnerCode);
        if ($businessPartner != null) {
            $this->businessPartnerCode = $businessPartner->cardCode;
            $this->businessPartnerName = $businessPartner->cardName;
            $this->telephoneNumber = $businessPartner->telephoneNumber;
        }

        // Recupera os dados do equipamento
        $equipment = $equipmentDAO->RetrieveRecord($equipmentCode);
        if ($equipment != null) {
            $contactPerson = null;
            if (!empty($equipment->contactPerson)) $contactPerson = $contactPersonDAO->RetrieveRecord($equipment->contactPerson);
            if ($contactPerson != null) {
                $contactTel = trim($contactPerson->phoneNumber);
                if (!empty($contactTel)) {
                    $this->operatorName    = $contactPerson->name;
                    $this->telephoneNumber = $contactPerson->phoneNumber;
                }
            }
            $this->equipmentCode = $equipment->insID;
            $this->serialNumber = $equipment->manufacturerSN;
            $this->equipmentStatus = $equipment->status;
        }
    }

}

?>
