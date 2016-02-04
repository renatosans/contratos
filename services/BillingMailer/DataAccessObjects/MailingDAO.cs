using System;
using System.Collections.Generic;
using DataTransferObjects;
using MySql.Data.MySqlClient;


namespace DataAccessObjects
{
    public class MailingDAO: DataAccessBase
    {
        public MailingDAO(MySqlConnection mySqlConnection)
        {
            this.mySqlConnection = mySqlConnection;
        }

        public List<MailingDTO> GetMailings(String filter)
        {
            List<MailingDTO> mailingList = new List<MailingDTO>();

            if (!String.IsNullOrEmpty(filter)) filter = " WHERE " + filter;
            String query = "SELECT * FROM `addoncontratos`.`mailing`" + filter + ";";
            MySqlCommand command = new MySqlCommand(query, this.mySqlConnection);
            MySqlDataReader dataReader = command.ExecuteReader();
            while (dataReader.Read())
            {
                MailingDTO mailing = new MailingDTO();
                mailing.id = (int)dataReader["id"];
                mailing.businessPartnerCode = (String)dataReader["businessPartnerCode"];
                mailing.businessPartnerName = (String)dataReader["businessPartnerName"];
                mailing.codigoContrato = (int)dataReader["contrato_id"];
                mailing.codigoSubContrato = (int)dataReader["subContrato_id"];
                mailing.diaFaturamento = (int)dataReader["diaFaturamento"];
                mailing.destinatarios = (String)dataReader["destinatarios"];
                mailing.enviarDemonstrativo = (Boolean)dataReader["enviarDemonstrativo"];
                mailing.ultimoEnvio = (DateTime)dataReader["ultimoEnvio"];

                mailingList.Add(mailing);
            }
            dataReader.Close();

            return mailingList;
        }

        public MailingDTO GetMailing(int id)
        {
            MailingDTO mailing = null;

            String query = "SELECT * FROM `addoncontratos`.`mailing` WHERE id=" + id;
            MySqlCommand command = new MySqlCommand(query, this.mySqlConnection);
            MySqlDataReader dataReader = command.ExecuteReader();
            if (dataReader.Read())
            {
                mailing = new MailingDTO();
                mailing.id = (int)dataReader["id"];
                mailing.businessPartnerCode = (String)dataReader["businessPartnerCode"];
                mailing.businessPartnerName = (String)dataReader["businessPartnerName"];
                mailing.codigoContrato = (int)dataReader["contrato_id"];
                mailing.codigoSubContrato = (int)dataReader["subContrato_id"];
                mailing.diaFaturamento = (int)dataReader["diaFaturamento"];
                mailing.destinatarios = (String)dataReader["destinatarios"];
                mailing.enviarDemonstrativo = (Boolean)dataReader["enviarDemonstrativo"];
                mailing.ultimoEnvio = (DateTime)dataReader["ultimoEnvio"];
            }
            dataReader.Close();

            return mailing;
        }

        public void SetMailing(MailingDTO mailing)
        {
            String commandText = "UPDATE `addoncontratos`.`mailing` SET" +
                                 "  businessPartnerCode='" + mailing.businessPartnerCode + "'" +
                                 ", businessPartnerName='" + mailing.businessPartnerName + "'" +
                                 ", contrato_id=" + mailing.codigoContrato +
                                 ", subContrato_id=" + mailing.codigoSubContrato +
                                 ", diaFaturamento=" + mailing.diaFaturamento +
                                 ", destinatarios='" + mailing.destinatarios + "'" +
                                 ", enviarDemonstrativo=" + mailing.enviarDemonstrativo +
                                 ", ultimoEnvio=@param1" +
                                 " WHERE id =" + mailing.id;
            if (mailing.id == 0) commandText = "INSERT INTO `addoncontratos`.`mailing` VALUES (NULL, '" + mailing.businessPartnerCode + "', " + mailing.codigoContrato + "', " + mailing.diaFaturamento + ", '" + mailing.destinatarios + "', " + mailing.enviarDemonstrativo + ", @param1)";
            MySqlParameter param1 = new MySqlParameter("@param1", MySqlDbType.DateTime);
            param1.Value = mailing.ultimoEnvio;
            MySqlCommand command = new MySqlCommand(commandText, this.mySqlConnection);
            command.Parameters.Add(param1);
            command.ExecuteNonQuery();
        }
    }

}
