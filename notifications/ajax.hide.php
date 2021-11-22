<?php 
	require_once("./../st.php");
	$json = array();
	$notification_id = $_REQUEST['notification_id'] ?? null;
	// проверка кода оповещения
	$preg_pattern = '/^[0-9]+$/i';
	$preg_source = $notification_id;
	$preg_match = preg_match($preg_pattern, $preg_source, $matches);
	if($preg_match !== 1)
	{
		$json['status'] = "error";
		$json['message'] = "invalid identifier for a notification.";
		echo json_encode($json);
		exit();
	}
	try
	{
		$st->db_connect();
		// чтение идентификатора пользователя из базы данных для проверки если оповещение назначено этому пользователю
		$s = $st->dbh->prepare("SELECT `user_id` FROM `notifications` WHERE `id` = :notification_id");
		$s->bindParam(':notification_id', $notification_id);
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);
		if($fetch)
		{
			// если запрос был получен от пользователя оповещения...
			if($st->user['id'] === $fetch['user_id'])
			{
				// ...то обновляем статус оповещения в базе данных
				$s = $st->dbh->prepare("UPDATE `notifications` SET `status` = 'read' WHERE `id` = :notification_id");
				$s->bindParam(':notification_id', $notification_id);
				$s->execute();
				// возвращение AJAX
				$json['status'] = 'success';
				$json['message'] = 'notification has been successfully updated.';
				$st->db_close();
				echo json_encode($json);
				exit();
			}
			else
			{
				throw new Exception('notification user id does not match to user id of the requester');
			}
		}
	}
	catch(Exception $e)
	{
		$st->db_close();
		$json['status'] = 'error';
		$json['message'] = 'Hide notifications operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}