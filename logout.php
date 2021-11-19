<?php
	require_once("./st.php");
	$st->db_connect();
	$s = $st->dbh->prepare("UPDATE `users` SET `status` = 'offline' WHERE `id` = :user_id");
	$s->bindParam(":user_id", $st->user['id']);
	$s->execute();
	$st->db_close();
	$st->SetAuth(false, ['redirect' => true]);