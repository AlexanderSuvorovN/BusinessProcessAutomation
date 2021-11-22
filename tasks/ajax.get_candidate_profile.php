<?php
	require_once("./../st.php");
	$json = array();
	$user_id = $_REQUEST['user_id'] ?? null;
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	if($user_id === null || $user_id < 0)
	{
		$json['status'] = "error";
		$json['message'] = "invalid input parameters";
		echo json_encode($json);
		exit();
	}
	$st->db_connect();
	$s = $st->dbh->prepare("SELECT `u`.`id` AS `user_id`, `u`.`status` AS `status`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `p`.`photo` AS `person_photo`, `r`.`name` AS `role_name`, `d`.`name` AS `department_name` FROM `users` AS `u` JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`) WHERE `u`.`id` = :user_id");
	$s->bindParam(":user_id", $user_id);
	$s->execute();
	$fetch = $s->fetch(PDO::FETCH_ASSOC);
	if($fetch)
	{
		$json['status'] = "success";
		$json['data'] = $fetch;
	}
	else
	{
		$json['status'] = 'failed';
		$json['message'] = 'no record found';
	}
	echo json_encode($json);