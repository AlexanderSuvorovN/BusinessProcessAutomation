<?php 
	require_once("./../../st.php");
	$user_id = $_REQUEST['user_id'] ?? null;
	if($user_id === '')
	{
		$user_id = null;
	}
	if($user_id !== null)
	{		
		$preg_pattern = '/[0-9]+/i';
		if(preg_match($preg_pattern, $user_id) !== 1)
		{
			$json['status'] = 'error';
			$json['message'] = 'invalid value for "user_id" parameter: '.$user_id;
			$json['user_id'] = $user_id;
			echo json_encode($json);
			exit();
		}
		$user_id = intval($user_id);
	}
	$json = array();
	$json['data'] = array();
	try
	{
		// подключение к базе данных
		$st->db_connect('smartteams_business');
		$query_template = "SELECT `u`.`id` AS `user_id`, `u`.`status` AS `user_status`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `p`.`photo` AS `person_photo`, `r`.`name` AS `role_name`, `d`.`name` AS `department_name` FROM `users` AS `u` JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`) %s ORDER BY `person_lastname` ASC, `person_firstname` ASC";		
		if($user_id !== null)
		{
			$query = sprintf($query_template, "WHERE `u`.`id` = :user_id");
			$s = $st->dbh->prepare($query);
			$s->bindParam(':user_id', $user_id, PDO::PARAM_INT);
		}
		else
		{
			$query = sprintf($query_template, "");
			$s = $st->dbh->prepare($query);			
		}
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch)
		{
			if(count($fetch) > 0)
			{
				$json['data']['stats'] = array();			
				// получим данные задач для каждого пользователя
				foreach($fetch as $row)
				{
					$record = [];
					$record['user_id'] = intval($row['user_id']);
					$record['user_status'] = $row['user_status'];
					$record['person_firstname'] = $row['person_firstname'];
					$record['person_lastname'] = $row['person_lastname'];
					$record['person_photo'] = $row['person_photo'];
					$record['role_name'] = $row['role_name'];
					$record['department_name'] = $row['department_name'];
					$record['count'] = array();
					$base_query = "SELECT COUNT(`t`.`id`) AS `count` FROM `tasks` AS `t` JOIN `tasks_to_users` AS `t2u` ON (`t`.`id` = `t2u`.`task_id`) WHERE `t2u`.`user_id` = :user_id";
					$queries = [];
					$queries['in_progress'] = $base_query." AND `t`.`status` IN ('assigned', 'implementation')";
					$queries['completed'] = $base_query." AND (`t`.`date_completed` IS NOT NULL)";
					$queries['completed_on_time'] = $base_query." AND (`t`.`date_completed` IS NOT NULL AND `t`.`date_completed` <= `t`.`date_end`)";
					$queries['completed_overdue'] = $base_query." AND (`t`.`date_completed` IS NOT NULL AND `t`.`date_completed` >= `t`.`date_end`)";
					$queries['overdue'] = $base_query." AND ((`t`.`date_completed` IS NULL AND NOW() > `t`.`date_end`) OR (`t`.`date_completed` IS NOT NULL AND `t`.`date_completed` > `t`.`date_end`))";
					foreach($queries as $key => $val)
					{
						$s = $st->dbh->prepare($val);
						$s->bindParam(':user_id', $record['user_id'], PDO::PARAM_INT);
						$s->execute();
						$fetch = $s->fetch(PDO::FETCH_ASSOC);
						if($fetch )
						{
							$record['count'][$key] = intval($fetch['count']);
						}
						else
						{
							$record['count'][$key] = 0;
						}
					}
					$json['data']['stats'][] = $record;
				}
			}
			else
			{
				$json['data']['stats'] = null;
			}
			// возвращение значения AJAX
			$st->db_close();
			$json['status'] = 'success';
			$json['message'] = 'Tasks statistics records have been successfully fetched.';
			echo json_encode($json);
			exit();
		}
		else
		{
			throw new Exception('Can not fetch list of users.');
		}
	}
	catch(Exception $e)
	{
		$st->db_close();
		$json['status'] = 'error';
		$json['message'] = 'Get tasks statistics operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}