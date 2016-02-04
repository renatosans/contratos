using System;
using System.IO;
using System.Timers;
using System.Reflection;
using System.Diagnostics;
using System.ServiceProcess;
using System.ComponentModel;
using System.Collections.Generic;
using MySql.Data.MySqlClient;
using DataManipulation;
using DataAccessObjects;
using DataTransferObjects;
using System.Net.Mail;


namespace BillingMailer
{
    public partial class MailerService : ServiceBase
    {
        private static String primaryServer; // Servidor primário onde se localiza o PHP e o MySQL

        private static String secondaryServer; // Servidor secundário onde se localizam os dados do SAP,  primário e secundário podem ser o mesmo ou o sistema pode estar distribuido

        private Timer mailingTimer;


        public MailerService()
        {
            InitializeComponent();
            mailingTimer = new Timer();
        }

        protected override void OnStart(String[] args)
        {
            mailingTimer.Elapsed +=new ElapsedEventHandler(TimeElapsed);
            mailingTimer.Interval = 600000; // seta um intervalo de 10 minutos
            mailingTimer.Start();
        }

        protected override void OnStop()
        {
            mailingTimer.Stop();
        }

        private void TimeElapsed(Object sender, ElapsedEventArgs e)
        {
            // Realiza a operação em uma thread separada
            BackgroundWorker backgroundWorker = new BackgroundWorker();
            backgroundWorker.DoWork += new DoWorkEventHandler(HandleMailing);
            backgroundWorker.RunWorkerAsync();

            System.Windows.Forms.Application.DoEvents();
        }

        public static void HandleMailing(Object sender, DoWorkEventArgs e)
        {
            if (EventLog.SourceExists("Billing Mailer"))
                EventLog.WriteEntry("Billing Mailer", "Iniciando fatura de contratos");

            try
            {
                // Processa a lista de envio (mailing) cadastrada no sistema
                ProcessMailingList();
            }
            catch (Exception exc)
            {
                if (EventLog.SourceExists("Billing Mailer"))
                    EventLog.WriteEntry("Billing Mailer", "Exceção encontrada -> " + Environment.NewLine + exc.Message + Environment.NewLine + exc.StackTrace);
            }
        }

        private static void ProcessMailingList()
        {
            DataConnector dataConnector = new DataConnector();
            dataConnector.OpenConnection();

            primaryServer = dataConnector.GetServer("Primary");
            secondaryServer = dataConnector.GetServer("Secondary");

            // Busca o servidor de envio que trabalha na porta 587 ( porta que o NET Smtp Client aceita )
            SmtpServerDAO smtpServerDAO = new SmtpServerDAO(dataConnector.MySqlConnection);
            List<SmtpServerDTO> serverList = smtpServerDAO.GetServers("porta=587");
            SmtpServerDTO smtpServer = null;
            if (serverList.Count == 1) smtpServer = serverList[0];

            String listaEnvio = "";
            MailingDAO mailingDAO = new MailingDAO(dataConnector.MySqlConnection);
            List<MailingDTO> mailingList = mailingDAO.GetMailings(null);
            foreach (MailingDTO mailing in mailingList)
            {
                // Verifica se hoje é o dia do faturamento
                Boolean isBillingTime = false;
                int diaFaturamento = mailing.diaFaturamento;
                if (diaFaturamento < 1) diaFaturamento = 1; // Consiste o dia para casos onde o usuário entrou um número negativo ou zero
                int daysInMonth = DateTime.DaysInMonth(DateTime.Now.Year, DateTime.Now.Month);
                if (diaFaturamento > daysInMonth) diaFaturamento = daysInMonth; // Consiste o dia para casos onde exceda a quantidade de dias do mês
                isBillingTime = (diaFaturamento == DateTime.Now.Day);

                // Verifica se já foi enviado hoje
                Boolean alreadySent = mailing.ultimoEnvio.Date == DateTime.Now.Date;

                if ((isBillingTime) && (!alreadySent) && (smtpServer != null))
                {
                    Boolean active = AreContractsActive(dataConnector, mailing);
                    if (active)
                    {
                        SendMailing(dataConnector, mailing, smtpServer);
                        listaEnvio += " Cliente: " + mailing.businessPartnerCode + " - " + mailing.businessPartnerName + " Enviado para: " + mailing.destinatarios + Environment.NewLine;
                    }
                }
            }
            if (String.IsNullOrEmpty(listaEnvio)) listaEnvio = "Vazia";
            if (EventLog.SourceExists("Billing Mailer"))
                EventLog.WriteEntry("Billing Mailer", "Lista de envio -> " + Environment.NewLine + listaEnvio);

            dataConnector.CloseConnection();
        }

        private static Boolean AreContractsActive(DataConnector connector, MailingDTO mailing)
        {
            Boolean active;

            ContractDAO contractDAO = new ContractDAO(connector.MySqlConnection);
            ContractItemDAO contractItemDAO = new ContractItemDAO(connector.MySqlConnection);

            // Faturamento de um contrato apenas
            if (mailing.codigoContrato != 0)
            {
                ContractDTO contract = contractDAO.GetContract(mailing.codigoContrato);
                active = (contract.status != 3) && (contract.status != 4);
                return active;
            }

            // Caso contrário é o faturamento de todos os equipamentos do cliente (um ou mais contratos)
            active = false;
            List<ContractItemDTO> itemList = contractItemDAO.GetItems("businessPartnerCode = '" + mailing.businessPartnerCode + "'");
            foreach (ContractItemDTO item in itemList)
            {
                ContractDTO contract = contractDAO.GetContract(item.contrato_id);
                if ((contract.status != 3) && (contract.status != 4)) active = true;
            }
            return active;
        }

        private static void SendMailing(DataConnector connector, MailingDTO mailing, SmtpServerDTO server)
        {
            MailMessage mailMessage = null;
            if (mailing.codigoContrato == 0)
                mailMessage = MountBusinessPartnerBilling(connector, mailing.businessPartnerCode, mailing.enviarDemonstrativo);
            else
                mailMessage = MountContractBilling(connector, mailing.codigoContrato, mailing.codigoSubContrato, mailing.enviarDemonstrativo);

            List<String> reportFiles = null;
            if (mailing.enviarDemonstrativo && mailMessage.Attachments.Count > 0) {
                reportFiles = new List<String>();
                foreach (Attachment mailAttachment in mailMessage.Attachments)
                    reportFiles.Add(mailAttachment.Name);
            }

            // TODO:  Consertar o envio através do .NET,  utilizar algum componente que funcione, o SmtpClient
            //        da Microsoft não suporta SSL implicito
            SmtpServer smtpServer = SmtpServer.ImportFromDTO(server);
            MailSender mailSender = new MailSender(smtpServer, new TraceHandler());
            mailSender.SetContents(mailMessage.Body, reportFiles);
            Boolean success = mailSender.SendMail("Faturamento de contrato", server.usuario, mailing.destinatarios);

            if (!success)
            {
                // caso não tenha tido sucesso ao enviar através do SmtpClient do .NET tenta enviar pelo PHP
                String mailerUrl = "http://" + primaryServer + "/Contratos/AjaxCalls/SendEmail.php";
                String mailerParams = "subject=Faturamento%20de%20contrato&mailBody=" + mailMessage.Body + "&recipients=" + mailing.destinatarios;
                String aditionalParams = "&fileCount=0";
                if (reportFiles != null)
                {
                    aditionalParams = "&fileCount=" + reportFiles.Count;
                    int fileIndex = 0;
                    foreach (String filename in reportFiles)
                    {
                        aditionalParams += "&filename" + fileIndex + "=" + reportFiles[fileIndex];
                        aditionalParams += "&path" + fileIndex + "=" + Environment.CurrentDirectory + @"\" + reportFiles[fileIndex];
                        fileIndex++;
                    }
                }

                RequestHandler mailerRequest = new RequestHandler(mailerUrl, new TraceHandler());
                mailerRequest.StartRequest(mailerParams + aditionalParams, null);
                String mailerResponse = (String)mailerRequest.ParseResponse(typeof(System.String));

                if (mailerResponse == "Email enviado com sucesso!") success = true;
                if (!success) EventLog.WriteEntry("Billing Mailer", mailerResponse);
            }

            if (success) // e-mail enviado com sucesso, grava no banco de dados a data
            {
                MailingDAO mailingDAO = new MailingDAO(connector.MySqlConnection);
                mailing.ultimoEnvio = DateTime.Now;
                mailingDAO.SetMailing(mailing);
            }
        }

        private static MailMessage MountContractBilling(DataConnector connector, int contractId, int subContractId, Boolean enviarDemonstrativo)
        {
            ContractDAO contractDAO = new ContractDAO(connector.MySqlConnection);
            SubContractDAO subContractDAO = new SubContractDAO(connector.MySqlConnection);
            ContractItemDAO contractItemDAO = new ContractItemDAO(connector.MySqlConnection);
            EquipmentDAO equipmentDAO = new EquipmentDAO(connector.SqlServerConnection);

            ContractDTO contract = contractDAO.GetContract(contractId);
            String contractItems = "";
            List<SubContractDTO> subContractList = subContractDAO.GetSubContracts("contrato_id=" + contract.id);
            foreach (SubContractDTO subContract in subContractList)
            {
                List<ContractItemDTO> itemList = contractItemDAO.GetItems("subcontrato_id = " + subContract.id);
                String equipmentEnumeration = "";
                foreach (ContractItemDTO contractItem in itemList)
                {
                    if (!String.IsNullOrEmpty(equipmentEnumeration)) equipmentEnumeration += ", ";
                    equipmentEnumeration += contractItem.codigoCartaoEquipamento;
                }
                List<EquipmentDTO> equipamentList = equipmentDAO.GetEquipments(equipmentEnumeration);
                String serialNumbers = "";
                foreach (EquipmentDTO equipment in equipamentList)
                {
                    if (!String.IsNullOrEmpty(serialNumbers)) serialNumbers += ", ";
                    serialNumbers += equipment.ManufSN;
                }
                if (String.IsNullOrEmpty(serialNumbers)) serialNumbers = "Nenhum item encontrado";
                contractItems += subContract.siglaTipo + " - " + serialNumbers + "<br/>";
            }

            String cliente = ObterNomeCliente(connector, contract.pn);
            String parcela = ObterParcelaContrato(connector, contract);
            String vendedor = ObterNomeVendedor(connector, contract);
            DateTime dataVencimento = MountDate(contract.diaVencimento, contract.referencialVencimento);
            String billingInfo = "Contrato: " + contract.numero + "<br/>" + "Itens: " + contractItems + "<br/>" + "Cliente: " + cliente + "<br/>" + "Parcela: " + parcela + "<br/>" + "Vendedor: " + vendedor + "<br/>" + "Data Vencimento: " + dataVencimento.ToString("dd/MM/yyyy") + "<br/>";

            List<String> reportFiles = new List<String>();
            if (enviarDemonstrativo) reportFiles = BuildReportFiles("faturamentoContrato.php", "contractId=" + contractId + "&subContractId=" + subContractId);

            MailMessage mailMessage = new MailMessage();
            mailMessage.Subject = "Faturamento de contrato";
            mailMessage.Body = "Email gerado automaticamente, não responder." + "<br/><br/>" + billingInfo;
            foreach (String filename in reportFiles)
                mailMessage.Attachments.Add(new Attachment(filename));

            return mailMessage;
        }

        private static MailMessage MountBusinessPartnerBilling(DataConnector connector, String businessPartnerCode, Boolean enviarDemonstrativo)
        {
            ContractDAO contractDAO = new ContractDAO(connector.MySqlConnection);
            SubContractDAO subContractDAO = new SubContractDAO(connector.MySqlConnection);
            ContractItemDAO contractItemDAO = new ContractItemDAO(connector.MySqlConnection);
            EquipmentDAO equipmentDAO = new EquipmentDAO(connector.SqlServerConnection);

            List<ContractItemDTO> itemList = contractItemDAO.GetItems("businessPartnerCode = '" + businessPartnerCode + "'");
            Dictionary<int, List<ContractItemDTO>> itemGroups = new Dictionary<int, List<ContractItemDTO>>();
            foreach (ContractItemDTO item in itemList)
            {
                // Cria um novo grupo caso não encontre um grupo para este subcontrato
                if (!itemGroups.ContainsKey(item.subContrato_id))
                    itemGroups.Add(item.subContrato_id, new List<ContractItemDTO>());

                // Adiciona o item ao grupo do subcontrato
                List<ContractItemDTO> group = itemGroups[item.subContrato_id];
                group.Add(item);
            }

            String billingInfo = "Cliente: " + ObterNomeCliente(connector, businessPartnerCode) + "<br/><br/>";
            foreach (int subContractId in itemGroups.Keys)
            {
                List<ContractItemDTO> group = itemGroups[subContractId];
                String itemEnumeration = "";
                foreach (ContractItemDTO contractItem in group)
                {
                    if (!String.IsNullOrEmpty(itemEnumeration)) itemEnumeration += ", ";
                    itemEnumeration += contractItem.codigoCartaoEquipamento;
                }
                List<EquipmentDTO> equipamentList = equipmentDAO.GetEquipments(itemEnumeration);
                String equipmentEnumeration = "";
                foreach (EquipmentDTO equipment in equipamentList)
                {
                    if (!String.IsNullOrEmpty(equipmentEnumeration)) equipmentEnumeration += ", ";
                    equipmentEnumeration += equipment.ManufSN;
                }
                SubContractDTO subContract = subContractDAO.GetSubContract(subContractId);
                ContractDTO contract = contractDAO.GetContract(subContract.contrato_id);
                Boolean activeContract = true;
                if ((contract.status == 3) || (contract.status == 4)) activeContract = false;
                if (activeContract) // Não fatura caso o status do contrato seja finalizado ou cancelado
                {
                    String parcela = ObterParcelaContrato(connector, contract);
                    String vendedor = ObterNomeVendedor(connector, contract);
                    DateTime dataVencimento = MountDate(contract.diaVencimento, contract.referencialVencimento);
                    String contractInfo = "Contrato: " + contract.numero + "<br/>" + "Parcela: " + parcela + "<br/>" + "Vendedor: " + vendedor + "<br/>" + "Data Vencimento: " + dataVencimento.ToString("dd/MM/yyyy") + "<br/>";
                    String subContractInfo = subContract.siglaTipo + " - " + equipmentEnumeration + "<br/>";
                    billingInfo += contractInfo + subContractInfo + "<br/>";
                }
            }

            List<String> reportFiles = new List<String>();
            if (enviarDemonstrativo) reportFiles = BuildReportFiles("faturamentoCliente.php", "businessPartnerCode=" + businessPartnerCode);

            MailMessage mailMessage = new MailMessage();
            mailMessage.Subject = "Faturamento de contrato";
            mailMessage.Body = "Email gerado automaticamente, não responder." + "<br/><br/>" + billingInfo;
            foreach(String filename in reportFiles)
                mailMessage.Attachments.Add(new Attachment(filename));

            return mailMessage;
        }

        // Monta os arquivos em disco, devolve uma lista com os nomes dos arquivos de relatório
        private static List<String> BuildReportFiles(String reportPage, String additionalParameters)
        {
            List<String> reportFiles = new List<String>();

            // Muda o diretório corrente para o lugar onde está o executável
            Environment.CurrentDirectory = Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location.ToString());
            // Obtem um identificador para o relatório (temporal)
            String reportStamp = DateTime.Now.Ticks.ToString();

            // Grava em disco o relatório no formato HTML
            String reportUrl = "http://" + primaryServer + "/Contratos/Frontend/mailing/" + reportPage;
            String requestParams = "startDate=&" + "endDate=&" + "acrescimo=&" + "obs=&" + additionalParameters; // Sem a faixa de datas recupera as duas últimas leituras
            RequestHandler requestHandler = new RequestHandler(reportUrl, new TraceHandler());
            requestHandler.StartRequest(requestParams, null);
            String response = (String)requestHandler.ParseResponse(typeof(System.String));

            String reportFilename = "Report" + reportStamp + ".htm";
            if (!File.Exists(reportFilename))
            {
                StreamWriter streamWriter = File.CreateText(reportFilename);
                streamWriter.Write(response);
                streamWriter.Close();
            }

            // Adiciona os nomes dos arquivos a lista
            reportFiles.Add(reportFilename);

            return reportFiles;
        }

        private static String ObterParcelaContrato(DataConnector connector, ContractDTO contract)
        {
            // Grava a parcela atual, adiciona uma parcela pois está iniciando um novo faturamento
            ContractDAO contractDAO = new ContractDAO(connector.MySqlConnection);
            int increase = 0;
            if ((contract.mesReferencia != DateTime.Now.Month)) {
                increase = 1;
                contractDAO.SetContractParcell(contract.id, contract.parcelaAtual + increase);
                contractDAO.SetContractMonthYear(contract.id, DateTime.Now.Month, DateTime.Now.Year);
            }

            return (contract.parcelaAtual + increase) + "/" + contract.quantidadeParcelas;
        }

        // Monta uma data a partir do dia, usa o mês corrente e o ano corrente, aplica o referencial sobre o mês
        private static DateTime MountDate(int day, int referential)
        {
            // Consiste o dia para casos onde o usuário entrou um número negativo ou zero
            if (day < 1) day = 1;

            // Calcula o mês e o ano a partir do referencial
            int month = DateTime.Now.AddMonths(referential).Month;
            int year = DateTime.Now.AddMonths(referential).Year;

            // Consiste o dia para casos onde exceda a quantidade de dias do mês
            int daysInMonth = DateTime.DaysInMonth(year, month);
            if (day > daysInMonth) day = daysInMonth;

            // Monta a data (resultado do método)
            DateTime date = new DateTime(year, month, day);

            return date;
        }

        // Busca o nome do vendedor no SQL Server
        private static String ObterNomeVendedor(DataConnector connector, ContractDTO contract)
        {
            SalesPersonDAO salesPersonDAO = new SalesPersonDAO(connector.SqlServerConnection);
            String nomeVendedor = salesPersonDAO.GetSalespersonName(contract.vendedor);

            return nomeVendedor;
        }

        // Busca o nome do parceiro de negócios no SQL Server
        private static String ObterNomeCliente(DataConnector connector, String cardCode)
        {
            BusinessPartnerDAO businessPartnerDAO = new BusinessPartnerDAO(connector.SqlServerConnection);
            BusinessPartnerDTO businessPartner = businessPartnerDAO.GetBusinessPartner(cardCode);
            String informacaoAdicional = "";
            if (businessPartner.CardName != businessPartner.CardFName) informacaoAdicional = " (" + businessPartner.CardFName + ")";
            String cliente = businessPartner.CardName + informacaoAdicional;

            return cliente;
        }
    }

}
