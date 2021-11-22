<?php
	require_once("./../st.php");
	$json = array();
	$task_id = $_REQUEST['task_id'] ?? null;
	$review_rate = $_REQUEST['review_rate'] ?? null;
	$review_comment = $_REQUEST['review_comment'] ?? null;
	$preg_pattern = '/^[0-9]+$/i';
	$preg_source = $task_id;
	$preg_match = preg_match($preg_pattern, $preg_source, $matches);
	if($preg_match !== 1)
	{
		$json['status'] = "error";
		$json['message'] = "invalid identifier for a task.";
		echo json_encode($json);
		exit();
	}
	$preg_pattern = '/^[0-5](\.(0|5|00|50))?$/i';
	$preg_source = $review_rate;
	$preg_match = preg_match($preg_pattern, $preg_source, $matches);
	if($preg_match !== 1)
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid value for the task review rate: '.$review_rate;
		echo json_encode($json);
		exit();
	}
	$review_comment = trim($review_comment);
	if($review_comment === '')
	{
		$json['status'] = 'error';
		$json['message'] = 'comment for the task can not be empty string.';
		echo json_encode($json);
		exit();
	}
	try
	{
		$st->db_connect('smartteams_business');
		$s = $st->dbh->prepare("UPDATE `tasks` SET `status` = 'reviewed_by_author', `author_review_rate` = :review_rate, `author_review_comment` = :review_comment WHERE `id` = :task_id");
		$s->bindParam(':task_id', $task_id);
		$s->bindParam(':review_rate', $review_rate);
		$s->bindParam(':review_comment', $review_comment);
		$s->execute();
		$st->db_close();
	}
	catch(Exception $e)
	{
		$json['status'] = 'failed';
		$json['message'] = 'task review by author operation failed:'.$e->getMessage();
		echo json_encode($json);
		exit();
	}
	$json['status'] = 'success';
	$json['message'] = 'status of the task '.$task_id.' has been successfully set to "reviewed_by_author".';
	echo json_encode($json);