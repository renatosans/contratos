using System;
using System.Collections.Generic;
using DataTransferObjects;
using MySql.Data.MySqlClient;


namespace DataAccessObjects
{
    public class ContractItemDAO: DataAccessBase
    {
        public ContractItemDAO(MySqlConnection mySqlConnection)
        {
            this.mySqlConnection = mySqlConnection;
        }

        public List<ContractItemDTO> GetItems(String filter)
        {
            List<ContractItemDTO> itemList = new List<ContractItemDTO>();

            String query = "SELECT * FROM `addoncontratos`.`itens` WHERE " + filter;
            if (String.IsNullOrEmpty(filter)) query = "SELECT * FROM `addoncontratos`.`itens`";

            MySqlCommand command = new MySqlCommand(query, this.mySqlConnection);
            MySqlDataReader dataReader = command.ExecuteReader();
            while (dataReader.Read())
            {
                ContractItemDTO contractItem = new ContractItemDTO();
                contractItem.codigoCartaoEquipamento = (int)dataReader["codigoCartaoEquipamento"];
                contractItem.businessPartnerCode = (String)dataReader["businessPartnerCode"];
                contractItem.contrato_id = (int)dataReader["contrato_id"];
                contractItem.subContrato_id = (int)dataReader["subContrato_id"];

                itemList.Add(contractItem);
            }
            dataReader.Close();

            return itemList;
        }

        public void SetContractItem(ContractItemDTO contractItem)
        {
            String commandText = "UPDATE `addoncontratos`.`itens` SET " + 
                                 "businessPartnerCode='" + contractItem.businessPartnerCode + "', " +
                                 "contrato_id=" + contractItem.contrato_id + ", " +
                                 "subContrato_id=" + contractItem.subContrato_id +
                                 " WHERE codigoCartaoEquipamento = " + contractItem.codigoCartaoEquipamento;
            MySqlCommand command = new MySqlCommand(commandText, this.mySqlConnection);
            command.ExecuteNonQuery();
        }
    }

}
