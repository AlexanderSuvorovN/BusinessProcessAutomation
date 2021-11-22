<?php
	require_once("./../../st.php");
	$json = array();
	$task_id = $_REQUEST['task_id'] ?? null;
	$comment_text = $_REQUEST['comment_text'] ?? null;
	$task_id = filter_var($task_id, FILTER_SANITIZE_NUMBER_INT);
	if($task_id === null || $task_id < 0)
	{
		$json['status'] = "error";
		$json['message'] = "invalid identifier for a task.";
		echo json_encode($json);
		exit();
	}
	$comment_text = trim($comment_text);
	if($comment_text === '')
	{
		$json['status'] = 'error';
		$json['message'] = 'comment text is empty string.';
		echo json_encode($json);
		exit();
	}
	try
	{
		$st->db_connect('smartteams_business');
		$st->dbh->beginTransaction();		
		$s = $st->dbh->prepare("INSERT INTO `tasks_comments` (`task_id`, `user_id`, `comment`, `date_created`) VALUES (:task_id, :user_id, :comment, NOW())");
		$s->bindParam(':task_id', $task_id);
		$s->bindParam(':user_id', $st->user['id']);
		$s->bindParam(':comment', $comment_text);
		$s->execute();
		$message = 'Пользователь <a href=\'/users/view?id='.$st->user['id'].'\'>'.$st->user['person_firstname'].' '.$st->user['person_lastname'].'</a> добавил комментарий к задаче <a href=\'/tasks/view?id='.$task_id.'\'>'.$task_id.'</a>';
		$author = null;
		$s = $st->dbh->prepare("SELECT `created_by` FROM `tasks` WHERE `id` = :task_id");
		$s->bindParam(':task_id', $task_id);
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);
		if($fetch)
		{						
			$author = $fetch['created_by'];
			$s = $st->dbh->prepare("INSERT INTO `notifications` (`user_id`, `message`, `date_created`) VALUES (:user_id, :message, NOW())");
			$s->bindParam(':user_id', $fetch['created_by']);
			$s->bindParam(':message', $message);
			$s->execute();
		}
		$s = $st->dbh->prepare("SELECT `user_id` FROM `tasks_to_users` WHERE `task_id` = :task_id");
		$s->bindParam(':task_id', $task_id);
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch)
		{
			foreach($fetch as $assignee)
			{
				if($assignee !== $author) // избегаем повторное сообщение исполнителю, если автор и исполнитель один и тот же пользователь
				{
					$s = $st->dbh->prepare("INSERT INTO `notifications` (`user_id`, `message`, `date_created`) VALUES (:user_id, :message, NOW())");
					$s->bindParam(':user_id', $assignee['user_id']);
					$s->bindParam(':message', $message);
					$s->execute();
				}
			}
		}
		$st->dbh->commit();
		$st->db_close();
		$json['status'] = 'success';
		$json['message'] = 'new task comment has been successfully recorded in the database.';
		echo json_encode($json);
		exit();
	}
	catch(Exception $e)
	{
		$st->dbh->rollback();
		$st->db_close();
		$json['status'] = 'failed';
		$json['message'] = 'add new task comment operation failed:'.$e->getMessage();
		echo json_encode($json);
		exit();
	}