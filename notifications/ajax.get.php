<?php 
	require_once("./../st.php");
	$is_error = false;
	$json = array();
	try
	{
		$st->db_connect();
		$s = $st->dbh->prepare("SELECT `id`, `message`, `status`, `date_created` FROM `notifications` WHERE `status` = 'unread' AND `user_id` = :user_id ORDER BY `date_created` DESC");
		$s->bindParam(':user_id', $st->user['id']);
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch !== false)
		{
			$json['status'] = 'success';
			$json['message'] = 'notifications have been successfully fetched.';
			$json['data'] = $fetch;
			$st->db_close();
			echo json_encode($json);
		}
		else
		{
			throw new Exception('error during fetch notifications.');
		}
	}
	catch(Exception $e)
	{
		$json['status'] = 'error';
		$json['message'] = 'Get notifications operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}