<?php
	class SmartTeams
	{
		public $config = array();
		public $cookies = array();
		public $urlOptions = array();
	    public $date;
		public $timestamp;
		public $year;
		public $dbh = null;
		public $authorized = null;
		public $user = null;
		public $user_status_map = array();
		public $task_status_map = array();
		public $navitem = null;
		public function __construct()
		{
			if(preg_match('/\.local$/', strtolower($_SERVER["SERVER_NAME"])) === 0)
			{
				$this->config["env"] = "production";
			}
			else 
			{
				$this->config["env"] = "development";
			}		
			$this->config["debug"] = false;
			$this->config["docRootPath"] = rtrim($_SERVER["DOCUMENT_ROOT"], "/");
			$preg_pattern = sprintf("/%s/i", preg_quote($this->config["docRootPath"], "/"));
			$preg_replace = "";
			$preg_source  = preg_replace("/\\\/i", "/", __DIR__);		
			// echo "preg_pattern: " . $preg_pattern . "<br>";
			// echo "preg_source: " . $preg_source . "<br>";
			$this->config["mainDirUrl"]  = preg_replace($preg_pattern, $preg_replace, $preg_source);
			echo $this->config['mainDirUrl'];
			$this->config["mainDirPath"] = __DIR__;
			/*
			thisDirUrl = pathinfo(SCRIPT_URL, PATHINFO_DIRNAME);
			*/
			$this->config["thisDirUrl"]   = pathinfo($_SERVER["PHP_SELF"], PATHINFO_DIRNAME);
			if($this->config['thisDirUrl'] === '\\')
			{
				$this->config['thisDirUrl'] = '/';
			}
			$this->config["thisDirPath"]  = getcwd();
			$this->config['appDirUrl']    = preg_replace("/\/..\/?$/i", "", $this->config["mainDirUrl"]);
			$this->config['imagesDirUrl'] = $this->config['appDirUrl']."/images";
			$this->config['videosDirUrl'] = $this->config['appDirUrl']."/videos";
			$this->config['fontsDirUrl']  = $this->config['appDirUrl']."/fonts";
			if($this->config["debug"])
			{
				echo "config[\"mainDirUrl\"]: {$this->config["mainDirUrl"]}<br>";
				echo "config[\"mainDirPath\"]: {$this->config["mainDirPath"]}<br>";
				echo "config[\"thisDirUrl\"]: {$this->config["thisDirUrl"]}<br>";
				echo "config[\"thisDirPath\"]: {$this->config["thisDirPath"]}<br>";
			}
			//
			//
			require_once('st.exception.php');
		    $this->date = new DateTime();
			$this->timestamp = $this->date->getTimestamp();
			$this->year = $this->date->format("Y");
			//
			// https://stackoverflow.com/questions/6249707/check-if-php-session-has-already-started
			if(session_status() == PHP_SESSION_NONE)
			{
				session_start();
			}
			if(isset($_SESSION['authorized']) && $_SESSION['authorized'] === true && isset($_SESSION['user_id']))
			{
				$this->db_connect();
				$s = $this->dbh->prepare("SELECT `u`.`id` AS `user_id`, `u`.`authorization_level` AS `user_authorization_level`, `u`.`avatar` AS `user_avatar`, `u`.`date_last_activity` AS `user_date_last_activity`, `p`.`id` AS `person_id`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `p`.`photo` AS `person_photo` FROM `users` AS `u` JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) WHERE `u`.`id` = :user_id LIMIT 1");
				$s->bindParam(":user_id", $_SESSION['user_id']);
				$s->execute();
				$fetch = $s->fetch(PDO::FETCH_ASSOC);
				if($fetch)
				{
					$this->user = array();
					$this->user['id'] = $fetch['user_id'];
					$this->user['authorization_level'] = $fetch['user_authorization_level'];
					$this->user['person_id'] = $fetch['person_id'];
					$this->user['person_firstname'] = $fetch['person_firstname'];
					$this->user['person_lastname'] = $fetch['person_lastname'];
					$this->user['person_photo'] = $fetch['person_photo'];
					$this->user['user_avatar'] = $fetch['user_avatar'];
					$this->user['status'] = 'онлайн';
					$this->SetAuth(true);
					$s = $this->dbh->prepare("UPDATE `users` SET `date_last_activity` = NOW(), `status` = 'online' WHERE `id` = :user_id");
					$s->bindParam(":user_id", $_SESSION['user_id']);
					$s->execute();
					$s = $this->dbh->prepare("SELECT `id` AS `employee_id` FROM `employees` WHERE `person_id` = :person_id LIMIT 1");
					$s->bindParam(":person_id", $this->user['person_id']);
					$s->execute();
					$fetch = $s->fetch(PDO::FETCH_ASSOC);
					if($fetch)
					{
						$this->user['is_employee'] = true;
						$this->user['employee_id'] = $fetch['employee_id'];
						$s = $this->dbh->prepare("SELECT `r`.`name` AS `role_name`, `r`.`duties` AS `role_duties`, `d`.`name` AS `department_name` FROM `persons` AS `p` JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`) WHERE `p`.`id` = :person_id");
						$s->bindParam(":person_id", $this->user['person_id']);
						$s->execute();
						$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
						if($fetch)
						{
							$this->user['roles'] = $fetch;
						}
						else
						{
							$this->user['roles'] = null;
						}
					}
					else
					{
						$this->user['is_employee'] = false;
						$this->user['employee_id'] = null;
					}
				}
				$s = $this->dbh->prepare("SELECT `id`, `name`, `display`, `description` FROM `user_status_map`");
				$s->execute();
				$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
				if($fetch)
				{
					foreach($fetch as $row)
					{
						$this->user_status_map[$row['name']] = array(
								'id' => $row['id'],
								'display' => $row['display'],
								'description' => $row['description']
							);
					}
				}
				$s = $this->dbh->prepare("SELECT `id`, `name`, `display`, `description` FROM `task_status_map`");
				$s->execute();
				$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
				if($fetch)
				{
					foreach($fetch as $row)
					{
						$this->task_status_map[$row['name']] = array(
								'id' => $row['id'],
								'display' => $row['display'],
								'description' => $row['description']
							);
					}
				}
				$this->db_close();
				$preg_pattern = "^\/([a-z0-9_]+)(?:\/([a-z0-9_]+))*(?:\.html|\.htm|\.php|\/)?(?:\?|$)?";
				$preg_subject = $_SERVER['REQUEST_URI'];
				$preg_match = preg_match("/$preg_pattern/i", $preg_subject, $matches);
				if($preg_match === 1)
				{
					$this->navitem = array();
					for($i = 1; $i < count($matches); $i++)
					{
						$this->navitem[] = $matches[$i];
					}
					if(empty($this->navitem))
					{
						$this->navitem[] = 'main';
					}					
					$this->navitem = json_encode($this->navitem);
				}
				if(!isset($_SESSION['ui']))
				{
					$_SESSION['ui'] = array();
				}
				if(!isset($_SESSION['ui']['tasks']))
				{
					$_SESSION['ui']['tasks'] = array();					
				}
				if(!isset($_SESSION['ui']['tasks']['filter']))
				{
					$_SESSION['ui']['tasks']['filter'] = array();					
				}
			}
			else
			{
				$this->SetAuth(false);
			}
		}
		public function SetAuth($type, $options = ['redirect' => false])
		{			
			switch($type)
			{
				case true:
					$this->authorized = true;
					$_SESSION['authorized'] = $this->authorized;
					if(isset($options['user_id']))
					{
						$_SESSION['user_id'] = $options['user_id'];
					}
					break;
				case false:
					$this->authorized = false;
					$_SESSION['authorized'] = $this->authorized;
					//
					// needed to prevent endless loop during calling SmartTeams constructor at logon page
					if($options['redirect'])
					{
						$preg_pattern = "/^\/(?:login|auth)(?:\.html|\.htm|\.php|\/)?$/i";
						$preg_subject =$_SERVER['REQUEST_URI'];
						$preg_match = preg_match($preg_pattern, $preg_subject);
						if($preg_match !== 1)
						{
							header("Location: /login");
							exit();
						}
					}
					break;
			}
		}
		public function JQuery($ver = "default")
		{
			if($ver === "default")
			{
				$ver = "3.5.1";
			}
			printf("<script src=\"%s/jquery/jquery-%s.min.js\"></script>".PHP_EOL, $this->config["mainDirUrl"], $ver);
		}
		public function ConsoleLog($msg)
		{
			$date = new DateTime("now", new DateTimeZone("EUROPE/Prague"));	
			$logMsg = __CLASS__ . ", " . $date->format("Y-m-d, G:i:s.u") . ": " . $msg;
			echo "<script>" . PHP_EOL;
			echo "console.log(\"{$logMsg}\");" . PHP_EOL;
			echo "</script>" . PHP_EOL;
		}
		public function Meta()
		{
			print("<meta charset=\"utf-8\">".PHP_EOL);
			print("<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">".PHP_EOL);
			print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">".PHP_EOL);
			print("<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">".PHP_EOL);
		}
		public function Title()
		{
			print("<title>SmartTeams&trade; Office &mdash; Управленческий учёт</title>".PHP_EOL);
		}
		public function FavIcon()
		{
			print("<link rel=\"icon\" href=\"/favicon.ico?v={$this->timestamp}\">".PHP_EOL);
		}
		public function Fonts()
		{
		    print("<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\">".PHP_EOL);
			print("<link href=\"https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap\" rel=\"stylesheet\">".PHP_EOL);
			print("<link href=\"https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap\" rel=\"stylesheet\">".PHP_EOL);
			print("<link href=\"https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700;1,900&display=swap\" rel=\"stylesheet\">".PHP_EOL);
			print("<link href=\"https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap\" rel=\"stylesheet\">".PHP_EOL);
			print("<link href=\"https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap\" rel=\"stylesheet\">".PHP_EOL);
			print("<link href=\"https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap\" rel=\"stylesheet\">".PHP_EOL);
		}
		public function Style($url, $force = false)
		{
			$html = sprintf("<link type=\"text/css\" meadia=\"screen\" rel=\"stylesheet\" href=\"%s%s\">", $url, ($force) ? "?v={$this->timestamp}" : "");
			echo $html.PHP_EOL;
		}
		public function Script($url)
		{
			printf("<script src=\"%s\"></script>".PHP_EOL, $url);
		}
		public function Header()
		{
			require_once($this->config["mainDirPath"]."/header.php");
		}
		public function Footer()
		{

			require_once($this->config["mainDirPath"]."/footer.php");
		}
		public function db_connect($db = 'smartteams_business')
		{
			try
			{
				$dbco = array();
				switch($db)
				{
					case 'smartteams_business':
						$dbco['database'] = $db;
						$dbco['username'] = $db;
						$dbco['password'] = 'cj6iajw6oKRmq7S7';
						break;
					case 'smartteams_school':
						$dbco['database'] = $db;
						$dbco['username'] = $db;
						$dbco['password'] = 'iEJ6ZVAlcBekJa3Q';
						break;
					default:
						throw new Exception('Неверное имя базы данных ("'.$db.'")');
						break;
				}
				$this->dbh = new PDO("mysql:host=localhost;dbname=".$dbco['database'].";charset=utf8", $dbco['username'], $dbco['password']);
		    	$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    	//echo "Connected successfully<br>";
			}
			catch(PDOException $e)
			{
				echo '<div>';
			    echo "Ошибка при подключении к базе данных: ".$e->getMessage();
			    echo '</div>';
			}
			return $this->dbh;
		}
		public function db_close()
		{
			$this->dbh = null;
		}
		public function dateSplit($datetime)
		{
			$dto = array();
			$dto['datetime'] = trim($datetime);
			$preg_pattern = '/^([0-9]{4}\-[0-9]{2}\-[0-9]{2})\s([0-9]{2}\:[0-9]{2})(?:\:[0-9]{2})?$/i';
			$preg_match = preg_match($preg_pattern, $dto['datetime'], $matches);
			if($preg_match === 1)
			{
				$dto['date'] = $matches[1];
				$dto['time'] = $matches[2];
			}
			else
			{
				$dto['date'] = '0000-00-00';
				$dto['time'] = '00:00';
			}
			$dto['datetime_html'] = $dto['date'].'&nbsp;'.$dto['time'];
			return $dto;
		}
		public function splitDate($datetime)
		{
			return $this->dateSplit($datetime);
		}
		public function dhms($seconds, $options = [])
		{
			if(empty($options))
			{
				$options['format'] = '1';
			}
			$seconds_per_minute = 60;
			$seconds_per_hour = 60 * $seconds_per_minute; 
			$seconds_per_day = 24 * $seconds_per_hour;
			$days = intdiv($seconds, $seconds_per_day);
			$seconds -= ($days * $seconds_per_day);
			$hours = intdiv($seconds, $seconds_per_hour);
			$seconds -= ($hours * $seconds_per_hour);
			$minutes = intdiv($seconds, $seconds_per_minute);
			$seconds -= ($minutes * $seconds_per_minute);
			if($options['format'] === '1')
			{
				$dhms = ($days > 0) ? str_pad($days, 2, '0').' д. ' : '';
				$dhms .= str_pad($hours, 2, '0', STR_PAD_LEFT).' ч. ';
				$dhms .= str_pad($minutes, 2, '0', STR_PAD_LEFT).' м. ';
				$dhms .= str_pad($seconds, 2, '0', STR_PAD_LEFT).' с. ';
			}
			return $dhms;
		}
		/*
		* 
		* mb_ucfirst
		* https://stackoverflow.com/questions/2517947/ucfirst-function-for-multibyte-character-encodings
		*
		*/
		public function mb_ucfirst($string, $encoding = 'utf-8')
		{
		    $first_char = mb_substr($string, 0, 1, $encoding);
		    $then = mb_substr($string, 1, null, $encoding);
		    return mb_strtoupper($first_char, $encoding) . $then;
		}
	}
	$st = new SmartTeams();	