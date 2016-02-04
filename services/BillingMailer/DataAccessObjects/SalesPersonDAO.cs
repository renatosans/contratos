using System;
using System.Data.SqlClient;
using System.Collections.Generic;
using DataTransferObjects;


namespace DataAccessObjects
{
    public class SalesPersonDAO: DataAccessBase
    {
        public SalesPersonDAO(SqlConnection sqlServerConnection)
        {
            this.sqlServerConnection = sqlServerConnection;
        }

        public List<SalesPersonDTO> GetAllSalesperson()
        {
            List<SalesPersonDTO> salespersonList = new List<SalesPersonDTO>();

            String query = "SELECT slpCode, slpName FROM OSLP";
            SqlCommand command = new SqlCommand(query, sqlServerConnection);
            SqlDataReader dataReader = command.ExecuteReader();
            while (dataReader.Read())
            {
                SalesPersonDTO salesperson = new SalesPersonDTO();
                salesperson.slpCode = (short)dataReader["slpCode"];
                salesperson.slpName = (String)dataReader["slpName"];

                salespersonList.Add(salesperson);
            }
            dataReader.Close();

            return salespersonList;
        }

        public String GetSalespersonName(int slpCode)
        {
            String salespersonName = null;

            String query = "SELECT slpName FROM OSLP WHERE slpCode = " + slpCode;
            SqlCommand command = new SqlCommand(query, sqlServerConnection);
            SqlDataReader dataReader = command.ExecuteReader();
            while (dataReader.Read())
            {
                salespersonName = GetStringValue(dataReader, "slpName");
            }
            dataReader.Close();

            return salespersonName;
        }
    }

}
