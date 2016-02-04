using System;
using System.Data.SqlClient;
using DataTransferObjects;


namespace DataAccessObjects
{
    public class BusinessPartnerDAO: DataAccessBase
    {
        public BusinessPartnerDAO(SqlConnection sqlServerConnection)
        {
            this.sqlServerConnection = sqlServerConnection;
        }

        /// <summary>
        /// Obtem um parceiro de negócio a partir de seu código
        /// </summary>
        public BusinessPartnerDTO GetBusinessPartner(String cardCode)
        {
            String query = "SELECT CardCode, CardName, CardFName, CntctPrsn FROM OCRD WHERE CardCode = '" + cardCode + "'";
            SqlCommand command = new SqlCommand(query, sqlServerConnection);
            SqlDataReader dataReader = command.ExecuteReader();
            if (!dataReader.Read()) return null;
            BusinessPartnerDTO businessPartner = new BusinessPartnerDTO();
            businessPartner.CardCode = GetStringValue(dataReader, "CardCode");
            businessPartner.CardName = GetStringValue(dataReader, "CardName");
            businessPartner.CardFName = GetStringValue(dataReader, "CardFName");
            businessPartner.CntctPrsn = GetStringValue(dataReader, "CntctPrsn");
            dataReader.Close();

            return businessPartner;
        }

        /// <summary>
        /// Obtem o parceiro de negócio responsável pelo transporte de mercadorias
        /// </summary>
        public BusinessPartnerDTO GetDefaultCarrier()
        {
            String subQuery = "SELECT GroupCode FROM OCRG WHERE GroupName = 'Transportadora'";
            String query = "SELECT CardCode, CardName, CardFName, CntctPrsn FROM OCRD WHERE GroupCode = (" + subQuery + ") ORDER BY UpdateDate ASC";
            SqlCommand command = new SqlCommand(query, sqlServerConnection);
            SqlDataReader dataReader = command.ExecuteReader();
            if (!dataReader.Read()) return null;
            BusinessPartnerDTO businessPartner = new BusinessPartnerDTO();
            businessPartner.CardCode = GetStringValue(dataReader, "CardCode");
            businessPartner.CardName = GetStringValue(dataReader, "CardName");
            businessPartner.CardFName = GetStringValue(dataReader, "CardFName");
            businessPartner.CntctPrsn = GetStringValue(dataReader, "CntctPrsn");
            dataReader.Close();

            return businessPartner;
        }
    }

}
