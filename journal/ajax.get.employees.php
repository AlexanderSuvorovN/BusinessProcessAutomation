<?php 
	require_once("./../st.php");
	$json = array();
	$json['data'] = array();
	try
	{
		// подключение к базе данных
		$st->db_connect('smartteams_business');
		// выбор записей оценок из базы данных
		$s = $st->dbh->prepare("SELECT `u`.`id` AS `user_id`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname` FROM `users` AS `u` JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`)");
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch !== false)
		{
			$json['data']['employees'] = $fetch;
			// возвращение значения AJAX
			$st->db_close();
			$json['status'] = 'success';
			$json['message'] = 'List of employees has been successfully fetched.';
			echo json_encode($json);
			exit();
		}
		else
		{
			throw new Exception('can not fetch list of employees from the database.');
		}
	}
	catch(Exception $e)
	{
		$st->db_close();
		$json['status'] = 'error';
		$json['message'] = 'Get list of employees operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}