<?php

include_once("ChargeFormula.php");
include_once("PriceByVolume.php");
include_once("ConsumptionMeasure.php");

include_once("../../DataAccessObjects/CounterDAO.php");
include_once("../../DataTransferObjects/CounterDTO.php");
include_once("../../DataAccessObjects/ReadingDAO.php");
include_once("../../DataTransferObjects/ReadingDTO.php");
include_once("../../DataAccessObjects/ContractDAO.php");
include_once("../../DataTransferObjects/ContractDTO.php");
include_once("../../DataAccessObjects/SubContractDAO.php");
include_once("../../DataTransferObjects/SubContractDTO.php");
include_once("../../DataAccessObjects/ContractTypeDAO.php");
include_once("../../DataTransferObjects/ContractTypeDTO.php");
include_once("../../DataAccessObjects/ContractChargeDAO.php");
include_once("../../DataTransferObjects/ContractChargeDTO.php");
include_once("../../DataAccessObjects/ContractBonusDAO.php");
include_once("../../DataTransferObjects/ContractBonusDTO.php");


class BillingEngine {
    var $listaSubContratos  =  "";  // lista de identificadores dos subcontratos separados por vírgula
    var $arrayItens = null;  // array com os items(equipamentos)
    var $contadores;
    var $tiposContrato;
    var $formasCobranca;
    var $precosFaixaConsumo;
    var $consumos;
    var $franquias;
    var $statusEquipamentos = null;
    var $displayAdditionalColumns = false;


    function BillingEngine($dataConnector, $listaSubContratos, $itemArray, $startDate = null, $endDate = null) {
        $this->listaSubContratos = $listaSubContratos;
        $this->arrayItens = $itemArray;
        $this->consumos = array();
        $this->franquias = array();

        // Busca os contadores cadastrados no sistema
        $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
        $counterDAO->showErrors = 1;
        $counterArray = $counterDAO->RetrieveRecordArray();
        $this->contadores = array();
        foreach ($counterArray as $counter) {
            $this->contadores[$counter->id] = $counter->nome;
        }

        // Busca os tipos de contrato cadastrados no sistema
        $contractTypeDAO = new ContractTypeDAO($dataConnector->mysqlConnection);
        $contractTypeDAO->showErrors = 1;
        $contractTypeArray = $contractTypeDAO->RetrieveRecordArray();
        $this->tiposContrato = array();
        foreach ($contractTypeArray as $contractType) {
            $this->tiposContrato[$contractType->id] = $contractType->nome.' ('.$contractType->sigla.')';
        }

        // Busca as formas de cobrança cadastradas nos subcontratos
        $contractChargeDAO = new ContractChargeDAO($dataConnector->mysqlConnection);
        $contractChargeDAO->showErrors = 1;
        $chargeArray = $contractChargeDAO->RetrieveRecordArray("subContrato_id IN (".$listaSubContratos.")");
        $this->formasCobranca = array();
        foreach ($chargeArray as $contractCharge) {
            $key = $contractCharge->codigoSubContrato.'-'.$contractCharge->codigoContador;
            $this->formasCobranca[$key] = new ChargeFormula($contractCharge->modalidadeMedicao, $contractCharge->fixo, $contractCharge->variavel, $contractCharge->franquia, $contractCharge->individual);
            if (!array_key_exists($contractCharge->codigoContador, $this->franquias)) $this->franquias[$contractCharge->codigoContador] = 0;
            $this->franquias[$contractCharge->codigoContador] += $contractCharge->franquia;
        }

        // Busca os preços por faixa de consumo
        $contractBonusDAO = new ContractBonusDAO($dataConnector->mysqlConnection);
        $contractBonusDAO->showErrors = 1;
        $bonusArray = $contractBonusDAO->RetrieveRecordArray("subContrato_id IN (".$listaSubContratos.")");
        $this->precosFaixaConsumo = array();
        foreach ($bonusArray as $bonus) {
            $this->precosFaixaConsumo[$bonus->id] = new PriceByVolume($bonus->codigoSubContrato, $bonus->codigoContador, $bonus->de, $bonus->ate, $bonus->valor);
        }

        // Busca o consumo para os contadores de cada equipamento
        $readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
        $readingDAO->showErrors = 1;
        foreach ($itemArray as $contractItem) {
            $equipamento_id = $contractItem->codigoCartaoEquipamento;
            $keys = array_keys($this->contadores);
            foreach ($keys as $contador_id) {
                $ajusteReset = 0;
                $readingArray = array();
                $arrLen = 0;
                if (($startDate != null) && ($endDate != null)) {
                    // Recupera as leituras no período, desconsiderando resets de contador como leitura final
                    $query = "codigoCartaoEquipamento=".$equipamento_id." AND contador_id=".$contador_id." AND data >= '".$startDate." 00:00' AND data <= '".$endDate." 23:59' AND origemLeitura_id=2 ORDER BY leitura.data DESC";
                    $tempArray = $readingDAO->RetrieveRecordArray($query);
                    $firstReading = null;
                    $lastReading = null;
                    foreach ($tempArray as $dto) {
                        if (($arrLen == 1) && ($dto->reset)) {  // procura a leitura inicial, depois de ter adicionado a final
                            $firstReading = $dto;
                            array_push($readingArray, $dto);
                            $arrLen++;
                        }
                        if (($arrLen == 0) && (!$dto->reset)) {  // procura a leitura final
                            $lastReading = $dto;
                            array_push($readingArray, $dto);
                            $arrLen++;
                        }
                    }
                    if ($lastReading != null) {  // Completa a faixa de leituras
                        $tempLen = sizeof($tempArray);
                        $firstReadingBeforeReset = $tempArray[$tempLen - 1];
                        if (($firstReading != null) && ($firstReading->data != $firstReadingBeforeReset->data)) {
                            $lastReadingBeforeReset = null;
                            foreach (array_reverse($tempArray) as $dto) {
                                if ($dto->reset) break;
                                $lastReadingBeforeReset = $dto;
                            }
                            $ajusteReset = $lastReadingBeforeReset->contagem - $firstReadingBeforeReset->contagem;
                        }
                        if ($firstReading == null) {
                            array_push($readingArray, $firstReadingBeforeReset);
                            $arrLen++;
                        }
                    }
                }
                else {
                    // Recupera as duas últimas leituras, desconsiderando resets de contador como leitura final
                    $query = "codigoCartaoEquipamento=".$equipamento_id." AND contador_id=".$contador_id." AND origemLeitura_id=2 ORDER BY leitura.data DESC LIMIT 0, 10";
                    $tempArray = $readingDAO->RetrieveRecordArray($query);
                    foreach ($tempArray as $dto) {
                        if ($arrLen == 1) {  // procura a leitura inicial, depois de ter adicionado a final
                            array_push($readingArray, $dto);
                            $arrLen++;
                        }
                        if (($arrLen == 0) && (!$dto->reset)) {  // procura a leitura final
                            array_push($readingArray, $dto);
                            $arrLen++;
                        }
                    }
                }

                if ($arrLen == 1) { // caso tenha apenas uma leitura
                    $reading = $readingArray[0];
                    $dataLeitura = strtotime($reading->data);
                    $medicaoInicial = 0;
                    $medicaoFinal = $reading->contagem;

                    $key = $equipamento_id.'-'.$contador_id;
                    $this->consumos[$key] = new ConsumptionMeasure($equipamento_id, $contador_id, $dataLeitura, $medicaoInicial, $medicaoFinal);
                    $this->consumos[$key]->ajusteMedicaoInicial = 0;
                    $this->consumos[$key]->ajusteMedicaoFinal = $reading->ajusteContagem;
                }
                if ($arrLen > 1) { // caso tenha mais de uma leitura (ordenação decrescente)
                    $initialReading = $readingArray[$arrLen - 1];
                    $finalReading = $readingArray[0];
                    $dataLeitura = strtotime($finalReading->data);
                    $medicaoInicial = $initialReading->contagem;
                    $medicaoFinal = $finalReading->contagem;

                    $key = $equipamento_id.'-'.$contador_id;
                    $this->consumos[$key] = new ConsumptionMeasure($equipamento_id, $contador_id, $dataLeitura, $medicaoInicial, $medicaoFinal);
                    $this->consumos[$key]->ajusteMedicaoInicial = $initialReading->ajusteContagem;
                    $this->consumos[$key]->ajusteMedicaoFinal = $finalReading->ajusteContagem;
                }

                if ($arrLen > 0) { // adiciona o total (consumo) caso exista alguma leitura
                    $formaCobranca = $this->GetFormaCobranca($contractItem->codigoSubContrato, $contador_id);
                    if (($formaCobranca != null) && ($formaCobranca->modalidadeMedicao == 2)) // Leitura simples (apenas a final)
                        $this->consumos[$key]->medicaoInicial = 0;

                    // o ajuste na medição inicial foi computado no faturamento anterior
                    $ajuste = $this->consumos[$key]->ajusteMedicaoFinal + $ajusteReset;
                    $this->consumos[$key]->total = ($this->consumos[$key]->medicaoFinal + $ajuste) - $this->consumos[$key]->medicaoInicial;
                    $this->consumos[$key]->ajusteTotal = $ajuste;
                }
            } // foreach de contadores
        } // foreach de itens 

    }

    function GetContadores()
    {
        return $this->contadores;
    }

    function GetContadorAsText($tipoContador)
    {
        if (!array_key_exists($tipoContador, $this->contadores)) return null;
        return $this->contadores[$tipoContador];
    }

    function GetTipoContratoAsText($tipoContrato)
    {
        if (!array_key_exists($tipoContrato, $this->tiposContrato)) return null;
        return $this->tiposContrato[$tipoContrato];
    }

    // Obtem a forma de cobrança cadastrada no subContrato para o tipo de contador informado
    function GetFormaCobranca($subContrato, $tipoContador)
    {
        $key = $subContrato.'-'.$tipoContador;
        if (!array_key_exists($key, $this->formasCobranca)) return null;

        return $this->formasCobranca[$key];
    }

    // Obtem o consumo para o equipamento de acordo com o tipo do contador
    function GetConsumo($cartaoEquipamento, $tipoContador)
    {
        $key = $cartaoEquipamento.'-'.$tipoContador;
        if (!array_key_exists($key, $this->consumos)) return null;

        return $this->consumos[$key];
    }

    // Obtem o consumo de todos os equipamentos somados
    function GetConsumoGlobal($mysqlConnection, $contractId, $tipoContador) {
        $consumoGlobal = 0;

        foreach ($this->consumos as $consumo) {
            // Obtem o contrato a que pertence o consumo
            $subContractId = $this->GetSubContrato($consumo->equipmentCode);
            $contractDTO = $this->GetContract($mysqlConnection, $subContractId);

            // Soma o consumo caso pertença ao mesmo contrato e tipo de contador
            if (($contractDTO->id == $contractId) && ($consumo->codigoContador == $tipoContador)) $consumoGlobal += $consumo->total;
        }

        return $consumoGlobal;
    }

    // Obtem a franquia total para o contador informado ( franquias dos subcontratos somadas )
    function GetFranquiaGlobal($mysqlConnection, $tipoContador) {
        $franquiaGlobal = 0;

        if (array_key_exists($tipoContador, $this->franquias))
            $franquiaGlobal = $this->franquias[$tipoContador];

        return $franquiaGlobal;
    }

    // Obtem o custo por página (custo variável), utiliza o custo default caso não encontre no cadastro de bonus
    function GetCustoVariavel($subContrato, $tipoContador, $consumoMedido, $default)
    {
        foreach ($this->precosFaixaConsumo as $preco) {
            if (($preco->codigoSubContrato == $subContrato) && ($preco->codigoContador == $tipoContador)) {
                if (($consumoMedido > $preco->de) && ($consumoMedido <= $preco->ate)) return $preco->valor;
            }
        }

        return $default;
    }

    // Obtem o subcontrato a que pertence o equipamento
    function GetSubContrato($equipamento_id) {

        foreach ($this->arrayItens as $equipamento) {
            if ($equipamento->codigoCartaoEquipamento == $equipamento_id)
                return $equipamento->codigoSubContrato;
        }

        return null;
    }

    // Retorna um DTO com os dados do contrato
    function GetContract($mysqlConnection, $subContractId) {
        // Cria os objetos de mapeamento objeto-relacional
        $contractDAO = new ContractDAO($mysqlConnection);
        $contractDAO->showErrors = 1;
        $subContractDAO = new SubContractDAO($mysqlConnection);
        $subContractDAO->showErrors = 1;

        // Busca o contrato a que pertence
        $subContract = $subContractDAO->RetrieveRecord($subContractId);
        $contract = $contractDAO->RetrieveRecord($subContract->codigoContrato);

        return $contract;
    }

    // Busca o divisor para calculo da média (no caso de contrato global) ou 1 no caso de faturamento individual
    function GetDivisor($mysqlConnection, $contractDTO, $subContractDTO) {
        // Cria o objeto de mapeamento objeto-relacional
        $contractItemDAO = new ContractItemDAO($mysqlConnection);
        $contractItemDAO->showErrors = 1;

        if ($contractDTO->global) {
            // Busca os itens(equipamentos) pertencentes ao subContrato
            $itemArray = $contractItemDAO->RetrieveRecordArray("subContrato_id = ".$subContractDTO->id);

            // Verifica a quantidade de equipamentos ativos ou emprestados
            $itemCount = 0; 
            foreach ($itemArray as $contractItem) {
                // if (array_key_exists($contractItem->codigoCartaoEquipamento, $this->statusEquipamentos))
                $status = $this->statusEquipamentos[$contractItem->codigoCartaoEquipamento];
                if (($status == 'A') || ($status == 'L')) $itemCount++;
            }

            return $itemCount;
        }

        return 1;  // divisor padrão, caso não seja contrato global
    }

    function EnableExtraColumns() {
        $this->displayAdditionalColumns = true;
    }

    function ExtraColumnsOnTheLeft($equipment, $clientDAO, $contractDTO) {
        if (!$this->displayAdditionalColumns) return '';

        $client = $clientDAO->RetrieveRecord($equipment->customer);
        $extraColumns = '<td>'.$equipment->insID.'</td><td>'.$client->cardName.'</td><td>'.$equipment->itemName.'</td>';
        $extraColumns .= '<td>'.$equipment->manufacturerSN.'</td><td>'.$equipment->internalSN.'</td>';
        $extraColumns .= '<td>'.$equipment->installationDate->format('d/m/Y').'</td>';
        $extraColumns .= '<td>'.$contractDTO->inicioAtendimento.'</td><td>'.$contractDTO->fimAtendimento.'</td>';

        return $extraColumns;
    }

    function ExtraColumnsOnTheRight($inventoryItem, $itemConsumption, $billingRevenue) {
        if (!$this->displayAdditionalColumns) return '';

        $custoAquisicao = 0;
        $custoPagPecas = 0;
        $vidaUtil = 0;
        if ($inventoryItem != null) {
            $custoAquisicao = $inventoryItem->avgPrice;
            $custoPagPecas = $inventoryItem->expenses;
            $vidaUtil = $inventoryItem->durability;
            if (empty($vidaUtil)) $vidaUtil = 1;
        }        
        $custoPagEquip = ($custoAquisicao / $vidaUtil) * 1.4;
        $custoTotal = ($custoPagEquip + $custoPagPecas) * $itemConsumption;

        $custoSobreReceita = "0,0000";
        if ($billingRevenue != 0) $custoSobreReceita = number_format($custoTotal/$billingRevenue, 4, ',', '.');

        $extraColumns = '<td>'.number_format($custoAquisicao, 2, ',', '.').'</td><td>'.$vidaUtil.'</td><td>'.number_format($custoPagEquip, 4, ',', '.').'</td>';
        $extraColumns .= '<td>'.number_format($custoPagPecas, 4, ',', '.').'</td><td>'.number_format($custoTotal, 2, ',', '.').'</td><td>'.$custoSobreReceita.'</td>';

        return $extraColumns;
    }

}

// Monta o cabeçalho com os dados do equipamento  
function GetEquipmentInfo($equipment, $contractType) {
    $equipmentInfo = "";

    // Busca os dados do equipamento
    $modelo = "";
    $codigoModelo = ""; 
    $serie = "";
    $instLocation = "";
    if ($equipment != null) {
        $modelo = $equipment->itemName;
        $codigoModelo = $equipment->itemCode;
        $serie = EquipmentDAO::GetShortDescription($equipment);
        $instLocation = $equipment->instLocation;
    }
    $spacing = '&nbsp;&nbsp;&nbsp;';
    $equipmentInfo = 'Cartão Equipamento: '.$equipment->insID.$spacing.'Modelo: '.$modelo.$spacing.'Série: '.$serie.$spacing.'Departamento: '.$instLocation.$spacing.'Tipo: '.$contractType;

    return $equipmentInfo;
}

// Formata um valor monetário (Pt-Br)
function formatBrCurrency($value, $decimalPlaces){
    return number_format($value, $decimalPlaces, ',', '.');
}

// Formata um valor decimal
function formatDecimal($value, $decimalPlaces){
    if ($value - (int)$value == 0) return $value;
    if ($decimalPlaces == null) return str_replace('.', ',', $value); 

    return number_format($value, $decimalPlaces, ',', '.');
}

?>
