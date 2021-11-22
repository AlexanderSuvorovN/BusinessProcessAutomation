<?php
	require_once("./../st.php");
	$json = array();
	$task_id = $_REQUEST['task_id'] ?? null;
	$task_id = filter_var($task_id, FILTER_SANITIZE_NUMBER_INT);
	if($task_id === null || $task_id < 0)
	{
		$json['status'] = "error";
		$json['message'] = "invalid identifier for a task.";
		echo json_encode($json);
		exit();
	}
	try
	{
		$st->db_connect('smartteams_business');
		$s = $st->dbh->prepare("UPDATE `tasks` SET `status` = 'closed' WHERE `id` = :task_id");
		$s->bindParam(':task_id', $task_id);
		$s->execute();
		$st->db_close();
	}
	catch(Exception $e)
	{
		$json['status'] = 'failed';
		$json['message'] = 'close task operation failed:'.$e->getMessage();
		echo json_encode($json);
		exit();
	}
	$json['status'] = 'success';
	$json['message'] = 'status of the task '.$task_id.' has been successfully set to "closed".';
	echo json_encode($json);