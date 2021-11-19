<?php
	class BaseController
	{
		public function db_connect()
		{
			try
			{
				$this->dbh = new PDO("mysql:host=localhost;dbname=smartteams_business;charset=utf8", "smartteams_business", "cj6iajw6oKRmq7S7");
		    	$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    	//echo "Connected successfully<br>";
			}
			catch(PDOException $e)
			{
			    echo "Database connection failed: " . $e->getMessage() . "<br>";
			}
		}
		public function db_close()
		{
			$this->dbh = null;
		}
	}