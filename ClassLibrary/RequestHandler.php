<?php

    // Classe responsável pelo envio de requisições HTTP, também é possível utilizar a seguinte função
    // file_get_contents('http://'.$_SERVER['HTTP_HOST'].$root ...
    // Exemplo de uso :
    //          $listener = new Listener();
    //          $requestHandler = new RequestHandler('http://vmhome:2086/IntegrationService.aspx', $listener);
    //          $requestSucceded = $requestHandler->StartRequest($_SERVER['QUERY_STRING'], null);
    //          if ($requestSucceded == true) // Trata apenas o caso de requisição bem sucedida, o tratamento de erro ocorre no listener
    //          {
    //              echo $requestHandler->GetTextResponse();
    //          }
    class RequestHandler
    {
        private $serviceUrl;

        private $requestParams;

        private $requestData;

        private $responseData;

        private $listener;


        function __construct($serviceUrl, $listener)
        {
            $this->serviceUrl = $serviceUrl;
            $this->listener = $listener;
        }

        function TrySend($timeout)
        {
            $url = $this->serviceUrl.'?'.$this->requestParams;
            $listener = $this->listener;
            try
            {
                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, $url); 
                curl_setopt($handle, CURLOPT_FAILONERROR, 1);
                curl_setopt($handle, CURLOPT_TIMEOUT, $timeout); 
                curl_setopt($handle, CURLOPT_POST, 1); 
                curl_setopt($handle, CURLOPT_POSTFIELDS, $this->requestData);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
                $this->responseData = curl_exec($handle); 
                curl_close($handle);

                if ($this->responseData == false) return false;
            }
            catch(Exception $exc)
            {
                if ($listener != null) $listener->NotifyObject("Exceção: ".$exc);
                return false;
            }

            return true;
        }

        function SendRequest()
        {
            $requestSent = false;
            $timeout = 16384;

            // Cria uma proteção contra loops infinitos (MAX_ATTEMPTS)
            define("MAX_ATTEMPTS", 3); // tenta no máximo 3 vezes

            $attempts = 0;
            while ((!$requestSent) && ($attempts < MAX_ATTEMPTS))
            {
                $requestSent = $this->TrySend($timeout);
                $timeout = $timeout * 2; // dobra o timeout a cada tentativa
                $attempts++;
            }

            return $requestSent;
        }

        // Inicia a requisição ao servidor
        function StartRequest($requestParams, $requestData)
        {
            $this->requestParams = $requestParams; // parametros concatenados na Url
            $this->requestData = $requestData; // enviado por POST

            if (!$this->SendRequest())
            {
                $listener = $this->listener;
                if ($listener != null) $listener->NotifyObject("Falha ao enviar requisição. Params:  ".$this->requestParams);
                return false;
            }

            return true;
        }

        function GetXmlResponse()
        {
            $xmlEnd = strrpos($this->responseData, "</response>");
            $xmlContent = "<?xml version='1.0'?>".substr($this->responseData, 0, $xmlEnd + 11);

            $xml = simplexml_load_string($xmlContent);
            return $xml;
        }
        
        function GetTextResponse()
        {
            $xmlEnd = strrpos($this->responseData, "</response>");
            $xmlContent = substr($this->responseData, 0, $xmlEnd + 11);
            return $xmlContent;
        }
    }


    // Classe responsável pelo recebimento de notificações
    class Listener
    {
        function NotifyObject($obj)
        {
            echo '<response><error>'.$obj.'</error></response>';
        }
    }

?>
