<?php
	require_once('./../st.php');
	$task_id = $_REQUEST['id'] ?? null;
	$task_id = filter_var($task_id, FILTER_SANITIZE_NUMBER_INT);
	$error_status = false;
	if($task_id === false)
	{
		$error_status = true;
		$error_type = 'input';
	}
	else
	{
		$st->db_connect('smartteams_business');
		$s = $st->dbh->prepare("SELECT `id`, `name`, `description`, `date_created`, `date_begin`, `date_end`, `progress`, `date_started`, `date_completed`, `created_by`, `status`, `time_spent`, `author_review_comment`, `author_review_rate`, `assignee_review_comment`, `assignee_review_specific`, `assignee_review_measurable`, `assignee_review_attainable`, `assignee_review_relevant`, `assignee_review_timebound` FROM `tasks` WHERE `id` = :task_id");
		$s->bindParam(":task_id", $task_id);
		$s->execute();
		$fetch = $s->fetch(PDO::FETCH_ASSOC);
		if($fetch)
		{
			$task = $fetch;
			$task['status_display'] = $st->mb_ucfirst($st->task_status_map[$task['status']]['display']);
			$task['date_created'] = $st->dateSplit($task['date_created']);
			$task['date_begin'] = $st->dateSplit($task['date_begin']);
			$task['date_end'] = $st->dateSplit($task['date_end']);
			$task['date_started'] = $st->dateSplit($task['date_started']);
			$task['date_completed'] = $st->dateSplit($task['date_completed']);
			$task['time_spent'] = array(
					'seconds' => $task['time_spent'],
					'dhms' => $st->dhms($task['time_spent']));
			$s = $st->dbh->prepare("SELECT `u`.`status` AS `user_status`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `p`.`photo` AS `person_photo`, `r`.`name` AS `role_name`, `d`.`name` AS `department_name` FROM `users` AS `u` JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`) WHERE `u`.`id` = :user_id");
			$s->bindParam(":user_id", $task['created_by']);
			$s->execute();
			$fetch = $s->fetch(PDO::FETCH_ASSOC);
			if($fetch)
			{
				$task['created_by'] = array(
					'user_id' => $task['created_by'],
					'info' => $fetch);
			}
			else
			{
				$task['created_by'] = array(
					'user_id' => $task['created_by'],
					'info' => array());
			}
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
			$dto_date_today = new DateTime();
			$dto_date_end = new DateTime($task['date_end']['datetime']);
			$owned_by_me = ($task['created_by']['user_id'] === $st->user['id']);
			$assigned_to_me = false;
			foreach($task['assignees'] as $assignee)
			{
				if($st->user['id'] === $assignee['user_id'])
				{
					$assigned_to_me = true;
					break;
				}
			}
			$task['buttons'] = array();
			$task['buttons']['start'] = false;
			$task['buttons']['decline'] = false; 
			$task['buttons']['complete'] = false;
			$task['buttons']['abort'] = false;
			$task['buttons']['edit'] = false;
			$task['buttons']['cancel'] = false; // задача отменена автором
			$task['buttons']['remove'] = false; // задача отозвана автором
			$task['buttons']['delete'] = false; // только не назначенные задачи могут быть удалены
			$task['buttons']['close'] = false;  // задачи, которые были хотя бы один раз назначены не могут быть удалены и помещаются в архив. неназначенные задачи могут быть удалены
			$task['buttons']['review_by_author'] = false; // оценка исполнения автором
			$task['buttons']['review_by_assignee'] = false; // оценка задачи исполнителем
			$task['buttons']['duplicate'] = true && $owned_by_me; // дублировать задачу возможно в любой стадии
			$tabs = array();
			$tabs['general'] = array();
			$tabs['general']['editable'] = false;
			$tabs['comments'] = array();
			$tabs['comments']['editable'] = false;
			$tabs['author_review'] = array();
			$tabs['author_review']['display'] = false;
			$tabs['author_review']['editable'] = false;
			$tabs['assignee_review'] = array();
			$tabs['assignee_review']['display'] = false;
			$tabs['assignee_review']['editable'] = false;
			if($task['status'] === 'created')
			{
				$task['buttons']['edit'] = true;
				$task['buttons']['delete'] = true;
				$tabs['general']['editable'] = true && $owned_by_me;
				$tabs['comments']['editable'] = true && $owned_by_me;
			}
			if($task['status'] === 'assigned')
			{
				$task['buttons']['start'] = true;
				$task['buttons']['decline'] = true;
				$task['buttons']['cancel'] = true;
				if($dto_date_today > $dto_date_end)
				{
					$task['buttons']['remove'] = true && $owned_by_me;
				}
				$tabs['comments']['editable'] = true;
			}
			if($task['status'] === 'declined')
			{
				$task['buttons']['review_by_author'] = true && $owned_by_me;
				$tabs['author_review']['display'] = true && $owned_by_me;
				$tabs['author_review']['editable'] = true && $owned_by_me;
			}
			if($task['status'] === 'implementation')
			{
				$task['buttons']['complete'] = true;
				$task['buttons']['abort'] = true;
				$task['buttons']['cancel'] = true && $owned_by_me;
				if($dto_date_today > $dto_date_end)
				{
					$task['buttons']['remove'] = true && $owned_by_me;
				}
				$tabs['comments']['editable'] = true;
			}
			if($task['status'] === 'completed')
			{
				$task['buttons']['review_by_author'] = true && $owned_by_me;
				$tabs['author_review']['display'] = true && $owned_by_me;
				$tabs['author_review']['editable'] = true && $owned_by_me;
			}
			if($task['status'] === 'cancelled')
			{
				$task['buttons']['review_by_author'] = true && $owned_by_me;
				$tabs['author_review']['display'] = true && $owned_by_me;
				$tabs['author_review']['editable'] = true && $owned_by_me;
			}
			if($task['status'] === 'removed')
			{
				$task['buttons']['review_by_author'] = true && $owned_by_me;
				$tabs['author_review']['display'] = true && $owned_by_me;
				$tabs['author_review']['editable'] = true && $owned_by_me;
			}
			if($task['status'] === 'aborted')
			{
				$task['buttons']['review_by_author'] = true && $owned_by_me;
				$tabs['author_review']['display'] = true && $owned_by_me;
				$tabs['author_review']['editable'] = true && $owned_by_me;
			}
			if($task['status'] === 'reviewed_by_author')
			{
				$task['buttons']['review_by_assignee'] = true;
				$tabs['author_review']['display'] = true;
				$tabs['assignee_review']['display'] = true && $assigned_to_me;
				$tabs['assignee_review']['editable'] = true && $assigned_to_me;
			}
			if($task['status'] === 'reviewed_by_assignee')
			{
				$task['buttons']['close'] = true && $owned_by_me;
				$tabs['author_review']['display'] = true && ($owned_by_me || $assigned_to_me);
				$tabs['assignee_review']['display'] = true && ($owned_by_me || $assigned_to_me);
			}
			if($task['status'] === 'closed')
			{
				// задачу можно только дублировать
				$tabs['author_review']['display'] = true && ($owned_by_me || $assigned_to_me);
				$tabs['assignee_review']['display'] = true && ($owned_by_me || $assigned_to_me);
			}
			$s = $st->dbh->prepare("SELECT `tc`.`id` AS `task_comment_id`, `tc`.`date_created` AS `task_comment_date_created`, `u`.`status` AS `user_status`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `p`.`photo` AS `person_photo`, `r`.`name` AS `role_name`, `d`.`name` AS `department_name`, `tc`.`comment` AS `task_comment` FROM `tasks_comments` AS `tc` JOIN `users` AS `u` ON (`tc`.`user_id` = `u`.`id`) JOIN `persons` AS `p` ON (`u`.`person_id` = `p`.`id`) JOIN `employees` AS `e` ON (`p`.`id` = `e`.`person_id`) JOIN `roles` AS `r` ON (`e`.`id` = `r`.`employee_id`) JOIN `departments` AS `d` ON (`r`.`department_id` = `d`.`id`) WHERE `tc`.`task_id`=:task_id");
			$s->bindParam(":task_id", $task_id);
			$s->execute();
			$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
			if($fetch)
			{
				$task['comments'] = $fetch;
				foreach($task['comments'] as &$comment)
				{
					$comment['task_comment_date_created'] = $st->dateSplit($comment['task_comment_date_created']);
				}
				// need to unset reference in order to avoid issues with foreach: https://www.php.net/manual/en/control-structures.foreach.php
				unset($comment);
			}
			else
			{
				$task['comments'] = array();
			}
			if(!in_array($task['status'], ['closed']))
			{
				$task['new_comment'] = true;
			}
			else
			{
				$task['new_comment'] = false;
			}
		}
		else
		{
			$error_status = true;
			$error_type = 'fetch_task';
		}
		$st->db_close();
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
	<?= $st->Style("/tasks/view.css", true) ?>
	<?= $st->JQuery() ?>
	<?= $st->Script('/libs/thedatepicker/the-datepicker.js') ?>
	<?= $st->Script('/libs/richtexteditor/rte.js') ?>
	<?= $st->Script('/libs/richtexteditor/lang/rte-lang-ru.js') ?>
	<?= $st->Script('/libs/richtexteditor/plugins/all_plugins.js') ?>
	<?= $st->Script("/st.js") ?>
	<?= $st->Script("/tasks/view.js") ?>
</head>
<body>
	<?php $st->Header() ?>
	<main>
		<?php require_once($st->config["mainDirPath"]."/sidebar.php") ?>		
		<div class="view">
			<div class='breadcrumb'>
				<a href='/'><img src='/images/ui/icon.home.png'></a>
				<span class='separator'>&raquo;</span>
				<a href='/tasks'>Задачи</a>
				<span class='separator'>&raquo;</span>
				<span class='current'>Карточка задачи</span>
			</div>
			<?php if($error_status): ?>
				<h2>Ошибка отображения карточки задачи</h2>
				<?php if($error_type === 'input'): ?>
					<div class='error_message'>Неверно задан код задачи.</div>
				<?php endif; ?>
				<?php if($error_type === 'fetch_task'): ?>
					<div class='error_message'>Задача не найдена.</div>
				<?php endif; ?>
			<?php else: ?>
				<h2>Карточка задачи</h2>
				<div class='controls'>
					<!-- Изменение задачи динамически с помощью AJAX -->
					<!-- Автор задачи может изменить все поля кроме поля "Код" при условии, что задача в статусе "Создана", "Отменена" или "Отозвана" -->
					<!-- Исполнитель может только отклонить, начать исполнение или выполнить задачу -->
					<!-- Задание удаляется из базы данных только в случае если оно не было назначено,  -->
					<!-- т.к. пользователь также тратит время на ознакомление с заданием -->
					<button class='start' <?= ($task['buttons']['start']) ? '' : 'disabled' ?>>Начать</button>
					<button class='decline' <?= ($task['buttons']['decline']) ? '' : 'disabled' ?>>Отклонить</button>
					<button class='complete' <?= ($task['buttons']['complete']) ? '' : 'disabled' ?>>Выполнить</button>
					<button class='abort' <?= ($task['buttons']['abort']) ? '' : 'disabled' ?>>Прервать</button>
					<button class='edit' <?= ($task['buttons']['edit']) ? '' : 'disabled' ?>>Изменить</button>
					<button class='cancel' <?= ($task['buttons']['cancel']) ? '' : 'disabled' ?>>Отменить</button>
					<button class='remove' <?= ($task['buttons']['remove']) ? '' : 'disabled' ?>>Отозвать</button>
					<button class='delete' <?= ($task['buttons']['delete']) ? '' : 'disabled' ?>>Удалить</button>
					<button class='close' <?= ($task['buttons']['close']) ? '' : 'disabled' ?>>Закрыть</button>
					<button class='review_by_author' <?= ($task['buttons']['review_by_author']) ? '' : 'disabled' ?>>Оценить исполнение</button>
					<button class='review_by_assignee' <?= ($task['buttons']['review_by_assignee']) ? '' : 'disabled' ?>>Оценить задание</button>
					<button class='duplicate' <?= ($task['buttons']['duplicate']) ? '' : 'disabled' ?>>Дублировать</button>
				</div>
				<div class="task_info">
					<input type='hidden' name='task_id' value='<?= $task['id'] ?>'>
					<input type='hidden' name='task_status' value='<?= $task['status'] ?>'>
					<div class='tabs'>
						<div class='general active'>Основное</div>
						<div class='comments'>Комментарии</div>
						<?php if($tabs['author_review']['display']): ?>
							<div class='author_review'>Оценка исполнения</div>
						<?php endif; ?>
						<?php if($tabs['assignee_review']['display']): ?>
							<div class='assignee_review'>Оценка задания</div>
						<?php endif; ?>
						<div class='history'>История</div>
					</div>
					<div class='tab_content general'>
						<div class='field id'>
							<div class='label'>Код</div>
							<div class='value'><?= $task['id'] ?></div>
						</div>
						<div class='field name'>
							<div class='label'>Название</div>
							<div class='value'><?= $task['name'] ?></div>
						</div>
						<div class='field date date_created'>
							<div class='label'>Дата создания</div>
							<div class='value'>
								<div class='date'>
									<div class='icon'></div>
									<div class='text'><?= $task['date_created']['date'] ?></div>
								</div>
								<div class='time'>
									<div class='icon'></div>
									<div class='text'><?= $task['date_created']['time'] ?></div>
								</div>
							</div>
						</div>
						<div class='field created_by'>
							<div class='label'>Автор</div>
							<div class='value'>
								<div class='profile'>
									<div class='avatar' style="background-image: url('<?= $task['created_by']['info']['person_photo'] ?>')">
										<div class='status <?= $task['created_by']['info']['user_status'] ?>'></div>
									</div>
									<div class='info'>
										<div class='name'>
											<span class='firstname'><?= $task['created_by']['info']['person_firstname'] ?></span>&nbsp;<span class='lastname'><?= $task['created_by']['info']['person_lastname'] ?></span>
										</div>
										<div class='role_name'><?= $task['created_by']['info']['role_name'] ?></div>
										<div class='department_name'><?= $task['created_by']['info']['department_name'] ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class='field description'>
							<div class='label'>Описание</div>
							<div class='value'>
								<?php if($task['description'] !== ''): ?>
									<?= $task['description'] ?>
								<?php else: ?>
									<div class='empty'></div>
								<?php endif; ?>
							</div>
						</div>
						<div class='field date date_begin'>
							<div class='label'>Начало</div>
							<div class='value'>
								<div class='date'>
									<div class='icon'></div>
									<div class='text'><?= $task['date_begin']['date'] ?></div>
								</div>
								<div class='time'>
									<div class='icon'></div>
									<div class='text'><?= $task['date_begin']['time'] ?></div>
								</div>
							</div>
						</div>
						<div class='field date date_end'>
							<div class='label'>Завершение</div>
							<div class='value'>
								<div class='date'>
									<div class='icon'></div>
									<div class='text'><?= $task['date_end']['date'] ?></div>
								</div>
								<div class='time'>
									<div class='icon'></div>
									<div class='text'><?= $task['date_end']['time'] ?></div>
								</div>
							</div>
						</div>
						<div class='field status'>
							<div class='label'>Статус</div>
							<div class='value'><?= $task['status_display'] ?></div>
						</div>
						<div class='field assignees'>
							<div class='label'>Назначение</div>
							<div class='value'>
								<?php if(!empty($task['assignees'])): ?>
									<?php foreach($task['assignees'] as $assignee): ?>
										<div class='profile'>
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
										</div>
									<?php endforeach; ?>
								<?php else: ?>
										<div class='empty'></div>
								<?php endif; ?>
							</div>
						</div>
						<div class='field date date_started'>
							<div class='label'>Начало исполнения</div>
							<div class='value'>
								<div class='date <?= ($task['date_started']['date'] === '0000-00-00') ? 'empty' : '' ?>'>
									<div class='icon'></div>
									<div class='text'>
										<?= $task['date_started']['date'] ?>
									</div>
								</div>
								<div class='time <?= ($task['date_started']['time'] === '00:00') ? 'empty' : '' ?>'>
									<div class='icon'></div>
									<div class='text'>
										<?= $task['date_started']['time'] ?>
									</div>
								</div>
							</div>
						</div>
						<div class='field date date_completed'>
							<div class='label'>Завершение исполнения</div>
							<div class='value'>
								<div class='date <?= ($task['date_completed']['date'] === '0000-00-00') ? 'empty' : '' ?>'>
									<div class='icon'></div>
									<div class='text'>
										<?= $task['date_completed']['date'] ?>
									</div>
								</div>
								<div class='time <?= ($task['date_completed']['time'] === '00:00') ? 'empty' : '' ?>'>
									<div class='icon'></div>
									<div class='text'>
										<?= $task['date_completed']['time'] ?>
									</div>
								</div>
							</div>
						</div>
						<div class='field progress'>
							<div class='label'>Прогресс</div>
							<div class='value'><?= $task['progress'] ?>%</div>
						</div>
						<div class='field time_spent'>
							<div class='label'>Время выполнения</div>
							<div class='value'><?= $task['time_spent']['dhms'] ?></div>
						</div>
					</div>
					<div class='tab_content comments' style='display: none'>
						<div class='comments'>
							<?php foreach($task['comments'] as $comment): ?>
								<div class='comment'>
									<input type='hidden' name='task_comment_id' value='<?= $comment['task_comment_id'] ?>'>
									<div class='comment_caption'>
										<div class='datetime'>
											<div class='date_icon'></div>
											<div class='date_text'><?= $comment['task_comment_date_created']['date'] ?></div>
											<div class='time_icon'></div>
											<div class='time_text'><?= $comment['task_comment_date_created']['time'] ?></div>
										</div>
										<div class='author'>
											<div class='profile'>
												<div class='avatar' style='background-image: url("<?= $comment['person_photo'] ?>")'>
													<div class='status <?= $comment['user_status'] ?>'></div>
												</div>
												<div class='info'>
													<div class='name'>
														<span class='firstname'><?= $comment['person_firstname'] ?></span>&nbsp;<span class='lastname'><?= $comment['person_lastname'] ?></span>
													</div>
													<div class='role_name'><?= $comment['role_name'] ?></div>
													<div class='department_name'><?= $comment['department_name'] ?></div>
												</div>
											</div>
										</div>
									</div>
									<div class='comment_body'>
										<?= $comment['task_comment'] ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						<?php if($tabs['comments']['editable']): ?>
							<div class='new_comment'>
								<div class='label'>Добавить комментарий</div>
								<div class='editor'>
									<textarea name='comment_text'></textarea>
								</div>
								<div class='controls'>
									<button class='add'>Добавить</button>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<?php if($tabs['author_review']['display']): ?>
						<div class='tab_content author_review' style='display: none'>
							<?php if($tabs['author_review']['editable']): ?>
								<div class='controls'>
									<button class='save' disabled>Сохранить</button>
								</div>
							<?php endif; ?>
							<div class='field author_rate'>
								<input type='hidden' name='author_review_rate' value='<?= $task['author_review_rate'] ?>'>
								<input type='hidden' name='editable' value='<?= $tabs['author_review']['editable'] ?>'>
								<div class='label'>Отзыв автора / Оценка исполнения</div>
								<div class='value'></div>
							</div>
							<div class='field author_comment'>
								<div class='label'>Отзыв автора / Комментарий</div>
								<?php if($tabs['author_review']['editable']): ?>
									<div class='editor'>
										<textarea></textarea>
									</div>
								<?php else: ?>
									<div class='value text'>
										<?= $task['author_review_comment'] ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
					<?php if($tabs['assignee_review']['display']): ?>
						<div class='tab_content assignee_review' style='display: none'>
							<?php if($tabs['assignee_review']['editable']): ?>
								<div class='controls'>
									<button class='save' disabled>Сохранить</button>
								</div>
							<?php endif; ?>
							<div class='field assignee_comment'>
								<div class='label'>Отзыв исполнителя</div>
								<?php if($tabs['assignee_review']['editable']): ?>
									<div class='editor'>
										<textarea></textarea>
									</div>
								<?php else: ?>
									<div class='value text'>
										<?= $task['assignee_review_comment'] ?>
									</div>
								<?php endif; ?>
							</div>
							<div class='assignee_rate'>
								<div class='label'>
									Отзыв исполнителя / SMART
								</div>
								<div class='smart_rating'>
									<input type='hidden' name='specific' value='<?= $task['assignee_review_specific'] ?>'>
									<input type='hidden' name='measurable' value='<?= $task['assignee_review_measurable'] ?>'>
									<input type='hidden' name='attainable' value='<?= $task['assignee_review_attainable'] ?>'>
									<input type='hidden' name='relevant' value='<?= $task['assignee_review_relevant'] ?>'>
									<input type='hidden' name='timebound' value='<?= $task['assignee_review_timebound'] ?>'>
									<input type='hidden' name='editable' value='<?= $tabs['assignee_review']['editable'] ?>'>
									<table>
										<tbody>
											<tr class='specific'>
												<td class='label'>Точное</td>
												<td class='value'></td>
											</tr>
											<tr class='measurable'>
												<td class='label'>Измеримое</td>
												<td class='value'></td>
											</tr>
											<tr class='attainable'>
												<td class='label'>Достижимое</td>
												<td class='value'></td>
											</tr>
											<tr class='relevant'>
												<td class='label'>Уместное</td>
												<td class='value'></td>
											</tr>
											<tr class='timebound'>
												<td class='label'>Сроки</td>
												<td class='value'></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<?php
							/*
							<div class='field'>
								<div class='label'>Радар-диаграмма SMART</div>
								<div class='value'></div>
							</div>
							*/
							?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</main>
	<?php require_once($st->config["mainDirPath"]."/footer.php") ?>
	<div class='dummy' style='display: none'>
		<div class='comment'>
			<input type='hidden' name='task_comment_id' value=''>
			<div class='comment_caption'>
				<div class='datetime'>
					<div class='date_icon'></div>
					<div class='date_text'></div>
					<div class='time_icon'></div>
					<div class='time_text'></div>
				</div>
				<div class='author'>
					<div class='profile'>
						<div class='avatar' style='background-image: url("")'>
							<div class='status'></div>
						</div>
						<div class='info'>
							<div class='name'>
								<span class='firstname'></span>&nbsp;<span class='lastname'></span>
							</div>
							<div class='role_name'></div>
							<div class='department_name'></div>
						</div>
					</div>
				</div>
			</div>
			<div class='comment_body'></div>
		</div>
	</div>
</body>
</html>