using System;
using DataTransferObjects;


namespace BillingMailer
{
    /// <summary>
    /// Classe utilizada para passagem de dados/atributos de um servidor SMTP
    /// </summary>
    public class SmtpServer
    {
        public String name;

        public String address;

        public int port;

        public Boolean requiresTLS;

        public String username;

        public String password;


        public SmtpServer(String name, String address, int port)
        {
            this.name = name;
            this.address = address;
            this.port = port;
        }

        public static SmtpServer ImportFromDTO(SmtpServerDTO dtoObject)
        {
            SmtpServer server = new SmtpServer(dtoObject.nome, dtoObject.endereco, dtoObject.porta);
            server.requiresTLS = dtoObject.requiresTLS;
            server.username = dtoObject.usuario;
            server.password = dtoObject.senha;

            return server;
        }
    }

}
