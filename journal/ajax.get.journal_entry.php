<?php 
	require_once("./../st.php");
	$user_id = $_REQUEST['user_id'] ?? null;
	$date = $_REQUEST['date'] ?? null;
	$criteria = $_REQUEST['criteria'] ?? null;
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
	$preg_pattern = '/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/i';
	if(preg_match($preg_pattern, $date) !== 1)
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid value for "date" parameter.';
		echo json_encode($json);
		exit();
	}
	if(empty($criteria))
	{
		$criteria = null;
	}
	$json = array();
	$json['data'] = array();
	try
	{
		// подключение к базе данных
		$st->db_connect('smartteams_business');
		// выбор записей оценок из базы данных
		if($criteria !== null)
		{
			$filter_criteria = '`criteria` = :criteria';
		}
		else
		{
			$filter_criteria = '`criteria` IS NULL';
		}
		$s = $st->dbh->prepare("SELECT `id`, `date`, `comment`, `criteria`, `grade` FROM `journal_entries` WHERE `user_id` = :user_id AND `date` = :date AND ".$filter_criteria);
		$s->bindParam(':user_id', $user_id, PDO::PARAM_INT);
		$s->bindParam(':date', $date, PDO::PARAM_STR);
		if($criteria !== null)
		{
			$s->bindParam(':criteria', $criteria, PDO::PARAM_STR);
		}
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);
		if($fetch !== false)
		{			
			$json['data']['journal_entry'] = $fetch;
			$journal_entry_id = intval($fetch['id']);
			$s = $st->dbh->prepare("SELECT `upload_dir`, `filename`, `ext` FROM `journal_entries_attachments` WHERE `journal_entry_id` = :journal_entry_id");
			$s->bindParam(':journal_entry_id', $journal_entry_id);
			$s->execute();
			$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
			if($fetch !== false)
			{
				$json['data']['journal_entry']['attachments'] = $fetch;
			}
			else
			{
				throw new Exception('error while fetching attachments for the journal entry');
			}
			// возвращение значения AJAX
			$st->db_close();
			$json['status'] = 'success';
			$json['message'] = 'Journal entry has been successfully fetched.';
			echo json_encode($json);
			exit();
		}
		else
		{
			$json['data']['journal_entry'] = null;
			// возвращение значения AJAX
			$st->db_close();
			$json['status'] = 'success';
			$json['message'] = 'Journal entry has been successfully fetched.';
			echo json_encode($json);
			exit();
		}
	}
	catch(Exception $e)
	{
		$st->db_close();
		$json['status'] = 'error';
		$json['message'] = 'Get journal entry operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}