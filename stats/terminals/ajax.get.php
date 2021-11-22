<?php 
	require_once("./../../st.php");
	$user_id = $_REQUEST['user_id'] ?? null;
	$date = $_REQUEST['date'] ?? null;
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
	$preg_pattern = '/[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}/i';
	if(preg_match($preg_pattern, $date) !== 1)
	{
		$json['status'] = 'error';
		$json['message'] = 'invalid value for "date" parameter: '.$date;
		echo json_encode($json);
		exit();
	}
	$json = array();
	$json['data'] = array();
	try
	{
		// подключение к базе данных
		$st->db_connect('smartteams_business');
		// вначале выберем имя пользователя на терминале
		$s = $st->dbh->prepare("SELECT `terminal_username` FROM `users` WHERE `id` = :user_id");
		$s->bindParam(':user_id', $user_id, PDO::PARAM_INT);
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);
		if($fetch)
		{
			$terminal_username = $fetch['terminal_username'];
		}
		else
		{
			throw new Exception('can not find associated terminal username.');
		}
		// затем получим записи для этой учётной записи
		$s = $st->dbh->prepare("SELECT `date`, `type`, `application_path`, `application_name`, `application_window_title`, `details` FROM `terminal_stats` WHERE `terminal_username` = :terminal_username AND DATE(`date`) = :date AND `type` IN ('Активность пользователя', 'Активность программ', 'Посещенные веб-сайты') ORDER BY `date` ASC");
		$s->bindParam(':date', $date, PDO::PARAM_STR);
		$s->bindParam(':terminal_username', $terminal_username, PDO::PARAM_STR);
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch !== false)
		{
			if(!empty($fetch))
			{
				$start_dto = null;
				$end_dto = null;
				$activity_track = 
					array(
						'start_dto' => null,
						'end_dto' => null,
						'seconds' => 0
					);
				$chart = [];
				$chart['labels'] = [];
				$chart['data'] = [];
				for($i = 0; $i < 24; $i++)
				{
					$chart['labels'][] = $i;
					$chart['data'][] = 0;
				}
				foreach($fetch as &$row)
				{
					$preg_pattern = '/([0-9]{4}\-[0-9]{2}\-[0-9]{2})\s([0-9]{2}:[0-9]{2}:[0-9]{2})/i';
					preg_match($preg_pattern, $row['date'], $matches);
					$row['date'] = array(
							'full' => $row['date'],
							'date' => $matches[1],
							'time' => $matches[2]
						);
					$this_dto = new DateTime($row['date']['full']);
					if($row['type'] === 'Активность пользователя' && in_array($row['details'], ['Активирован', 'Начало активности пользователя']))
					{
						if(($start_dto === null) || ($this_dto < $start_dto))
						{
							$start_dto = $this_dto;
						}
					}
					if($row['type'] === 'Активность пользователя' && $row['details'] === 'Начало активности пользователя')
					{
						$activity_track['start_dto'] = $this_dto;					
					}
					if($row['type'] === 'Активность пользователя' && $row['details'] === 'Завершение активности пользователя')
					{
						if(($end_dto === null) || ($this_dto > $end_dto))
						{
							$end_dto = $this_dto;
						}
						if($activity_track['start_dto'] !== null)
						{
							$activity_track['end_dto'] = $this_dto;
							$activity_track['seconds'] += $activity_track['end_dto']->getTimestamp() - $activity_track['start_dto']->getTimestamp();
							//
							// расчёт по часам для построения графика
							$seconds = $activity_track['end_dto']->getTimestamp() - $activity_track['start_dto']->getTimestamp();
							$start_hour = intval($activity_track['start_dto']->format('H'));
							$done = false;
							// echo 'activity_track[\'start_dto\']: '.$activity_track['start_dto']->format('Y-m-d H:i:s').PHP_EOL;
							// echo 'activity_track[\'end_dto\']: '.$activity_track['end_dto']->format('Y-m-d H:i:s').PHP_EOL;
							while($done === false)
							{
								$hour_end_dto = new DateTime(sprintf('%s %02u:59:59', $row['date']['date'], $start_hour));
								$this_hour_seconds = $hour_end_dto->getTimestamp() - $activity_track['start_dto']->getTimestamp();
								// echo 'start_hour: '.$start_hour.'<br>'.PHP_EOL;
								// echo 'seconds: '.$seconds.'<br>'.PHP_EOL;
								// echo 'hour_end_dto: '.$hour_end_dto->format('Y-m-d H:i:s').'<br>'.PHP_EOL;
								// echo 'this_hour_seconds: '.$this_hour_seconds.'<br>'.PHP_EOL;
								if($seconds <= $this_hour_seconds)
								{
									$chart['data'][$start_hour] += $seconds;
									// echo 'chart[\'data\']['.$start_hour.']: '.$chart['data'][$start_hour].PHP_EOL;
									$done = true;
								}
								else
								{
									$chart['data'][$start_hour] += $this_hour_seconds;
									// echo 'chart[\'data\']['.$start_hour.']: '.$chart['data'][$start_hour].PHP_EOL;								
									$seconds -= $this_hour_seconds;
									$start_hour++;
									if($seconds <= 0 || $start_hour > 23)
									{
										$done = true;
									}
								}
							}
						}
						$activity_track['start_dto'] = null;
						$activity_track['end_dto'] = null;
					}
				}
				$json['data']['records'] = $fetch;
				$json['data']['summary'] = [];
				$json['data']['summary']['start'] = ($start_dto !== null) ? $start_dto->format('H:i:s') : null;
				$json['data']['summary']['end'] = ($end_dto !== null) ? $end_dto->format('H:i:s') : null;
				$json['data']['summary']['work'] = ($start_dto !== null && $end_dto !== null) ? $st->dhms($end_dto->getTimestamp() - $start_dto->getTimestamp()) : null;
				$json['data']['summary']['active'] = ($start_dto !== null && $end_dto !== null) ? $st->dhms($activity_track['seconds']) : null;
				$json['data']['summary']['idle'] = ($start_dto !== null && $end_dto !== null) ? $st->dhms($end_dto->getTimestamp() - $start_dto->getTimestamp() - $activity_track['seconds']) : null;
				$json['data']['chart'] = $chart;
			}
			else
			{
				$json['data'] = null;
			}
			// возвращение значения AJAX
			$st->db_close();
			$json['status'] = 'success';
			$json['message'] = 'Terminal statistics records have been successfully fetched.';
			echo json_encode($json);
			exit();
		}
		else
		{
			throw new Exception('can not fetch terminal statistics records from the database.');
		}
	}
	catch(Exception $e)
	{
		$st->db_close();
		$json['status'] = 'error';
		$json['message'] = 'Get terminal statistics records operation failed: '.$e->getMessage();
		echo json_encode($json);
		exit();
	}