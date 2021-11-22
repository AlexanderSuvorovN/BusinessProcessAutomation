<?php 
	require_once("./../st.php");
	$grade_id = '1';
	$app_dir = 'app_dir';
	$dir = '/upload/';
	$filename = 'filename';
	$ext = 'ext';
	$size = 0;
	$st->db_connect('smartteams_business');	
	$s = $st->dbh->prepare("INSERT INTO `grades_attachments` (`grade_id`, `app_dir`, `upload_dir`, `filename`, `ext`, `size`) VALUES (:grade_id, :app_dir, :upload_dir, :filename, :ext, :size)");
	$s->bindParam(':grade_id', $grade_id, PDO::PARAM_INT);
	$s->bindParam(':app_dir', $app_dir, PDO::PARAM_STR);
	$s->bindParam(':upload_dir', $dir, PDO::PARAM_STR);
	$s->bindParam(':filename', $filename, PDO::PARAM_STR);
	$s->bindParam(':ext', $ext, PDO::PARAM_STR);
	$s->bindParam(':size', $size, PDO::PARAM_INT);
	$s->execute();
	$st->db_close();
	echo 'complete';