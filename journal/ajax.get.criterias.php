<?php 
	require_once("./../st.php");
	$user_id = $_REQUEST['user_id'] ?? null;
	if($user_id !== null)
	{
		$preg_pattern = '/[0-9]+/i';
		if(preg_match($preg_pattern, $user_id) !== 1)
		{
			$json['status'] = 'error';
			$json['message'] = 'invalid value for "user_id" parameter: '.$user_id;
			echo json_encode($json);
			exit();		
		}
		$user_id = intval($user_id);
	}
	else
	{
		$user_id = $st->user['id'];
	}	
	$json = array();
	$json['data'] = array();
	try
	{
		// подключение к базе данных
		$st->db_connect('smartteams_business');
		$s = $st->dbh->prepare("SELECT DISTINCT(`criteria`) FROM `journal_entries` WHERE `user_id` = :user_id");
		$s->bindParam(':user_id', $user_id, PDO::PARAM_INT);
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch !== false)
		{
			$json['data']['criterias'] = $fetch;
		}
		else
		{
			$json['data']['criterias'] = null;
		}
		// возвращение значения AJAX
		$st->db_close();
		$json['status'] = 'success';
		$json['message'] = 'Criterias have been successfully fetched.';
		echo json_encode($json);
		exit();
	}
	catch(Exception $e)
	{
		$st->db_close();
		$json['status'] = 'error';
		$json['message'] = 'Get criterias operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}