using System;
using System.Collections.Generic;
using DataTransferObjects;
using MySql.Data.MySqlClient;


namespace DataAccessObjects
{
    public class ContractDAO: DataAccessBase
    {
        public ContractDAO(MySqlConnection mySqlConnection)
        {
            this.mySqlConnection = mySqlConnection;
        }

        public static String GetStatusAsText(int status)
        {
            switch (status)
            {
                case 1: return "Pendente";
                case 2: return "Vigente";
                case 3: return "Finalizado";
                case 4: return "Cancelado";
                case 5: return "Renovado";
                case 6: return "Reajustado";
                default: return "Pendente";
            }
        }

        public ContractDTO GetContract(int id)
        {
            ContractDTO contract = null;

            String query = "SELECT * FROM `addoncontratos`.`contrato` WHERE id = " + id;
            MySqlCommand command = new MySqlCommand(query, this.mySqlConnection);
            MySqlDataReader dataReader = command.ExecuteReader();
            if (dataReader.Read())
            {
                contract = new ContractDTO();
                contract.id = (int)dataReader["id"];
                contract.numero = (String)dataReader["numero"];
                contract.pn = (String)dataReader["pn"];
                contract.divisao = (String)dataReader["divisao"];
                contract.contato = (int)dataReader["contato"];
                contract.status = (int)dataReader["status"];
                contract.assinatura = (DateTime)dataReader["assinatura"];
                contract.encerramento = (DateTime)dataReader["encerramento"];
                contract.inicioAtendimento = (DateTime)dataReader["inicioAtendimento"];
                contract.fimAtendimento = (DateTime)dataReader["fimAtendimento"];
                contract.primeiraParcela = (DateTime)dataReader["primeiraParcela"];
                contract.parcelaAtual = (int)dataReader["parcelaAtual"];
                contract.mesReferencia = (int)dataReader["mesReferencia"];
                contract.anoReferencia = (int)dataReader["anoReferencia"];
                contract.quantidadeParcelas = (int)dataReader["quantidadeParcelas"];
                contract.global = (Boolean)dataReader["global"];
                contract.vendedor = (int)dataReader["vendedor"];
                contract.diaVencimento = (int)dataReader["diaVencimento"];
                contract.referencialVencimento = (int)dataReader["referencialVencimento"];
                contract.diaLeitura = (int)dataReader["diaLeitura"];
                contract.referencialLeitura = (int)dataReader["referencialLeitura"];
                contract.indiceReajuste_id = (int)dataReader["indicesReajuste_id"];
                contract.dataRenovacao = GetDateTimeValue(dataReader, "dataRenovacao");
                contract.dataReajuste = GetDateTimeValue(dataReader, "dataReajuste");
                contract.obs = (String)dataReader["obs"];
            }
            dataReader.Close();

            return contract;
        }

        public List<ContractDTO> GetAllContracts(String filter)
        {
            List<ContractDTO> contractList = new List<ContractDTO>();

            String query = "SELECT * FROM `addoncontratos`.`contrato`";
            if (!String.IsNullOrEmpty(filter)) query = "SELECT * FROM `addoncontratos`.`contrato` WHERE " + filter;
            MySqlCommand command = new MySqlCommand(query, this.mySqlConnection);
            MySqlDataReader dataReader = command.ExecuteReader();
            while (dataReader.Read())
            {
                ContractDTO contract = new ContractDTO();
                contract.id = (int)dataReader["id"];
                contract.numero = (String)dataReader["numero"];
                contract.pn = (String)dataReader["pn"];
                contract.divisao = (String)dataReader["divisao"];
                contract.contato = (int)dataReader["contato"];
                contract.status = (int)dataReader["status"];
                contract.assinatura = (DateTime)dataReader["assinatura"];
                contract.encerramento = (DateTime)dataReader["encerramento"];
                contract.inicioAtendimento = (DateTime)dataReader["inicioAtendimento"];
                contract.fimAtendimento = (DateTime)dataReader["fimAtendimento"];
                contract.primeiraParcela = (DateTime)dataReader["primeiraParcela"];
                contract.parcelaAtual = (int)dataReader["parcelaAtual"];
                contract.mesReferencia = (int)dataReader["mesReferencia"];
                contract.anoReferencia = (int)dataReader["anoReferencia"];
                contract.quantidadeParcelas = (int)dataReader["quantidadeParcelas"];
                contract.global = (Boolean)dataReader["global"];
                contract.vendedor = (int)dataReader["vendedor"];
                contract.diaVencimento = (int)dataReader["diaVencimento"];
                contract.referencialVencimento = (int)dataReader["referencialVencimento"];
                contract.diaLeitura = (int)dataReader["diaLeitura"];
                contract.referencialLeitura = (int)dataReader["referencialLeitura"];
                contract.indiceReajuste_id = (int)dataReader["indicesReajuste_id"];
                contract.dataRenovacao = GetDateTimeValue(dataReader, "dataRenovacao");
                contract.dataReajuste = GetDateTimeValue(dataReader, "dataReajuste");
                contract.obs = (String)dataReader["obs"];

                contractList.Add(contract);
            }
            dataReader.Close();

            return contractList;
        }

        public void SetContractParcell(int contractId, int currentParcell)
        {
            String commandText = "UPDATE `addoncontratos`.`contrato` SET parcelaAtual=@param1 WHERE id =" + contractId;
            MySqlParameter param1 = new MySqlParameter("@param1", MySqlDbType.Int32);
            param1.Value = currentParcell;
            MySqlCommand command = new MySqlCommand(commandText, this.mySqlConnection);
            command.Parameters.Add(param1);
            command.ExecuteNonQuery();
        }

        public void SetContractMonthYear(int contractId, int mesReferencia, int anoReferencia)
        {
            String commandText = "UPDATE `addoncontratos`.`contrato` SET mesReferencia=@param1, anoReferencia=@param2 WHERE id =" + contractId;
            MySqlParameter param1 = new MySqlParameter("@param1", MySqlDbType.Int32);
            param1.Value = mesReferencia;
            MySqlParameter param2 = new MySqlParameter("@param2", MySqlDbType.Int32);
            param2.Value = anoReferencia;
            MySqlCommand command = new MySqlCommand(commandText, this.mySqlConnection);
            command.Parameters.Add(param1);
            command.Parameters.Add(param2);
            command.ExecuteNonQuery();
        }
    }

}
