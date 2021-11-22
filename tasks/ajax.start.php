<?php
	require_once("./../st.php");
	$json = array();
	$task_id = $_REQUEST['task_id'] ?? null;
	$preg_pattern = '/^[0-9]+$/i';
	$preg_source = $task_id;
	$preg_match = preg_match($preg_pattern, $preg_source);
	if($preg_match !== 1)
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid identifier for a task.';
		echo json_encode($json);
		exit();
	}
	try
	{
		$st->db_connect('smartteams_business');
		$st->dbh->beginTransaction();
		// обновим статус задачи в базе данных
		$s = $st->dbh->prepare("UPDATE `tasks` SET `status`='implementation', `date_started`=NOW() WHERE `id`=:task_id");
		$s->bindParam(':task_id', $task_id);
		$s->execute();
		// создадим оповещение автору задачи
		// вначале сформируем текст сообщения
		$message = 'Пользователь <a href=\'/users/view?id='.$st->user['id'].'\'>'.$st->user['person_firstname'].' '.$st->user['person_lastname'].'</a> начал выполнение задачи <a href=\'/tasks/view?id='.$task_id.'\'>'.$task_id.'</a>';
		// затем, определяем идентификатор автора задачи
		$s = $st->dbh->prepare("SELECT `created_by` FROM `tasks` WHERE `id` = :task_id");
		$s->bindParam(':task_id', $task_id);
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);
		// если запись автора найдена в базе данных, то...
		if($fetch)
		{
			if($fetch['created_by'] !== $st->user['id']) // ...если автор задачи и исполнитель не один и тот же пользователь
			{
				// создаём уведомление в базе данных
				$s = $st->dbh->prepare("INSERT INTO `notifications` (`user_id`, `message`, `date_created`) VALUES (:user_id, :message, NOW())");
				$s->bindParam(':user_id', $fetch['created_by']);
				$s->bindParam(':message', $message);
				$s->execute();
			}
		}
		$st->dbh->commit();
		$st->db_close();
		$json['status'] = 'success';
		$json['message'] = 'status of task '.$task_id.' has been successfully set to "implementation".';
		echo json_encode($json);
		exit();
	}
	catch(Exception $e)
	{
		$st->dbh->rollback();
		$st->db_close();
		$json['status'] = 'failed';
		$json['message'] = 'start task operation failed:'.$e->getMessage();
		echo json_encode($json);
		exit();
	}