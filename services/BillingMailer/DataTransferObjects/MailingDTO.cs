using System;


namespace DataTransferObjects
{
    public class MailingDTO
    {
        public int id;
        public String businessPartnerCode;
        public String businessPartnerName;
        public int codigoContrato;
        public int codigoSubContrato;
        public int diaFaturamento;
        public String destinatarios;
        public Boolean enviarDemonstrativo;
        public DateTime ultimoEnvio;


        public MailingDTO()
        {
        }
    }

}
