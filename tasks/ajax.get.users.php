<?php 
	require_once("./../st.php");
	$json = array();
	$json['data'] = array();
	try
	{
		// подключение к базе данных
		$st->db_connect('smartteams_business');
		$s = $st->dbh->prepare("SELECT `u`.`id` AS `user_id`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname` FROM `users` AS `u` JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`)");
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch !== false)
		{
			$json['data']['users'] = $fetch;
			// возвращение значения AJAX
			$st->db_close();
			$json['status'] = 'success';
			$json['message'] = 'List of users have been successfully fetched.';
			echo json_encode($json);
			exit();
		}
		else
		{
			throw new Exception('can not fetch list of users from the database.');
		}
	}
	catch(Exception $e)
	{
		$st->db_close();
		$json['status'] = 'error';
		$json['message'] = 'Get list of users operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}