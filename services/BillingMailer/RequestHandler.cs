using System;
using System.IO;
using System.Net;
using System.Text;


namespace BillingMailer
{
    // Classe responsavel pelas requisições feitas ao servidor
    public class RequestHandler
    {
        private String serviceUrl;

        private String requestParams;

        private Object requestData;

        private Byte[] responseData;

        private IListener listener;


        public RequestHandler(String serviceUrl, IListener listener)
        {
            this.serviceUrl = serviceUrl;
            this.listener = listener;
        }

        private Byte[] ReadFullStream(Stream stream)
        {
            Byte[] buffer = new Byte[32768];

            using (MemoryStream memoryStream = new MemoryStream())
            {
                while (true)
                {
                    int read = stream.Read(buffer, 0, buffer.Length);
                    if (read <= 0)
                        return memoryStream.ToArray();

                    memoryStream.Write(buffer, 0, read);
                }
            }
        }

        private Boolean TrySend(int timeout, int attempts)
        {
            Exception lastException = null;
            HttpStatusCode status = HttpStatusCode.BadRequest; // valor inicial antes do envio
            if (listener != null) listener.NotifyObject("Enviando requisição...");

            HttpWebRequest request = null;
            Stream requestStream = null;
            HttpWebResponse response = null;
            Stream responseStream = null;
            try
            {
                request = (HttpWebRequest)WebRequest.Create(serviceUrl + "?" + requestParams);
                request.Method = "POST";
                request.ServicePoint.ConnectionLimit = timeout * 5;
                request.Timeout = timeout;
                request.ContentType = "application/x-www-form-urlencoded";
                request.ContentLength = 0;
                requestStream = request.GetRequestStream();
                response = (HttpWebResponse)request.GetResponse();
                status = response.StatusCode;
                responseStream = response.GetResponseStream();
                responseData = ReadFullStream(responseStream);
            }
            catch (Exception exception)
            {
                lastException = exception;
            }
            finally
            {
                if (responseStream != null) responseStream.Close();
                if (response != null) { ((IDisposable)response).Dispose(); response.Close(); }
                if (requestStream != null) requestStream.Close();
                request = null; // permite que o garbage collector elimine o objeto
            }

            if (lastException != null)
            {
                if ((listener != null) && (attempts == 0)) // Só exibe a primeira mensagem de erro ( primeira tentativa )
                    listener.NotifyObject(lastException);
                return false;
            }

            if (status != HttpStatusCode.OK)
            {
                if ((listener != null) && (attempts == 0)) // Só exibe a primeira mensagem de erro ( primeira tentativa )
                    listener.NotifyObject(new Exception("Falha no envio. Status = " + status));
                return false;
            }

            return true;
        }

        private Boolean SendRequest()
        {
            Boolean requestSent = false;
            int timeout = 16384;

            // Cria uma proteção contra loops infinitos (MAX_ATTEMPTS)
            const int MAX_ATTEMPTS = 3; // tenta no máximo 3 vezes

            int attempts = 0;
            while ((!requestSent) && (attempts < MAX_ATTEMPTS))
            {
                requestSent = TrySend(timeout, attempts);
                timeout = timeout * 2; // dobra o timeout a cada tentativa
                attempts++;
            }

            return requestSent;
        }

        public void ChangeUrl(String serviceUrl)
        {
            this.serviceUrl = serviceUrl;
        }

        // Inicia a requisição ao servidor
        public Boolean StartRequest(String requestParams, Object requestData)
        {
            this.requestParams = requestParams;
            this.requestData = requestData;

            if (!SendRequest())
            {
                listener.NotifyObject(new Exception("Falha ao enviar requisição. Params -> " + requestParams));
                return false;
            }

            return true;
        }

        // Trata cada tipo de resposta de acordo com o tipo do objeto
        public Object ParseResponse(Type objectType)
        {
            Object parsedValue = null;
            String response = Encoding.UTF8.GetString(responseData);

            // Se o tipo do objeto for int faz o parse correspondente
            if (objectType == typeof(int))
                parsedValue = int.Parse(response);

            // Se o tipo do objeto for DateTime faz o parse correspondente
            if (objectType == typeof(DateTime))
                parsedValue = DateTime.Parse(response);

            // Se o tipo do objeto for Boolean faz o parse correspondente
            if (objectType == typeof(Boolean))
                parsedValue = Boolean.Parse(response);

            // Se o tipo do objeto for String retorna seu valor sem o parse
            if (objectType == typeof(String))
                parsedValue = response;

            // Caso não se enquadre em nenhum dos tipos primitivos, apenas retorna a resposta
            if (parsedValue == null)
                parsedValue = responseData;

            return parsedValue;
        }
    }

}
