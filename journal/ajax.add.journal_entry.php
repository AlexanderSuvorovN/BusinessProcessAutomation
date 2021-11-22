<?php 
	require_once("./../st.php");
	$upload = array();
	$upload['dir'] = '/files/grades';
	$upload['max_files'] = 10; // максимальный количество файлов
	$upload['max_size'] = 52428800; // максимальный размер файла: 50 мегабайт
	$upload['valid_ext'] = ['png', 'jpg', 'jpeg', 'pdf', 'tiff', 'bmp', 'svg', 'doc', 'docx', 'rtf', 'xls', 'xlsx', 'htm', 'html'];
	$upload['mode'] = 0766;
	$upload['app_dir'] = $st->config['mainDirPath'];
	$user_id = $_REQUEST['user_id'] ?? null;
	$date = $_REQUEST['date'] ?? null;
	$comment = $_REQUEST['comment'] ?? null;
	$criteria = $_REQUEST['criteria'] ?? null;
	$grade = $_REQUEST['grade'] ?? null;
	$remove = $_REQUEST['remove'] ?? null;
	$attachments = $_FILES['attachments'] ?? null;
	if($user_id !== null)
	{
		$preg_pattern = '/[0-9]+/i';
		if(preg_match($preg_pattern, $user_id) !== 1)
		{
			$json['status'] = 'error';
			$json['message'] = 'invalid value for "user_id" parameter: '.$user_id;
			echo json_encode($json);
			exit();		
		}
		$user_id = intval($user_id);
	}
	else
	{
		$user_id = $st->user['id'];
	}
	$preg_pattern = '/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/i';
	if(preg_match($preg_pattern, $date) !== 1)
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid value for "date" parameter.';
		echo json_encode($json);
		exit();
	}	
	if(empty($comment))
	{
		$comment = null;
	}
	if(empty($criteria))
	{
		$criteria = null;
	}
	$upload['dir'] .= '/'.$user_id.'/'.$date.'/';
	if($criteria !== null)
	{
		$upload['dir'] .= '/'.$criteria.'/';
	}
	if($attachments !== null)
	{
		if(count($attachments['name']) > $upload['max_files'])
		{
			$json['status'] = 'error';
			$json['message'] = 'no more than "'.$upload['max_files'].'" allowed';
			echo json_encode($json);
			exit();
		}
		$attachments['files'] = array();
		$attachments['json'] = array();
		for($i = 0; $i < count($attachments['name']); $i++)
		{
			$file = array();
			$file['basename'] = $attachments['name'][$i];
			$file['filename'] = (pathinfo($file['basename']))['filename'];
			$file['ext'] = (pathinfo($file['basename']))['extension'];
			$file['type'] = $attachments['type'][$i];
			$file['size'] = $attachments['size'][$i];
			$file['tmp'] = $attachments['tmp_name'][$i];
			$file['upload'] = $upload['app_dir'].'/'.$upload['dir'].'/'.$file['filename'].'.'.$file['ext'];
			if($file['size'] > $upload['max_size'])
			{
				$json['status'] = 'error';
				$json['message'] = 'file "'.$file['basename'].'" exceeds max file size of "'.$upload['max_size']."' bytes";
				echo json_encode($json);
				exit();
			}
			if(!in_array($file['ext'], $upload['valid_ext']))
			{
				$json['status'] = 'error';
				$json['message'] = 'file format "'.$file['ext'].'" is not supported';
				echo json_encode($json);
				exit();
			}
			$attachments['files'][] = $file;
		}
	}
	$json = array();
	$json['data'] = array();
	try
	{
		// подключение к базе данных
		$st->db_connect('smartteams_business');
		$st->dbh->beginTransaction();	
		$s = $st->dbh->prepare("SELECT `id` FROM `journal_entries` WHERE `user_id` = :user_id AND `date` = :date AND `criteria` = :criteria");
		$s->bindParam(':user_id', $user_id, PDO::PARAM_INT);
		$s->bindParam(':date', $date, PDO::PARAM_STR);
		if($criteria !== null)
		{
			$s->bindParam(':criteria', $criteria, PDO::PARAM_STR);			
		}
		else
		{
			$s->bindValue(':criteria', NULL, PDO::PARAM_NULL);
		}
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);
		// если запись уже существует...
		if($fetch !== false)
		{			
			$journal_entry_id = intval($fetch['id']);
			// обновим основные данные о записи
			$s = $st->dbh->prepare("UPDATE `journal_entries` SET `comment` = :comment, `criteria` = :criteria, `grade` = :grade WHERE `id` = :journal_entry_id");
			if($comment !== null)
			{
				$s->bindParam(':comment', $comment, PDO::PARAM_STR);
			}
			else
			{
				$s->bindValue(':comment', NULL, PDO::PARAM_NULL);
			}
			if($criteria !== null)
			{
				$s->bindParam(':criteria', $criteria, PDO::PARAM_STR);
			}
			else
			{
				$s->bindValue(':criteria', NULL, PDO::PARAM_NULL);
			}
			if($grade !== null)
			{
				$s->bindParam(':grade', $grade, PDO::PARAM_INT);
			}
			else
			{
				$s->bindValue(':grade', NULL, PDO::PARAM_NULL);
			}
			$s->bindParam(':journal_entry_id', $journal_entry_id, PDO::PARAM_INT);
			if($s->execute() === true)
			{
				// если есть существующие файлы для удаления, вначале удалим их
				if($remove !== null)
				{
					foreach($remove as $basename)
					{
						$filename = $upload['app_dir'].'/'.$upload['dir'].'/'.$basename;
						// если файл существует...
						if(file_exists($filename))
						{
							unlink($filename);
						}
						$s = $st->dbh->prepare("DELETE FROM `journal_entries_attachments` WHERE `journal_entry_id` = :journal_entry_id AND CONCAT(`filename`,'.',`ext`) = :basename");
						$s->bindParam(':journal_entry_id', $journal_entry_id, PDO::PARAM_INT);
						$s->bindParam(':basename', $basename, PDO::PARAM_STR);
						$s->execute();
					}
				}
				// затем добавим новые файлы приложения записи журнала, если они есть
				if($attachments !== null)
				{
					foreach($attachments['files'] as $file)
					{
						// если директории для файлов ещё не существует...
						if(is_dir($upload['app_dir'].'/'.$upload['dir']) === false)
						{
							// ...создадим директорию для файлов
							mkdir($upload['app_dir'].'/'.$upload['dir'], $upload['mode'], true);
						}
						// проверим если файл приложения записи журнала уже существует
						if(file_exists($file['upload']) === false)
						{
							// ...если файл не существует, то создадим его
							if($file['size'] > 0)
							{
								move_uploaded_file($file['tmp'], $file['upload']);								
							}
							else
							{
								touch($file['upload']);
							}
							// обновим сведения о файлах в приложении записи журнала в базе данных
							$s = $st->dbh->prepare("INSERT INTO `journal_entries_attachments` (`journal_entry_id`, `app_dir`, `upload_dir`, `filename`, `ext`, `size`) VALUES (:journal_entry_id, :app_dir, :upload_dir, :filename, :ext, :size)");
							$s->bindParam(':journal_entry_id', $journal_entry_id, PDO::PARAM_INT);
							$s->bindParam(':app_dir', $upload['app_dir'], PDO::PARAM_STR);
							$s->bindParam(':upload_dir', $upload['dir'], PDO::PARAM_STR);
							$s->bindParam(':filename', $file['filename'], PDO::PARAM_STR);
							$s->bindParam(':ext', $file['ext'], PDO::PARAM_STR);
							$s->bindParam(':size', $file['size'], PDO::PARAM_INT);
							// если создание записи о файле приложения записи журнала не получилось...
							if($s->execute() === false)
							{
								// ...прерываем процесс обновления записи журнала
								throw new Exception('can not create attachment record in the database');
							}
						}
						// ...если файл уже существует...
						else
						{
							// ...прерываем процесс создания записи журнала
							throw new Exception('attachment file already exists');
						}
					}
				}
				// возвращение значения AJAX
				$st->dbh->commit();
				$st->db_close();
				$json['status'] = 'success';
				$json['message'] = 'Grade has been successfully updated.';
				echo json_encode($json);
				exit();
			}
			else
			{
				throw new Exception('can not update grade with the parameters specified');
			}
		}
		// ...если запись ещё не существует
		else
		{
			// ...создадим запись журнала в базе данных
			$s = $st->dbh->prepare("INSERT INTO `journal_entries` (`user_id`, `date`, `comment`, `criteria`, `grade`) VALUES (:user_id, :date, :comment, :criteria, :grade)");
			$s->bindParam(':user_id', $user_id, PDO::PARAM_INT);
			$s->bindParam(':date', $date, PDO::PARAM_STR);
			if($comment !== null)
			{
				$s->bindParam(':comment', $comment, PDO::PARAM_STR);
			}
			else
			{
				$s->bindValue(':comment', NULL, PDO::PARAM_NULL);
			}
			if($criteria !== null)
			{
				$s->bindParam(':criteria', $criteria, PDO::PARAM_STR);
			}
			else
			{
				$s->bindValue(':criteria', NULL, PDO::PARAM_NULL);
			}
			if($grade !== null)
			{
				$s->bindParam(':grade', $grade, PDO::PARAM_INT);
			}
			else
			{
				$s->bindValue(':grade', NULL, PDO::PARAM_NULL);
			}
			// если создание новой записи журнала успешно выполнено...
			if($s->execute() === true)
			{
				// ...получим идентификатор новой созданной записи журнала
				$s = $st->dbh->prepare("SELECT LAST_INSERT_ID() AS `journal_entry_id`");
				$s->execute();
				$fetch = $s->fetch(PDO::FETCH_ASSOC);
				// проверка если операция выполнена успешно...
				if($fetch !== false)
				{
					$journal_entry_id = intval($fetch['journal_entry_id']);
				}
				// ...если не получилось получить идентификатор новой записи журнала...
				else
				{
					// ...сообщаем об ошибке и прерываем процесс создания записи журнала
					throw new Exception('can not fetch identifier for a new grade record.');
				}
				// запишем файлы приложения
				if($attachments !== null)
				{
					foreach($attachments['files'] as $file)
					{
						// если директории для файлов ещё не существует...
						if(is_dir($upload['app_dir'].'/'.$upload['dir']) === false)
						{
							// ...создадим директорию для файлов
							mkdir($upload['app_dir'].'/'.$upload['dir'], $upload['mode'], true);
						}
						// проверим если файл приложения записи журнала уже существует
						if(file_exists($file['upload']) === false)
						{
							// ...если файл не существует, то создадим его
							if($file['size'] > 0)
							{
								move_uploaded_file($file['tmp'], $file['upload']);								
							}
							else
							{
								touch($file['upload']);
							}
							// обновим сведения о файлах в приложении записи журнала в базе данных
							$s = $st->dbh->prepare("INSERT INTO `journal_entries_attachments` (`journal_entry_id`, `app_dir`, `upload_dir`, `filename`, `ext`, `size`) VALUES (:journal_entry_id, :app_dir, :upload_dir, :filename, :ext, :size)");
							$s->bindParam(':journal_entry_id', $journal_entry_id, PDO::PARAM_INT);
							$s->bindParam(':app_dir', $upload['app_dir'], PDO::PARAM_STR);
							$s->bindParam(':upload_dir', $upload['dir'], PDO::PARAM_STR);
							$s->bindParam(':filename', $file['filename'], PDO::PARAM_STR);
							$s->bindParam(':ext', $file['ext'], PDO::PARAM_STR);
							$s->bindParam(':size', $file['size'], PDO::PARAM_INT);
							// если создание записи о файле приложения записи журнала не получилось...
							if($s->execute() === false)
							{
								// ...прерываем процесс обновления записи журнала
								throw new Exception('can not create attachment record in the database');
							}
						}
						// ...если файл уже существует...
						else
						{
							// ...прерываем процесс создания записи журнала
							throw new Exception('attachment file already exists');
						}
					}
				}
				// возвращение значения AJAX
				$st->dbh->commit();
				$st->db_close();
				$json['status'] = 'success';
				$json['message'] = 'Grade has been successfully added.';
				echo json_encode($json);
				exit();
			}
			else
			{
				throw new Exception('can not create grade with the parameters specified');
			}
		}
	}
	catch(Exception $e)
	{
		$st->dbh->rollback();
		$st->db_close();
		$json['status'] = 'error';
		$json['message'] = 'Add grade operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}