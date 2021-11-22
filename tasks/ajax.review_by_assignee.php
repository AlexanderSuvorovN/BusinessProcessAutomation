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
	$review_comment = trim($review_comment);
	if($review_comment === '')
	{
		$json['status'] = 'error';
		$json['message'] = 'comment for the review by assignee can not be empty string.';
		echo json_encode($json);
		exit();
	}	
	if(empty($review_rate))
	{
		$json['status'] = 'error';
		$json['message'] = 'S.M.A.R.T. rate must be specified.';
		echo $json_encode($json);
		exit();
	}
	foreach($review_rate as $key => $value)
	{
		if(!in_array($key, ['specific', 'measurable', 'attainable', 'relevant', 'timebound']))
		{
			$json['status'] = 'error';
			$json['message'] = '"'.$key.'" is not S.M.A.R.T. rate';
			echo json_encode($json);
			exit();
		}
		$preg_pattern = '/^[0-5](\.(0|5|00|50))?$/i';
		$preg_source = $value;
		$preg_match = preg_match($preg_pattern, $preg_source);
		if($preg_match !== 1)
		{
			$json['status'] = 'error';
			$json['message'] = 'invalid value for the task\'s "'.$key.'" rate: '.$value;
			echo json_encode($json);
			exit();
		}
	}
	try
	{
		$st->db_connect('smartteams_business');
		$s = $st->dbh->prepare("UPDATE `tasks` SET `status` = 'reviewed_by_assignee', `assignee_review_comment` = :review_comment, `assignee_review_specific` = :review_specific, `assignee_review_measurable` = :review_measurable, `assignee_review_attainable` = :review_attainable, `assignee_review_relevant` = :review_relevant, `assignee_review_timebound` = :review_timebound WHERE `id` = :task_id");
		$s->bindParam(':review_comment', $review_comment);
		$s->bindParam(':review_specific', $review_rate['specific']);
		$s->bindParam(':review_measurable', $review_rate['measurable']);
		$s->bindParam(':review_attainable', $review_rate['attainable']);
		$s->bindParam(':review_relevant', $review_rate['relevant']);
		$s->bindParam(':review_timebound', $review_rate['timebound']);
		$s->bindParam(':task_id', $task_id);
		$s->execute();
		$st->db_close();
	}
	catch(Exception $e)
	{
		$json['status'] = 'failed';
		$json['message'] = 'task review by assignee operation failed:'.$e->getMessage();
		echo json_encode($json);
		exit();
	}
	$json['status'] = 'success';
	$json['message'] = 'status of the task '.$task_id.' has been successfully set to "reviewed_by_assignee".';
	echo json_encode($json);