<?php
	require_once('config.php');
	
	//Class db is a wrapper for mysqli, using the connection variables from config.php
	class db {
		
		public $mysqli;
		public function __construct( ) {
			$config = new config;
			$this->mysqli = new mysqli($config->hostname,
				$config->username,
				$config->password,
				$config->db);
				
			if (mysqli_connect_error()) {
	   			 die('Connect Error (' . mysqli_connect_errno() . ') '
	            		. mysqli_connect_error());
			}
		}
		//Returns result object which has num_rows property can close() method
		public function query($sSQL) {
			return $this->mysqli->query($sSQL);
		}

		public function __destruct( ) {
			try {
				$this->mysqli->close();
			}
			catch(Exception $e) {
				//mysqli object not created to be able to close.
			}
		}
	}

?>
