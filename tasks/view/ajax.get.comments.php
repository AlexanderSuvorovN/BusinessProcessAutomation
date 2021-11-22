<?php
	require_once("./../../st.php");
	$json = array();
	$task_id = $_REQUEST['task_id'] ?? null;
	$task_id = filter_var($task_id, FILTER_SANITIZE_NUMBER_INT);
	if($task_id === null || $task_id < 0)
	{
		$json['status'] = "error";
		$json['message'] = "invalid input parameters";
		echo json_encode($json);
		exit();
	}
	$st->db_connect('smartteams_business');
	$s = $st->dbh->prepare("SELECT `tc`.`id` AS `task_comment_id`, `tc`.`date_created` AS `task_comment_date_created`, `u`.`status` AS `user_status`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `p`.`photo` AS `person_photo`, `r`.`name` AS `role_name`, `d`.`name` AS `department_name`, `tc`.`comment` AS `task_comment` FROM `tasks_comments` AS `tc` JOIN `users` AS `u` ON (`tc`.`user_id` = `u`.`id`) JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`) WHERE `tc`.`task_id` = :task_id");
	$s->bindParam(":task_id", $task_id);
	$s->execute();
	$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
	if($fetch)
	{
		foreach($fetch as &$comment)
		{
			$comment['task_comment_date_created'] = $st->dateSplit($comment['task_comment_date_created']);
		}
		$json['status'] = 'success';
		$json['message'] = 'records have been succesfully fetched.';
		$json['data'] = $fetch;
		echo json_encode($json);
	}
	else
	{
		$json['status'] = 'failed';
		$json['message'] = 'no records found';
		echo json_encode($json);
	}