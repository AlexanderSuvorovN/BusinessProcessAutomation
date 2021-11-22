<?php 
	require_once("./../st.php");
	$is_error = false;
	$json = array();
	$new_task = array();
	// var_export($_REQUEST);
	$new_task['name'] = $_REQUEST['name'] ?? null;
	$new_task['description'] = $_REQUEST['description'] ?? null;
	$new_task['date_begin'] = $_REQUEST['date_begin'] ?? null;
	$new_task['date_end'] = $_REQUEST['date_end'] ?? null;
	$new_task['assignees'] = $_REQUEST['assignees'] ?? null;
	$new_task['created_by'] = $st->user['id'];
	$new_task['name'] = filter_var($new_task['name'], FILTER_SANITIZE_STRING);
	$new_task['name'] = trim($new_task['name']);
	$new_task['description'] = trim($new_task['description']);
	$new_task['date_begin'] = trim($new_task['date_begin']);
	$new_task['date_end'] = trim($new_task['date_end']);
	$preg_pattern_date = '/^[0-9]{4}\-[0-9]{1,2}-[0-9]{1,2}\s[0-9]{2}\:[0-9]{2}$/i';
	if(empty($new_task['name']))
	{
		$is_error = true;
		$json['status'] = 'error';
		$json['message'] = 'field "name" can not be empty';
		echo json_encode($json);
		exit();
	}
	if(!preg_match($preg_pattern_date, $new_task['date_begin']))
	{
		$is_error = true;
		$json['status'] = 'error';
		$json['message'] = 'incorrect format of "date_begin" value';
		echo json_encode($json);
		exit();
	}
	if(!preg_match($preg_pattern_date, $new_task['date_end']))
	{
		$is_error = true;
		$json['status'] = 'error';
		$json['message'] = 'incorrect format of "date_end" value';
		echo json_encode($json);
		exit();
	}
	try
	{
		$st->db_connect();
		$st->dbh->beginTransaction();
		// создадим запись задачи в базе данных
		$s = $st->dbh->prepare("INSERT INTO `tasks` (`name`, `description`, `date_created`, `date_begin`, `date_end`, `created_by`, `status`) VALUES(:name, :description, NOW(), :date_begin, :date_end, :created_by, 'created')");
		$s->bindParam(':name', $new_task['name']);
		$s->bindParam(':description', $new_task['description']);
		$s->bindParam(':date_begin', $new_task['date_begin']);
		$s->bindParam(':date_end', $new_task['date_end']);
		$s->bindParam(':created_by', $new_task['created_by']);
		$s->execute();
		// выберем из базы данных код созданной записи задачи
		$s = $st->dbh->prepare('SELECT LAST_INSERT_ID() AS `task_id`');
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);		
		if($fetch)
		{
			$new_task['id'] = $fetch['task_id'];
		}
		else
		{
			throw new Exception('error while fetching "id" of the new task');
		}
		$message = 'Пользователь <a href=\'/users/view?id='.$new_task['created_by'].'\'>'.$st->user['person_firstname'].' '.$st->user['person_lastname'].'</a> назначил Вам задачу <a href=\'/tasks/view?id='.$new_task['id'].'\'>'.$new_task['id'].'</a>';
		if(!empty($new_task['assignees']))
		{
			$s = $st->dbh->prepare("UPDATE `tasks` SET `status` = 'assigned' WHERE `id` = :new_task_id");
			$s->bindParam(':new_task_id', $new_task['id']);
			$s->execute();
			$dto = new DateTime();
			foreach($new_task['assignees'] as $assignee)
			{
				$s = $st->dbh->prepare("INSERT INTO `tasks_to_users` (`task_id`, `user_id`) VALUES(:new_task_id, :user_id)");
				$s->bindParam(':new_task_id', $new_task['id']);
				$s->bindParam(':user_id', $assignee);
				$s->execute();
				// для каждого исполнителя создадим запись оповещения в базе данных
				$s = $st->dbh->prepare("INSERT INTO `notifications` (`user_id`, `message`, `date_created`) VALUES (:user_id, :message, NOW())");
				$s->bindParam(':user_id', $assignee);
				$s->bindParam(':message', $message);
				$s->execute();
			}
		}
		$st->dbh->commit();
		$st->db_close();
		$json['status'] = 'success';
		$json['message'] = 'record has been successfully added.';
		echo json_encode($json);
	}
	catch(Exception $e)
	{
		$st->dbh->rollback();
		$st->db_close();
		$json['status'] = 'failed';
		$json['message'] = 'Add new task operation failed:'.$e->getMessage();
		echo json_encode($json);
		exit();
	}