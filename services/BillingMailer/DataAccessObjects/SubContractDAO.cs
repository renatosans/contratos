using System;
using System.Collections.Generic;
using DataTransferObjects;
using MySql.Data.MySqlClient;


namespace DataAccessObjects
{
    public class SubContractDAO: DataAccessBase
    {
        public SubContractDAO(MySqlConnection mySqlConnection)
        {
            this.mySqlConnection = mySqlConnection;
        }

        public SubContractDTO GetSubContract(int id)
        {
            SubContractDTO subContract = null;

            String query = "SELECT SUBC.*, TIPC.sigla as tipoContrato FROM `addoncontratos`.`subContrato` SUBC " +
                           "JOIN `addoncontratos`.`tipoContrato` TIPC ON SUBC.tipocontrato_id = TIPC.id WHERE SUBC.id = " + id;
            MySqlCommand command = new MySqlCommand(query, this.mySqlConnection);
            MySqlDataReader dataReader = command.ExecuteReader();
            if (dataReader.Read())
            {
                subContract = new SubContractDTO();
                subContract.id = (int)dataReader["id"];
                subContract.contrato_id = (int)dataReader["contrato_id"];
                subContract.tipoContrato_id = (int)dataReader["tipoContrato_id"];
                subContract.siglaTipo = (String)dataReader["tipoContrato"];
            }
            dataReader.Close();

            return subContract;
        }

        public List<SubContractDTO> GetSubContracts(String filter)
        {
            List<SubContractDTO> subContractList = new List<SubContractDTO>();

            if (!String.IsNullOrEmpty(filter)) filter = " WHERE " + filter;
            String query = "SELECT SUBC.*, TIPC.sigla as tipoContrato FROM `addoncontratos`.`subContrato` SUBC " +
                           "JOIN `addoncontratos`.`tipoContrato` TIPC ON SUBC.tipocontrato_id = TIPC.id " + filter + ";";
            MySqlCommand command = new MySqlCommand(query, this.mySqlConnection);
            MySqlDataReader dataReader = command.ExecuteReader();
            while (dataReader.Read())
            {
                SubContractDTO subContract = new SubContractDTO();
                subContract.id = (int)dataReader["id"];
                subContract.contrato_id = (int)dataReader["contrato_id"];
                subContract.tipoContrato_id = (int)dataReader["tipoContrato_id"];
                subContract.siglaTipo = (String)dataReader["tipoContrato"];

                subContractList.Add(subContract);
            }
            dataReader.Close();

            return subContractList;
        }
    }

}
