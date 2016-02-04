using System;


namespace DataTransferObjects
{
    public class SmtpServerDTO
    {
        public int id;
        public String nome;
        public String endereco;
        public int porta;
        public String usuario;
        public String senha;
        public Boolean requiresTLS;
        public Boolean defaultServer;


        public SmtpServerDTO()
        {
        }
    }

}
