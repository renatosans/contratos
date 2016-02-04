<?php

class CommissionRule{
    var $segmento           = 0;
    var $dataAssinaturaDe   = NULL;
    var $dataAssinaturaAte  = NULL;
    var $comissao           = 0;

    function CommissionRule($segmento, $dataAssinaturaDe, $dataAssinaturaAte, $comissao) {
        $this->segmento           = $segmento;
        $this->dataAssinaturaDe   = $dataAssinaturaDe;
        $this->dataAssinaturaAte  = $dataAssinaturaAte;
        $this->comissao           = $comissao;
    }

}

?>
