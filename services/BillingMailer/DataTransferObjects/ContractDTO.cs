using System;


namespace DataTransferObjects
{
    public class ContractDTO
    {
        public int id;
        public String numero;
        public String pn;
        public String divisao;
        public int contato;
        public int status;
        public DateTime assinatura;
        public DateTime encerramento;
        public DateTime inicioAtendimento;
        public DateTime fimAtendimento;
        public DateTime primeiraParcela;
        public int parcelaAtual;
        public int mesReferencia;
        public int anoReferencia;
        public int quantidadeParcelas;
        public Boolean global;
        public int vendedor;
        public int diaVencimento;
        public int referencialVencimento;
        public int diaLeitura;
        public int referencialLeitura;
        public int indiceReajuste_id;
        public DateTime? dataRenovacao;
        public DateTime? dataReajuste;
        public String obs;


        public ContractDTO()
        {
        }
    }

}
