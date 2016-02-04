<?php
class connection{
	var $handle ;

	# constructor
	function connection(){
	}

	function attempts(){
		$attempts = 1 ;
		return $attempts ;
	}

	function last_insert_id(){
	    return mysql_insert_id( $this->handle ) ;
	}

	function open( $options = Array() ){
		global $db_auth_options ;
		if ( isset( $db_auth_options ) ){
			$options = $db_auth_options ;
		}

		for ( $iIndex = 0 ; $iIndex < $this->attempts() ; $iIndex++ ){
			$this->handle = @mysql_pconnect( $options{"host"} , $options{"user"} , $options{"password"} ) ;

			if ( isset( $this->handle ) && $this->handle ){
				mysql_set_charset("utf8");
				return @mysql_select_db( $options{"database"} , $this->handle ) ;
				break ;
			}
		}
		return 0 ;
	}

        //open customizado para clientes do netpost
	function open_np( $options = Array() ){
		global $npost_auth_options ;
		if ( isset( $npost_auth_options ) ){
			$options = $npost_auth_options ;
		}

		for ( $iIndex = 0 ; $iIndex < $this->attempts() ; $iIndex++ ){
			$this->handle = @mysql_pconnect( $options{"host"} , $options{"user"} , $options{"password"} ) ;

			if ( isset( $this->handle ) && $this->handle ){
				return @mysql_select_db( $options{"database"} , $this->handle ) ;
				break ;
			}
		}
		return 0 ;
	}

	
	function close(){
		return mysql_close( $this->handle ) ;
	}

	function execute( $sql ){
		if ( !$this->handle ){
			$this->open() ;
		}

		for( $iIndex = 0 ; $iIndex < $this->attempts() ; $iIndex++ ){
			if ( $result = @mysql_query( $sql , $this->handle ) ){
				return 1 ;
			}
		}
		return 0 ;
	}
}

class recordset{
	var $handle ;
	var $pointer = -1 ;
	var $row = Array() ;
	var $attempts = 1 ;
	var $EOF = 1 ;
	var $BOF = 1 ;
	var $page_size = -1 ;
	var $page = -1 ;
	var $record_count ;

	function record_cache( $sql ){

	}

	function add_cache( $r ){
	}

	function open( $sql , $conn ){
		if ( !$conn->handle ){
			$conn->open() ;
		}

		$num_args = func_num_args() ;
		$this->error = 0 ;

		for( $iIndex = 0 ; $iIndex < $this->attempts ; $iIndex++ ){
			if ( $this->handle = @mysql_query( $sql , $conn->handle ) ){
				$this->pointer = 0 ;
				$this->BOF = 1 ;
				$this->record_count = mysql_num_rows( $this->handle ) ;

				if ( $this->record_count > 0 && $this->page_size > 0 ){
					$this->total_pages = ceil( $this->record_count / $this->page_size ) ;

					if ( isset( $this->page ) ){
						$this->pointer += ( ( $this->page - 1 ) * $this->page_size ) ;
					}
				}

				$this->EOF = 0 ;
				if ( $this->pointer >= $this->record_count ){
					$this->EOF = 1 ;
				}

				if ( $this->record_count > 0 && $this->record_count > $this->pointer ){
					$this->fetch_row( $this->pointer ) ;
				}
				return 1 ;
			}
			$this->error = 1 ;
		}
		return 0 ;
	}

	function item( $name ){
		if ( isset( $this->row{$name} ) ){
			return $this->row{$name} ;
		}
		return "" ;
	}

	function itens(){
		if ( isset( $this->pointer ) ){
			$tmp_array = Array() ;
			while( list( $key , $value ) = each( $this->row ) ){
				if ( !is_numeric( $key ) && isset( $this->row{"$key"} ) ){
					$tmp_array[] = $key ;
				}
			}
			return $tmp_array ;
		}
		return 0 ;
	}

	function fetch_row(){
		$this->row = Array() ;

		if ( func_num_args() > 0 ){
			$this->BOF = 1 ;
			if ( $this->pointer > 0 ){
				$this->BOF = 0 ;
			}

			$this->EOF = 0 ;
			if ( $this->pointer > $this->record_count ){
				$this->EOF = 1 ;
			}
		}

		if ( $this->record_count == 0 ){
			return 0 ;
		}

		if ( !$this->EOF ){
			if ( func_num_args() > 0 ){
				mysql_data_seek( $this->handle , $this->pointer ) ;
			}

	    	if ( $this->row = mysql_fetch_array( $this->handle ) ){
	    		$this->pointer++ ;
	    		# verificar se página acabou.
				$this->BOF = 0 ;
	    	}
	    	else{
	    		$this->EOF = 1 ;
	    	}
		}
	}

    function close(){
    	if ( isset( $this->handle ) ){
#		mysql_free_result( $this->handle ) ;
	}
    }

    function move_next(){
    	return $this->fetch_row() ;
    }

    function move_previous(){
    	$this->pointer-- ;
    	return $this->fetch_row( $this->pointer ) ;
    }

    function move_first(){
    	$this->pointer = 0 ;
    	return $this->fetch_row( $this->pointer ) ;
    }

    function move_to( $pointer ){
    	if ( $pointer >= $this->record_count ){
    		return 0 ;
    	}
    	$this->pointer = $pointer ;
    	return $this->fetch_row( $this->pointer ) ;
    }

    function move_last(){
    	$this->pointer = 0 ;
    	if ( $this->page > 0 && $this->page_size > 0 ){
			$this->pointer += ( $this->page - 1 * $this->page_size ) ;
			$this->pointer += $this->page_size ;
		}
		else{
			$this->pointer += $this->record_count ;
		}
    	return $this->fetch_row( $this->pointer ) ;
    }

    function set_page( $page ){
    	if ( isset( $this->page_size ) ){
    		$this->page = $page ;
    		if ( isset( $this->total_pages ) ){
		    	if ( $page > 0 && $page <= $this->total_pages ){
		    		$this->pointer = ( $this->page - 1 ) * $this->page_size ;
		    		$this->fetch_row( $this->pointer ) ;
		    		return 1 ;
		    	}
		    }
	    }
	    return 0 ;
    }
}
?>