using System;
using System.IO;
using System.Xml;
using System.Reflection;
using System.Data.SqlClient;
using MySql.Data.MySqlClient;


namespace DataManipulation
{
    public class DataConnector
    {
        private String primaryServer;

        private String secondaryServer;

        private MySqlConnection mySqlConnection;

        private SqlConnection sqlServerConnection;

        public MySqlConnection MySqlConnection
        {
            get { return mySqlConnection; }
        }

        public SqlConnection SqlServerConnection
        {
            get { return sqlServerConnection; }
        }

        public DataConnector()
        {
            String baseDir = Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location.ToString());
            XmlDocument xmlDoc = new XmlDocument();
            xmlDoc.Load(baseDir + @"\DataAccess.xml");
            XmlNodeList nodeList = xmlDoc.ChildNodes[1].ChildNodes;
            XmlNode mylSqlNode = null;
            XmlNode sqlServerNode = null;
            foreach (XmlNode node in nodeList)
            {
                if (node.Attributes["name"].Value == "MySQL 5.5")
                    mylSqlNode = node;
                if (node.Attributes["name"].Value == "SQL Server 2008")
                    sqlServerNode = node;
            }
            if ((mylSqlNode == null) || (sqlServerNode == null)) return;

            String server = mylSqlNode.SelectSingleNode("server").InnerText;
            String database = mylSqlNode.SelectSingleNode("database").InnerText;
            String username = mylSqlNode.SelectSingleNode("username").InnerText;
            String password = mylSqlNode.SelectSingleNode("password").InnerText;
            String connectionString = "server=" + server + ";user id=" + username + ";password=" + password + ";";
            if (!String.IsNullOrEmpty(database)) connectionString += "database=" + database + ";";
            mySqlConnection = new MySqlConnection(connectionString);
            primaryServer = server; // Servidor primário onde se localiza o PHP e o MySQL

            server = sqlServerNode.SelectSingleNode("server").InnerText;
            database = sqlServerNode.SelectSingleNode("database").InnerText;
            username = sqlServerNode.SelectSingleNode("username").InnerText;
            password = sqlServerNode.SelectSingleNode("password").InnerText;
            connectionString = @"Data Source=" + server + ";Initial Catalog=" + database + "; User=" + username + "; password=" + password;
            sqlServerConnection = new SqlConnection(connectionString);
            secondaryServer = server; // Servidor secundário onde se localizam os dados do SAP,  primário e secundário podem ser o mesmo ou o sistema pode estar distribuido
        }

        public void OpenConnection()
        {
            mySqlConnection.Open();
            sqlServerConnection.Open();
        }

        public void CloseConnection()
        {
            mySqlConnection.Close();
            sqlServerConnection.Close();
        }

        public String GetServer(String identifier)
        {
            if (identifier == "Primary") return primaryServer;
            if (identifier == "Secondary") return secondaryServer;
            return null; // Caso seja passado identificador inválido
        }
    }

}
