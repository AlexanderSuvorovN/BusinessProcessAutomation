<?php
	require_once("./st.php");
	$json = array();
	$email = $_REQUEST['email'] ?? null;
	$password = $_REQUEST['password'] ?? null;
	$remember = $_REQUEST['remember'] ?? null;
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);
	if(!$email || !$password)
	{
		$json['status'] = "error";
		$json['message'] = "invalid input parameters";
		echo json_encode($json);
		exit();
	}
	// fix this: vulnerable to brute force
	// https://www.php.net/manual/en/faq.passwords.php#faq.passwords.fasthash
	$md5 = md5($password);
	$st->db_connect();
	$s = $st->dbh->prepare("SELECT `id`, `email`, `password` FROM `users` WHERE `email` = :email AND `password` = :md5 LIMIT 1");
	$s->bindParam(":email", $email);
	$s->bindParam(":md5", $md5);
	$s->execute();
	$fetch = $s->fetch(PDO::FETCH_ASSOC);
	if($fetch)
	{
		$st->SetAuth(true, ['user_id' => $fetch['id'], 'remember' => $remember]);
		$json['status'] = "success";
	}
	else
	{
		$st->SetAuth(false, ['redirect' => false]);
		$json['status'] = "failed";
	}
	echo json_encode($json);