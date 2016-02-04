using System;
using System.Net;
using System.Net.Mail;
using System.ComponentModel;
using System.Collections.Generic;


namespace BillingMailer
{
    /// <summary>
    /// Classe utilizada para o envio de e-mails pelo sistema
    /// </summary>
    public class MailSender
    {
        private SmtpClient smtpClient;

        private IListener exceptionHandler;

        private String mailBody = "";

        private List<String> attachmentFiles = null;


        public MailSender(SmtpServer smtpServer, IListener exceptionHandler)
        {
            if (smtpServer == null)
            {
                if (exceptionHandler != null)
                    exceptionHandler.NotifyObject(new Exception("Parâmetro nulo - smtpServer"));
                return;
            }

            smtpClient = new SmtpClient(smtpServer.address, smtpServer.port);
            if (smtpServer.requiresTLS) smtpClient.EnableSsl = true;
            Boolean useCredentials = true;
            if (String.IsNullOrEmpty(smtpServer.username)) useCredentials = false;
            if (String.IsNullOrEmpty(smtpServer.password)) useCredentials = false;
            if (useCredentials)
            {
                smtpClient.Credentials = new NetworkCredential(smtpServer.username, smtpServer.password);
            }
            // Atribui o handler SendCompleted, utilizado para envio assíncrono
            smtpClient.SendCompleted += new SendCompletedEventHandler(SmtpClientSendCompleted);

            // Atribui o responsável pelo tratamento de exceções, elas não são tratadas nesta classe
            this.exceptionHandler = exceptionHandler;
        }

        private void SmtpSend(MailMessage mailMessage, Boolean async)
        {
            if (async)
                smtpClient.SendAsync(mailMessage, null);
            else
                smtpClient.Send(mailMessage);
        }

        private void SmtpClientSendCompleted(object sender, AsyncCompletedEventArgs e)
        {
            // Envio assíncrono foi completado, verifica possíveis exceções
            if (e.Error != null)
            {
                if (exceptionHandler != null)
                    exceptionHandler.NotifyObject(e.Error);
            }
        }

        /// <summary>
        /// Define o conteudo do e-mail (o corpo da mensagem e os anexos)
        /// </summary>
        public void SetContents(String mailBody, List<String> attachmentFiles)
        {
            this.mailBody = mailBody;
            this.attachmentFiles = attachmentFiles;
        }

        /// <summary>
        /// Inicia o envio do e-mail através do servidor de smtp
        /// </summary>
        public Boolean SendMail(String subject, String sender, String recipients)
        {
            try
            {
                // Cria a mensagem de email a ser enviada
                MailMessage mailMessage = new MailMessage(sender, recipients, subject, mailBody);
                mailMessage.IsBodyHtml = true;
                if (attachmentFiles != null)
                {
                    foreach (String attachmentFile in attachmentFiles)
                    {
                        // Caso o arquivo não exista uma exceção é disparada, o Listener é notificado
                        Attachment mailAttachment = new Attachment(attachmentFile);
                        mailMessage.Attachments.Add(mailAttachment);
                    }
                }

                // Envia o e-mail de maneira síncrona, async = false
                SmtpSend(mailMessage, false);
            }
            catch (Exception Exc)
            {
                if (exceptionHandler != null)
                    exceptionHandler.NotifyObject(Exc);

                return false;
            }

            return true;
        }
    }

}
