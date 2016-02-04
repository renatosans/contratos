<?php

class Utils{


    #construtor
    function Utils(){
    }

    #Funcao gerarNivelPagina
    #Objetivo: gerar uma string para ser usada no html, de modo a atingir o diretorio raiz do site
    #parametros: 
    #    intNivel: numero de subdiretorios a subir
    #retorno:
    #    strNivel: string para ser concatenada no html
    #Criada em: 28/03/2004
    #Ultima altera��o: 28/03/2004
    #--------------------------------------------------------
    function gerarNivelPagina($intNivel){
        $strNivel = "";
        $intContador = 0;
    
        for($intContador = 0; $intContador < $intNivel; $intContador++){
            $strNivel .= "../";
        }
    
        return $strNivel;
    }

    #Funcao getForm
    #Objetivo: Recupera os dados do formulario, filtrando as aspas simples do conteudo;
    #          Esta funcao e uma evolucao da antiga FiltraSQL
    #parametros:
    #    campo: o nome do campo a recuperar
    #retorno: a string modificada recuperada do formulario
    #Criada em: 28/03/2004
    #Ultima altera��o: 28/03/2004
    #--------------------------------------------------------
    function getForm($campo){
        if(isset($_REQUEST[$campo])){
            if( strpos( strtolower( $_REQUEST[$campo] ), "information_schema") !== false || strpos( strtolower( $_REQUEST[$campo] ), "concat(") !== false || strpos( strtolower( $_REQUEST[$campo] ), "count(") !== false || strpos( strtolower( $_REQUEST[$campo] ), "union select") !== false ){
                return 1;
            }

            return mysql_real_escape_string( $_REQUEST[$campo] );
        }
        else{
            return "";
        }
    }

    #Funcao dateFormatMySQL
    #Objetivo: formatar a data no padrao MySQL(YYYY-MM-DD)
    #parametros:
    #    oData: a data a converter, no formato dd/mm/yyyy
    #retorno: a data no formato MySQL
    #Criada em: 28/03/2004
    #Ultima altera��o: 28/03/2004
    #--------------------------------------------------------
    function dateFormatMySQL($oData){
        $arrData = null; //armazenara o array da data de nasc
        $dt = ""; //armazenara a data ja no formato mysql

        $arrData = split("/", $oData);
        $dt = $arrData[2] . "-" . $arrData[1] . "-" . $arrData[0];        

        return $dt;
    }


    #Funcao dateFormatBr
    #Objetivo: formatar a data no padrao brasileiro(DD/MM/YYYY)
    #parametros:
    #    oData: a data a converter, no formato yyyy-mm-dd
    #retorno: a data no formato Br
    #Criada em: 28/03/2004
    #Ultima altera��o: 28/03/2004
    #--------------------------------------------------------
    function dateFormatBr($oData){
        $arrData = null; //armazenara o array da data de nasc
        $dt = ""; //armazenara a data ja no formato mysql

        $arrData = split("-", $oData);
        $dt = $arrData[2] . "/" . $arrData[1] . "/" . $arrData[0];        

        return $dt;
    }


    #Funcao dateFormatBr2
    #Objetivo: formatar a data no padrao brasileiro com 2 digitos de ano(DD/MM/YY)
    #parametros:
    #    oData: a data a converter, no formato yyyy-mm-dd
    #retorno: a data no formato Br
    #Criada em: 28/03/2004
    #Ultima altera��o: 28/03/2004
    #--------------------------------------------------------
    function dateFormatBr2($oData){
        $arrData = null; //armazenara o array da data de nasc
        $dt = ""; //armazenara a data ja no formato mysql

        $arrData = split("-", $oData);
        $dt = $arrData[2] . "/" . $arrData[1] . "/" . substr($arrData[0], 2, 2);

        return $dt;
    }

    #Funcao cut 
    #Objetivo: Cortar uma string de acordo com o limite de caracteres estipulados
    #parametros:
    #    	$str : a string inteira que vai ser cortada.
    #		$pos : posicao inicial em que sera cortada a string
    #		$maxlenght : o n�mero de caracteres que sera cortado da string ( caso ela exceda )
    #		#str end : str que sera adicionada a string original caso seja cortada
    #retorno: a string recortada
    #Criada em: 12/01/2006
    #Ultima altera��o: 12/01/2006
    #--------------------------------------------------------
    function cut( $str , $pos , $maxlenght , $strend )
    {
    	
	$r = substr( trim($str ) , $pos , $maxlenght );
	
	if( strlen( $str ) > $maxlenght ){
		$r.=$strend;
	}
	
	return $r;
	
    }

    #Funcao dateAdd
    #Objetivo: Adicionar uma quantidade de dias a uma data
    #parametros:
    #    v: dias a adicionar. caso nao seja especificado o parametro d, sera adicionado ao dia atual
    #    d: data em que sera adicionado(opcional)
    #    f: formato de exibicao(opcional)
    #retorno: o valor no formato US
    #Criada em: 11/01/2006
    #Ultima altera��o: 11/01/2006
    #--------------------------------------------------------
    function dateAdd($v,$d=null , $f="d/m/Y"){
      $d = ( ( $d )? $d : date("Y-m-d") );
      return date( $f, strtotime($v." days", strtotime($d) ) );
    }


    #Funcao decimalFormatUS
    #Objetivo: formatar numero decimal no padr�o americano (caracter '.' como marcador de casa decimal)
    #parametros:
    #    oValor: valor a converter
    #retorno: o valor no formato US
    #Criada em: 18/11/2005
    #Ultima altera��o: 18/11/2005
    #--------------------------------------------------------
    function decimalFormatUS($oValor){
        //return number_format($oValor, 2, ".", "");
        return str_replace(",", ".", str_replace(".", "", $oValor));
    }


    #Funcao decimalFormatBR
    #Objetivo: formatar numero decimal no padr�o Brasileiro (caracter ',' como marcador de casa decimal)
    #parametros:
    #    oValor: valor a converter
    #retorno: o valor no formato BR
    #Criada em: 18/11/2005
    #Ultima altera��o: 18/11/2005
    #--------------------------------------------------------
    function decimalFormatBR($oValor){
        return number_format($oValor, 2, ",", ".");
        //return str_replace(".", ",", str_replace(",", "", $oValor));
    }


    #Funcao listDir
    #Objetivo: lista os arquivos do diretorio indicado
    #parametros:
    #    oDir: caminho para o diretorio
    #retorno: array com os nomes dos arquivos encontrados
    #Criada em: 09/05/2006
    #Ultima altera��o: 09/06/2006
    #--------------------------------------------------------
    function listDir( $oDir ){
        $arrResult = array();

        if( $handle = opendir( $oDir ) ){
                while(false !== ($file = readdir($handle))) {
                        if($file != "." && $file != "..") {
                                $arrResult[] = $file;
                        }
                }
                closedir($handle);
        }

        return $arrResult;
    }

    #Funcao criarThumbnail
    #Objetivo: cria o thumbnail da imagem indicada
    #parametros:
    #    oImg: caminho para a imagem original
    #    oThumb: caminho para o thumb
    #    oWidth: largura da imagem destino
    #    oHeight: altura da imagem destino
    #retorno: true se gerou, false em caso de erro
    #Criada em: 09/05/2006
    #Ultima altera��o: 09/06/2006
    #Agradecimentos a Maur�cio Massaia(_M) pelo c�digo de exemplo cedido
    #--------------------------------------------------------
    function criarThumbnail( $oImg, $oThumb, $oWidth, $oHeight ){
        $tmp_image = null;
        $tipoImg = "";
        if( strpos($oImg, ".jpg") !== false ){
                $tmp_image=imagecreatefromjpeg( $oImg );
                $tipoImg = "jpg";
        }
        else{
                $tmp_image=imagecreatefromgif( $oImg );
                $tipoImg = "gif";
        }

        $width = imagesx($tmp_image);
        $height = imagesy($tmp_image);

        $new_image = imagecreatetruecolor($oWidth,$oHeight);
        
        imagecopyresampled($new_image, $tmp_image,0,0,0,0, $oWidth, $oHeight, $width, $height);

        if( $tipoImg == "jpg" ){
                if ( imagejpeg ( $new_image, $oThumb, 100 ) ){
                        return true;
                }
                else{
                        return false;
                }
        }
        else{
                imagetruecolortopalette($new_image, false, 256);
                if ( imagegif ( $new_image, $oThumb, 100 ) ){
                        return true;
                }
                else{
                        return false;
                }
        }
    }
    
    
    #Funcao criarThumbnailCentralizado
    #Objetivo: cria o thumbnail redimensionando a imagem na mesma propor��o 
    # e ent�o efetua um "crop" centralizado pro tamanho correto do thumb
    #parametros:
    #    filein: caminho para a imagem original
    #    fileout: caminho para o thumb
    #    imagethumbsize_w: largura da imagem destino
    #    imagethumbsize_h: altura da imagem destino
    #    red: por��o rgb de vermelho para o background
    #    green: por��o rgb de verde para o background
    #    blue: por��o rgb de azul para o background
    #
    #retorno: true se gerou, false em caso de erro
    #Criada em: 09/05/2006
    #Ultima altera��o: 09/06/2006
    #Agradecimentos a Maur�cio Massaia(_M) pelo c�digo de exemplo cedido
    #--------------------------------------------------------
    function criarThumbnailCentralizado( $filein,  $imagethumbsize_w, $imagethumbsize_h, $red, $green, $blue){
        
        // Get new dimensions
        list($width, $height) = getimagesize($filein);
        
        if(preg_match("/.jpg/i", "$filein")){
               $format = 'image/jpeg';
        }
        if (preg_match("/.gif/i", "$filein")){
               $format = 'image/gif';
        }
        if(preg_match("/.png/i", "$filein")){
               $format = 'image/png';
        }
          
        switch($format){
            case 'image/jpeg':
            $image = imagecreatefromjpeg($filein);
            break;
            case 'image/gif';
            $image = imagecreatefromgif($filein);
            break;
            case 'image/png':
            $image = imagecreatefrompng($filein);
            break;
        }
        
        $width = $imagethumbsize_w ;
        $height = $imagethumbsize_h ;
        list($width_orig, $height_orig) = getimagesize($filein);
        
        if ($width_orig < $height_orig) {
          $height = ($imagethumbsize_w / $width_orig) * $height_orig;
        } else {
            $width = ($imagethumbsize_h / $height_orig) * $width_orig;
        }
        
        if ($width < $imagethumbsize_w)
        //if the width is smaller than supplied thumbnail size
        {
        $width = $imagethumbsize_w;
        $height = ($imagethumbsize_w/ $width_orig) * $height_orig;
        }
        
        if ($height < $imagethumbsize_h)
        //if the height is smaller than supplied thumbnail size
        {
        $height = $imagethumbsize_h;
        $width = ($imagethumbsize_h / $height_orig) * $width_orig;
        }
        
        $thumb = imagecreatetruecolor($width , $height); 
        $bgcolor = imagecolorallocate($thumb, $red, $green, $blue);  
        ImageFilledRectangle($thumb, 0, 0, $width, $height, $bgcolor);
        imagealphablending($thumb, true);
        
        imagecopyresampled($thumb, $image, 0, 0, 0, 0,
        $width, $height, $width_orig, $height_orig);
        $thumb2 = imagecreatetruecolor($imagethumbsize_w , $imagethumbsize_h);
        // true color for best quality
        $bgcolor = imagecolorallocate($thumb2, $red, $green, $blue);  
        ImageFilledRectangle($thumb2, 0, 0, $imagethumbsize_w , $imagethumbsize_h , $bgcolor);
        imagealphablending($thumb2, true);
        
        $w1 =($width/2) - ($imagethumbsize_w/2);
        $h1 = ($height/2) - ($imagethumbsize_h/2);
        
        imagecopyresampled($thumb2, $thumb, 0,0, $w1, $h1,
        $imagethumbsize_w , $imagethumbsize_h ,$imagethumbsize_w, $imagethumbsize_h);

        switch($format){
            case 'image/jpeg':
                imagejpeg($thumb2); //write to file
                break;
            case 'image/gif';
                imagegif($thumb2); //write to file
                break;
            case 'image/png':
                imagepng($thumb2); //write to file
                break;
            break;
        }        

        
    }
    
    
    #retorno: true se gerou, false em caso de erro
    #Criada em: 09/05/2006
    #Ultima altera��o: 09/06/2006
 
    function espaco_branco($tam, $texto)
    {

    if (strlen($texto)<$tam) {
        for($i=strlen($texto);$i<$tam;$i++){
                 $texto = $texto.' ';
                }
     }
     return $texto;
    }

    #Funcao resume
    #Objetivo: resume um texto em caracteres sem cortar palavras
    #parametros:
    #    text: texto a ser resumido
    //   limit: limite em caracteres
    #retorno: o texto cortado e adcionado '...' caso necessário
    #Criada em: 19/11/2010
    #Ultima alteração: 03/01/2011
    #--------------------------------------------------------
    function resume($text,$limit){
        if(strlen($text)>$limit){
			
            $text = substr($text, 0, strrpos(substr($text, 0, $limit), ' '));

			while( !ctype_alnum($text[strlen($text)-1]) ){
				$text = substr($text,0,strlen($text)-1);
			}
			
			$text .= "...";
        }
        return $text;
    }

    // resume um texto cortando fora os caracteres excedentes, trunca a última palavra
    function truncateText($text,$limit){
        if(strlen($text)>$limit){
            $text = substr($text, 0, $limit-3);
            $text .= "...";
        }

        return $text;
	}

    // Adiciona horas e minutos a um timestamp
    function addTime($time, $hours, $minutes)
    {
        $hour = date("H", $time);
        $minute = date("i", $time);
        $second = date("s", $time);
        $month = date("m", $time);
        $day = date("d", $time);
        $year = date("Y", $time);
        $result = mktime(($hour + $hours), ($minute + $minutes), $second, $month, $day, $year);

        return $result;
    }

    // Adiciona meses a um timestamp
    function addMonths($time, $months)
    {
        $hour = date("H", $time);
        $minute = date("i", $time);
        $second = date("s", $time);
        $month = date("m", $time);
        $day = date("d", $time);
        $year = date("Y", $time);
        $result = mktime($hour, $minute, $second, ($month + $months), $day, $year);

        return $result;
    }

    #Funcao date2timestamp
    #Objetivo: transforma uma data em unix epoch timestamp
    #parametros:
    #    data: data a ser transformada formato d-m-yyyy
    //   separador: separador da data (default '-')
    //   hora: hora a ser gravada na timestamp (default 0:0)
    #retorno: a timestamp da data e hora inseridas
    #Criada em: 17/01/2011
    #Ultima alteração: 17/01/2011
    #--------------------------------------------------------

    function date2timestamp($data,$hora='0:0',$separador='-'){
        $data = explode($separador,$data);
        $hora = explode(':',$hora);
        return mktime($hora[0], $hora[1], 0, $data[1], $data[0], $data[2]);
    }

    #Funcao encrypt
    #Objetivo: cria um hash de criptografia irreversivel de 10 caracteres
    #parametros:
    #    pass: senha a ser criptografada
    //   salt: sal ou senha de criptografia
    #retorno: senha criptografada
    #Criada em: 25/01/2011
    #Ultima alteração: 25/01/2011
    #--------------------------------------------------------

    function encrypt($pass,$salt="youwiilneverbreakit"){
        $salt1 = substr($salt, 0, (strlen($salt)/2));
        $salt2 = substr($salt, (strlen($salt)/2), strlen($salt));
            $salt = $salt2.$salt1;

        $pass1 = substr($pass, 0, (strlen($pass)/2));
        $pass2 = substr($pass, (strlen($pass)/2), strlen($pass));
            $pass = $pass2.$pass1;

        return substr(crypt(sha1(md5($pass)),sha1(md5($salt))),0,10);
    }
    function dateDiff($time1, $time2, $precision = 6) {
    
    // If not numeric then convert texts to unix timestamps
    if (!is_int($time1)) {
      $time1 = strtotime($time1);
    }
    if (!is_int($time2)) {
      $time2 = strtotime($time2);    
    }   

    // If time1 is bigger than time2
    // Then swap time1 and time2
    if ($time1 > $time2) {
      $ttime = $time1;
      $time1 = $time2;
      $time2 = $ttime;
    }

    // Set up intervals and diffs arrays
    $intervals = array('year','month','day','hour','minute','second');
    $diffs = array();

    // Loop thru all intervals
    foreach ($intervals as $interval) {
      // Set default diff to 0
      $diffs[$interval] = 0;
      // Create temp time from time1 and interval
      $ttime = strtotime("+1 " . $interval, $time1);
      // Loop until temp time is smaller than time2
      while ($time2 >= $ttime) {
	$time1 = $ttime;
	$diffs[$interval]++;
	// Create new temp time from time1 and interval
	$ttime = strtotime("+1 " . $interval, $time1);
      }
    }
 
    $count = 0;
    $times = array();
    // Loop thru all diffs
    foreach ($diffs as $interval => $value) {
      // Break if we have needed precission
      if ($count >= $precision) {
	break;
      }
      // Add value and interval 
      // if value is bigger than 0
      if ($value > 0) {
	// Add s if value is not 1
	if ($value != 1) {
	  $interval .= "s";
	}
	// Add value and interval to times array
	$times[] = $value . " " . $interval;
	$count++;
      }
    }

    // Return string with times
    //return implode(", ", $times);
    return $diffs;
  }
}
?>