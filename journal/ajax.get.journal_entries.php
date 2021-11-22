<?php 
	require_once("./../st.php");
	$user_id = $_REQUEST['user_id'] ?? null;
	$month = $_REQUEST['month'] ?? null;
	$year = $_REQUEST['year'] ?? null;
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
	$preg_pattern = '/[1-9]|11|12/i';
	if(preg_match($preg_pattern, $month) !== 1)
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid value for "month" parameter.';
		echo json_encode($json);
		exit();
	}
	$preg_pattern = '/[0-9]{1,4}/i';
	if(preg_match($preg_pattern, $year) !== 1)
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid value for "year" parameter.';
		echo json_encode($json);
		exti();
	}
	$dto_begin = new DateTime($year.'-'.$month.'-01');
	$dto_end = new DateTime($dto_begin->format('Y-m-t'));
	$filter_date_begin = $dto_begin->format('Y-m-d');
	$filter_date_end = $dto_end->format('Y-m-d');
	// echo $filter_date_begin;
	// echo $filter_date_end;
	$json = array();
	$json['data'] = array();
	try
	{
		// подключение к базе данных
		$st->db_connect('smartteams_business');
		// выбор записей оценок из базы данных
		$s = $st->dbh->prepare("SELECT `id`, `date`, `comment`, `criteria`, `grade` FROM `journal_entries` WHERE `user_id` = :user_id AND `date` >= :filter_date_begin AND `date` <= :filter_date_end ORDER BY `criteria` ASC, `date` ASC");
		$s->bindParam(':user_id', $user_id, PDO::PARAM_INT);
		$s->bindParam(':filter_date_begin', $filter_date_begin, PDO::PARAM_STR);
		$s->bindParam(':filter_date_end', $filter_date_end, PDO::PARAM_STR);
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch !== false)
		{
			$journal_entries = $fetch;
			foreach($journal_entries as &$entry)
			{
				$s = $st->dbh->prepare("SELECT COUNT(*) AS `attachments_count` FROM `journal_entries_attachments` WHERE `journal_entry_id` = :journal_entry_id");
				$s->bindValue(':journal_entry_id', intval($entry['id']), PDO::PARAM_INT);
				$s->execute();
				$fetch = $s->fetch(PDO::FETCH_ASSOC);
				if($fetch !== false)
				{
					$entry['attachments_count'] = $fetch['attachments_count'];
				}
				else
				{
					throw new Exception('can not fetch attachments count for a journal entry.');
				}
			}
			$json['data']['journal_entries'] = $journal_entries;
			$s = $st->dbh->prepare("SELECT `criteria` FROM `journal_entries` WHERE `user_id` = :user_id GROUP BY `criteria` ORDER BY `criteria` ASC");
			$s->bindParam(':user_id', $user_id, PDO::PARAM_INT);
			$s->execute();
			$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
			if($fetch !== false)
			{
				$json['data']['criteria_entries'] = $fetch;
			}
			else
			{
				throw new Exception('can not fetch criteria entries.');
			}
			// возвращение значения AJAX
			$st->db_close();
			$json['status'] = 'success';
			$json['message'] = 'Grades have been successfully fetched.';
			echo json_encode($json);
			exit();
		}
		else
		{
			throw new Exception('can not fetch grades for the user and filter specified');
		}
	}
	catch(Exception $e)
	{
		$st->db_close();
		$json['status'] = 'error';
		$json['message'] = 'Get grades operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}