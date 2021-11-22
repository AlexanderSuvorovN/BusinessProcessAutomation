<?php
	require_once('./../st.php');
	$task_id = $_REQUEST['id'] ?? null;
	$task_id = filter_var($task_id, FILTER_SANITIZE_NUMBER_INT);
	$error = array('status' => false);
	try
	{
		if($task_id === false || $task_id < 0)
		{
			throw new stException('Неверно задан код задачи.');
		}
		$st->db_connect('smartteams_business');
		$s = $st->dbh->prepare("SELECT `id`, `name`, `description`, `date_begin`, `date_end` FROM `tasks` WHERE `id` = :task_id");
		$s->bindParam(":task_id", $task_id);
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);
		if($fetch)
		{
			$task = $fetch;
			$task['date_begin'] = $st->dateSplit($task['date_begin']);
			$task['date_end'] = $st->dateSplit($task['date_end']);
			$s = $st->dbh->prepare("SELECT `u`.`id` AS `user_id`, `u`.`status` AS `user_status`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `p`.`photo` AS `person_photo`, `r`.`name` AS `role_name`, `d`.`name` AS `department_name` FROM `tasks_to_users` AS `t2u` JOIN `users` AS `u` ON (`t2u`.`user_id` = `u`.`id`) JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`) WHERE `t2u`.`task_id` = :task_id");
			$s->bindParam(":task_id", $task_id);
			$s->execute();
			$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
			if($fetch)
			{
				$task['assignees'] = $fetch;
			}
			else
			{
				$task['assignees'] = array();
			}
		}
		else
		{				
			throw new stException('Задача не найдена.');
		}
		$s = $st->dbh->prepare("SELECT `u`.`id` AS `user_id`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `p`.`photo` AS `person_photo`, `r`.`name` AS `role_name`, `d`.`name` AS `department_name` FROM `users` AS `u` JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`)");
		$s->execute();
		$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
		if($fetch)
		{
			$assignment_candidates = $fetch;
		}
		else
		{
			$assignment_candidates = array();
		}
		$st->db_close();
	}
	catch(stException $ste)
	{
		$error['status'] = true;
		$error['message'] = $ste->getMessage();
	}
	catch(Exception $e)
	{
		$error['status'] = true;
		$error['message'] = $e->getMessage();
	}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<?= $st->Meta() ?>
	<?= $st->Title() ?>
	<?= $st->FavIcon() ?>
    <?= $st->Fonts() ?>
	<?= $st->Style('/libs/thedatepicker/the-datepicker.css') ?>
	<?= $st->Style('/libs/richtexteditor/rte_theme_default.css') ?>
	<?= $st->Style("/st.css", true) ?>
	<?= $st->Style("/header.css", true) ?>
	<?= $st->Style("/sidebar.css", true) ?>
	<?= $st->Style("/footer.css", true) ?>
	<?= $st->Style("/tasks/duplicate.css", true) ?>
	<?= $st->JQuery() ?>
	<?= $st->Script('/libs/thedatepicker/the-datepicker.js') ?>
	<?= $st->Script('/libs/richtexteditor/rte.js') ?>
	<?= $st->Script('/libs/richtexteditor/lang/rte-lang-ru.js') ?>
	<?= $st->Script('/libs/richtexteditor/plugins/all_plugins.js') ?>
	<?= $st->Script("/st.js") ?>
	<?= $st->Script("/tasks/duplicate.js") ?>
</head>
<body>
	<?php $st->Header() ?>
	<main>
		<?php require_once($st->config["mainDirPath"]."/sidebar.php") ?>
		<div class="view">
			<?php if($error['status']): ?>
				<h2>Ошибка дублирования задачи</h2>
				<div class='error_message'><?= $error['message'] ?></div>
			<?php else: ?>
				<h2>Новая задача (дублирование)</h2>
				<div class='controls'>
					<button class='cancel'>Отмена</button>
					<button class='submit'>Сохранить</button>
				</div>
				<div class="task_data">
					<div class='field name'>
						<div class='label'>Название</div>
						<div class='value'>
							<input type='text' name='name' value='<?= $task['name'] ?>'>
						</div>
					</div>
					<div class='field description'>
						<div class='label'>Описание</div>
						<div class='value'>
							<textarea><?= $task['description'] ?></textarea>
						</div>
					</div>
					<div class='field date date_begin'>
						<div class='label'>Начало</div>
						<div class='value'>
							<div class='date'>
								<div class='icon'></div>
								<input type='text' name='date_begin' value='<?= $task['date_begin']['date'] ?>'>
							</div>
							<div class='time'>
								<div class='icon'></div>
								<input type='text' name='time_begin' value='<?= $task['date_begin']['time'] ?>'>
							</div>
						</div>
					</div>
					<div class='field date date_end'>
						<div class='label'>Завершение</div>
						<div class='value'>
							<div class='date'>
								<div class='icon'></div>
								<input type='text' name='date_end' value='<?= $task['date_end']['date'] ?>'>
							</div>
							<div class='time'>
								<div class='icon'></div>
								<input type='text' name='time_end' value='<?= $task['date_end']['time'] ?>'>
							</div>
						</div>
					</div>
					<div class='field assignees'>
						<div class='label'>Назначение</div>
						<div class='value'>
							<?php if(!empty($task['assignees'])): ?>
								<?php foreach($task['assignees'] as $assignee): ?>
									<div class='profile'>
										<input type='hidden' name='user_id' value='<?= $assignee['user_id'] ?>'>
										<div class='avatar' style='background-image: url("<?= $assignee['person_photo'] ?>")'>
											<div class='status <?= $assignee['user_status'] ?>'></div>
										</div>
										<div class='info'>
											<div class='name'>
												<span class='firstname'><?= $assignee['person_firstname'] ?></span>&nbsp;<span class='lastname'><?= $assignee['person_lastname'] ?></span>
											</div>
											<div class='role_name'><?= $assignee['role_name'] ?></div>
											<div class='department_name'><?= $assignee['department_name'] ?></div>
										</div>
										<div class="button_close"></div>
									</div>
								<?php endforeach; ?>
							<?php else: ?>
									<div class='empty'></div>
							<?php endif; ?>
						</div>
					</div>
					<div class='field select_assignee'>
						<select>
							<option value=''></option>
							<?php foreach($assignment_candidates as $candidate): ?>
								<option value='<?= $candidate['user_id'] ?>'>
									<?= $candidate['person_firstname'] ?>&nbsp;<?= $candidate['person_lastname'] ?>&nbsp;/&nbsp;<?= $candidate['role_name'] ?>&nbsp;/&nbsp;<?= $candidate['department_name'] ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</main>
	<?php require_once($st->config["mainDirPath"]."/footer.php") ?>
	<div class='dummy' style='display: none'>
		<div class='profile'>
			<input type='hidden' name='user_id' value=''>
			<div class='avatar'>
				<div class='status'></div>
			</div>
			<div class='info'>
				<div class='name'>
					<span class='firstname'></span>&nbsp;<span class='lastname'></span>
				</div>
				<div class='role_name'></div>
				<div class='department_name'></div>
			</div>
			<div class="button_close"></div>
		</div>
	</div>
</body>
</html>