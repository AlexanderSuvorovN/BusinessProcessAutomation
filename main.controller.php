<?php
	require_once($st->config['thisDirPath']."/base.controller.php");
	class MainController extends BaseController
	{
		function __construct()
		{
		}
		public function test()
		{
			$this->db_connect();
    		$s = $this->dbh->prepare("SELECT * FROM `users`");
    		// $s->bindParam(":document_name", $document_item['name']);
    		$s->execute();
    		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
    		foreach($fetch as $row)
    		{
    			// var_dump($row);
    		}
			$this->db_close();
		}
	}
	$main = new MainController;