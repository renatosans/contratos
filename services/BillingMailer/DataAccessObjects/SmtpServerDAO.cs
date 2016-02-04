using System;
using System.Collections.Generic;
using DataTransferObjects;
using MySql.Data.MySqlClient;


namespace DataAccessObjects
{
    public class SmtpServerDAO: DataAccessBase
    {
        public SmtpServerDAO(MySqlConnection mySqlConnection)
        {
            this.mySqlConnection = mySqlConnection;
        }

        public List<SmtpServerDTO> GetServers(String filter)
        {
            List<SmtpServerDTO> serverList = new List<SmtpServerDTO>();

            if (!String.IsNullOrEmpty(filter)) filter = " WHERE " + filter;
            String query = "SELECT * FROM `addoncontratos`.`smtpServer`" + filter + ";";
            MySqlCommand command = new MySqlCommand(query, this.mySqlConnection);
            MySqlDataReader dataReader = command.ExecuteReader();
            while (dataReader.Read())
            {
                SmtpServerDTO smtpServer = new SmtpServerDTO();
                smtpServer.id = (int)dataReader["id"];
                smtpServer.nome = (String)dataReader["nome"];
                smtpServer.endereco = (String)dataReader["endereco"];
                smtpServer.porta = (int)dataReader["porta"];
                smtpServer.usuario = (String)dataReader["usuario"];
                smtpServer.senha = (String)dataReader["senha"];
                smtpServer.requiresTLS = (Boolean)dataReader["requiresTLS"];
                smtpServer.defaultServer = (Boolean)dataReader["defaultServer"];

                serverList.Add(smtpServer);
            }
            dataReader.Close();

            return serverList;
        }
    }

}
