<?php

class Cipher{
    var $salt = NULL;


    function Cipher() {
        $this->salt = "youwiilneverbreakit";
    }

    // Gera um hash MD5 para proteção da senha
    function GenerateHash($password) {
        $hash = NULL;
        $pass = $password;
        $salt = $this->salt;

        $salt1 = substr($salt, 0, (strlen($salt)/2));
        $salt2 = substr($salt, (strlen($salt)/2), strlen($salt));
            $salt = $salt2.$salt1;

        $pass1 = substr($pass, 0, (strlen($pass)/2));
        $pass2 = substr($pass, (strlen($pass)/2), strlen($pass));
            $pass = $pass2.$pass1;

        $hash = substr(crypt(sha1(md5($pass)),sha1(md5($salt))),0,10);

        return $hash;
    }

    // Faz a criptografia do texto passado como parâmetro
    function Encode($text) {
        // not implemented yet
    }

    // Remove a criptografia do texto criptografado passado 
    function Decode($encodedText) {
        // not implemented yet
    }

}
