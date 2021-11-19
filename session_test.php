<?php
	if(session_status() == PHP_SESSION_NONE)
	{
		session_start();
		$_SESSION["started"] = "true";
		echo "session started<br>";
		var_dump($_SESSION);
	}
	else
	{
		exit();
	}