<?php
	require_once("./../st.php");
	function fetchTaskAssignees($task_id)
	{

		$s = $GLOBALS['st']->dbh->prepare("SELECT `u`.`id` AS `user_id`, `u`.`status` AS `user_status`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `p`.`photo` AS `person_photo`, `r`.`name` AS `role_name`, `d`.`name` AS `department_name` FROM `tasks_to_users` AS `t2u` JOIN `users` AS `u` ON (`t2u`.`user_id` = `u`.`id`) JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`) WHERE `t2u`.`task_id` = :task_id");
		$s->bindParam(":task_id", $task_id);
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch)
		{
			$assignees = $fetch;
		}
		else
		{
			$assignees = array();
		}
		return $assignees;
	}
	function fetchTaskComments($task_id)
	{
		$s = $GLOBALS['st']->dbh->prepare("SELECT COUNT(*) AS `count` FROM `tasks_comments` AS `tc` WHERE `tc`.`task_id` = :task_id");
		$s->bindParam(":task_id", $task_id);
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);
		if($fetch)
		{
			$comments_count = $fetch['count'];
		}
		else
		{
			$comments_count = 0;
		}
		return $comments_count;
	}
	function addFilter($query)	
	{
		$s = null;
		if($GLOBALS['filter']['predefined_filter'] === '')
		{
			if($GLOBALS['filter']['status'] !== 'all')
			{
				$query .= " AND `t`.`status` = :filter_status";
				$s = $GLOBALS['st']->dbh->prepare($query);
				$s->bindParam(':user_id', $GLOBALS['user_id'], PDO::PARAM_INT);
				$s->bindParam(':filter_status', $GLOBALS['filter']['status'], PDO::PARAM_STR);
			}
			else
			{
				$s = $GLOBALS['st']->dbh->prepare($query);
				$s->bindParam(':user_id', $GLOBALS['user_id'], PDO::PARAM_INT);
			}
		}
		else
		{
			if($GLOBALS['filter']['predefined_filter'] === 'in_progress')
			{
				$query .= " AND `t`.`status` IN ('assigned', 'implementation')";
			}
			if($GLOBALS['filter']['predefined_filter'] === 'completed')
			{
				$query .= " AND (`t`.`date_completed` IS NOT NULL)";
			}
			if($GLOBALS['filter']['predefined_filter'] === 'completed_on_time')
			{
				$query .= " AND (`t`.`date_completed` IS NOT NULL AND `t`.`date_completed` <= `t`.`date_end`)";
			}
			if($GLOBALS['filter']['predefined_filter'] === 'completed_overdue')
			{
				$query .= " AND (`t`.`date_completed` IS NOT NULL AND `t`.`date_completed` >= `t`.`date_end`)";
			}
			if($GLOBALS['filter']['predefined_filter'] === 'overdue')
			{
				$query .= " AND ((`t`.`date_completed` IS NULL AND NOW() > `t`.`date_end`) OR (`t`.`date_completed` IS NOT NULL AND `t`.`date_completed` > `t`.`date_end`))";
			}
			$s = $GLOBALS['st']->dbh->prepare($query);
			$s->bindParam(':user_id', $GLOBALS['user_id'], PDO::PARAM_INT);
		}
		return $s;
	}
	function processFetch($fetch)
	{
		$data = array();
		foreach($fetch as $row)
		{
			$row['task_status_display'] = $GLOBALS['st']->mb_ucfirst($GLOBALS['st']->task_status_map[$row['task_status']]['display']);
			$row['task_date_begin'] = $GLOBALS['st']->dateSplit($row['task_date_begin']);
			$row['task_date_end'] = $GLOBALS['st']->dateSplit($row['task_date_end']);
			if($row['task_date_completed'] !== NULL)
			{
				$row['task_date_completed'] = $GLOBALS['st']->dateSplit($row['task_date_completed']);
			}
			else
			{
				$row['task_date_completed']['datetime_html'] = '-';
			}
			$row['task_assignees'] = fetchTaskAssignees($row['task_id']);
			$row['task_comments_count'] = fetchTaskComments($row['task_id']);
			$data[] = $row;
		}
		return $data;
	}
	$json = array();
	$user_id = $_REQUEST['user_id'] ?? $st->user['id'];
	$filter = $_REQUEST['filter'] ?? null;
	$preg_pattern = '/^[0-9]+$/i';
	$preg_match = preg_match($preg_pattern, $user_id);
	if($preg_match !== 1)
	{
		$json['status'] = "error";
		$json['message'] = "invalid identifier for user id.";
		echo json_encode($json);
		exit();
	}
	if($filter === null)
	{
		$filter = array();
		$filter['author_assignee'] = 'all';
		$filter['status'] = 'all';
		$filter['predefined_filter'] = '';
	}
	$valid_author_assignee = ['author', 'assignee', 'all'];
	if(!(isset($filter['author_assignee']) && in_array($filter['author_assignee'], $valid_author_assignee)))
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid value for "author_assignee" filter';
		echo json_encode($json);
		exit();
	}
	$valid_status = ['created', 'assigned', 'declined', 'implementation', 'completed', 'cancelled', 'removed', 'aborted', 'reviewed_by_author', 'reviewed_by_assignee', 'completed2', 'closed', 'all'];
	if(!(isset($filter['status']) && in_array($filter['status'], $valid_status)))
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid value for "status" filter';
		echo json_encode($json);
		exit();
	}
	$user_id = intval($user_id);
	$data['tasks'] = array();
	try
	{
		$st->db_connect('smartteams_business');
		if(in_array($filter['author_assignee'], ['author', 'all']))
		{
			$query = "SELECT `t`.`id` AS `task_id`, `t`.`name` AS `task_name`, `t`.`status` AS `task_status`, `t`.`date_begin` AS `task_date_begin`, `t`.`date_end` AS `task_date_end`, `t`.`progress` AS `task_progress`, `t`.`date_started` AS `task_date_started`, `t`.`date_completed` AS `task_date_completed`, `t`.`created_by` AS `task_created_by`, `p`.`photo` AS `author_person_photo`, `p`.`firstname` AS `author_person_firstname`, `p`.`lastname` AS `author_person_lastname`, `u`.`status` AS `author_user_status`, `r`.`name` AS `author_role_name`, `d`.`name` AS `author_department_name` FROM `tasks` AS `t` JOIN `users` AS `u` ON (`t`.`created_by` = `u`.`id`) JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`) WHERE `t`.`created_by` = :user_id";
			$s = addFilter($query);
			$s->execute();
			$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
			if($fetch)
			{
				$data['tasks'] = processFetch($fetch);
			}
		}
		if(in_array($filter['author_assignee'], ['assignee', 'all']))
		{
			$query = "SELECT `t`.`id` AS `task_id`, `t`.`name` AS `task_name`, `t`.`status` AS `task_status`, `t`.`date_begin` AS `task_date_begin`, `t`.`date_end` AS `task_date_end`, `t`.`progress` AS `task_progress`, `t`.`date_started` AS `task_date_started`, `t`.`date_completed` AS `task_date_completed`, `t`.`created_by` AS `task_created_by`, `p`.`photo` AS `author_person_photo`, `p`.`firstname` AS `author_person_firstname`, `p`.`lastname` AS `author_person_lastname`, `u`.`status` AS `author_user_status`, `r`.`name` AS `author_role_name`, `d`.`name` AS `author_department_name` FROM `tasks` AS `t` JOIN `users` AS `u` ON (`t`.`created_by` = `u`.`id`) JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`) WHERE `t`.`id` IN (SELECT `task_id` FROM `tasks_to_users` AS `t2u` WHERE `t2u`.`user_id` = :user_id)";
			$s = addFilter($query);
			$s->execute();
			$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
			if($fetch)
			{
				$data['tasks'] = processFetch($fetch);
			}
		}
		$st->db_close();
	}
	catch(Exception $e)
	{
		$json['status'] = 'failed';
		$json['message'] = 'Get tasks operation failed:'.$e->getMessage();
		echo json_encode($json);
		exit();
	}
	$_SESSION['ui.tasks.controls.user'] = $user_id;
	$_SESSION['ui.tasks.controls.author_assignee'] = $filter['author_assignee'];
	$_SESSION['ui.tasks.controls.status'] = $filter['status'];
	$json['status'] = 'success';
	$json['message'] = 'Tasks have been successfully fetched.';
	$json['data'] = $data;
	echo json_encode($json);