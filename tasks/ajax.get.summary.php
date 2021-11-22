<?php 
	require_once("./../st.php");
	$page_size = $_REQUEST['page_size'] ?? 5;
	$page_number = $_REQUEST['page_number'] ?? 0;
	$preg_pattern = '/[0-9]{1,2}/i';
	if(preg_match($preg_pattern, $page_size) !== 1)
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid value for "page_size" parameter.';
		echo json_encode($json);
		exit();
	}
	if(preg_match($preg_pattern, $page_number) !== 1)
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid value for "page_number" parameter.';
		echo json_encode($json);
		exti();
	}
	$json = array();
	$json['data'] = array();
	$json['data']['tasks'] = array();
	$json['data']['tasks']['count'] = array();
	try
	{
		// подключение к базе данных
		$st->db_connect('smartteams_business');
		// выборка количества созданных задач из базы данных
		$s = $st->dbh->prepare("SELECT COUNT(*) AS `count` FROM `tasks` WHERE `status` = 'created' AND `created_by` = :user_id");
		$s->bindParam(':user_id', $st->user['id'], PDO::PARAM_INT);
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);
		if($fetch !== false)
		{
			$json['data']['tasks']['count']['created'] = $fetch['count'];
		}
		else
		{
			throw new Exception('can not fetch count of created tasks from the database.');
		}
		$s = $st->dbh->prepare("SELECT `id`, `name`, `status`, `date_begin`, `date_end` FROM `tasks` WHERE `status` = 'created' AND `created_by` = :user_id LIMIT :page_number, :page_size");
		$s->bindParam(':user_id', $st->user['id'], PDO::PARAM_INT);
		$s->bindParam(':page_number', $page_number, PDO::PARAM_INT);
		$s->bindParam(':page_size', $page_size, PDO::PARAM_INT);
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch !== false)
		{
			$json['data']['tasks']['created'] = array();
			foreach($fetch as $row)
			{
				$row['status_display'] = $st->mb_ucfirst($st->task_status_map[$row['status']]['display']);
				$json['data']['tasks']['created'][] = $row;
			}
		}
		else
		{
			throw new Exception('can not fetch tasks in status "created" from the database.');
		}
		foreach(['assigned', 'implementation', 'reviewed_by_author'] as $status)
		{
			$s = $st->dbh->prepare("SELECT COUNT(*) AS `count` FROM `tasks` AS `t` JOIN `tasks_to_users` AS `t2u` ON (`t`.`id` = `t2u`.`task_id`) WHERE `t`.`status` = :status AND `t2u`.`user_id` = :user_id");
			$s->bindParam(':status', $status, PDO::PARAM_STR);
			$s->bindParam(':user_id', $st->user['id'], PDO::PARAM_INT);
			$s->execute();
			$fetch = $s->fetch(PDO::FETCH_ASSOC);
			if($fetch !== false)
			{
				$json['data']['tasks']['count'][$status] = $fetch['count'];
			}
			else
			{
				throw new Exception('can not fetch count of tasks in "'.$status.'" from the database.');
			}
			$s = $st->dbh->prepare("SELECT `t`.`id`, `t`.`name`, `t`.`status`, `t`.`date_begin`, `t`.`date_end` FROM `tasks` AS `t` JOIN `tasks_to_users` AS `t2u` ON (`t`.`id` = `t2u`.`task_id`) WHERE `status` = :status AND `t2u`.`user_id` = :user_id LIMIT :page_number, :page_size");
			$s->bindParam(':status', $status, PDO::PARAM_STR);
			$s->bindParam(':user_id', $st->user['id'], PDO::PARAM_INT);
			$s->bindParam(':page_number', $page_number, PDO::PARAM_INT);
			$s->bindParam(':page_size', $page_size, PDO::PARAM_INT);
			$s->execute();
			$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
			if($fetch !== false)
			{
				$json['data']['tasks'][$status] = array();
				foreach($fetch as $row)
				{
					$row['status_display'] = $st->mb_ucfirst($st->task_status_map[$row['status']]['display']);
					$json['data']['tasks'][$status][] = $row;
				}
			}
			else
			{
				throw new Exception('can not fetch tasks in status "'.$status.'" from the database.');
			}
		}
		// возвращение значения AJAX
		$st->db_close();
		$json['status'] = 'success';
		$json['message'] = 'Tasks summary has been successfully fetched.';
		echo json_encode($json);
		exit();
	}
	catch(Exception $e)
	{
		$st->db_close();
		$json['status'] = 'error';
		$json['message'] = 'Get tasks summary operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}