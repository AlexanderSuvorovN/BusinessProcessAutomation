<?php
	require_once("./st.php");
	$data = array();
	$user = $_REQUEST['user_id'] ?? $st->user['id'];
	$author_assignee = $_REQUEST['author_assignee'] ?? 'all';
	$status = $_REQUEST['status'] ?? 'all';
	$predefined_filter = $_REQUEST['predefined_filter'] ?? '';
	// $filter_author_assignee = (isset($_SESSION['ui']['tasks']['filter']['author_assignee'])) ? $_SESSION['ui']['tasks']['filter']['author_assignee'] : 'all';
	// $filter_status = (isset($_SESSION['ui']['tasks']['filter']['status'])) ? $_SESSION['ui']['tasks']['filter']['status'] : 'all';
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
	<?= $st->Style("/tasks.css", true) ?>
	<?= $st->JQuery() ?>
	<?= $st->Script('/libs/thedatepicker/the-datepicker.js') ?>
	<?= $st->Script('/libs/richtexteditor/rte.js') ?>
	<?= $st->Script('/libs/richtexteditor/lang/rte-lang-ru.js') ?>
	<?= $st->Script('/libs/richtexteditor/plugins/all_plugins.js') ?>
	<?= $st->Script("/st.js") ?>
	<?= $st->Script("/tasks.js") ?>
</head>
<body>
	<input type='hidden' name='user_id' value='<?= $st->user['id'] ?>'>
	<input type='hidden' name='user_authorization_level' value='<?= $st->user['authorization_level'] ?>'>
	<input type='hidden' name='person_firstname' value='<?= $st->user['person_firstname'] ?>'>
	<input type='hidden' name='person_lastname' value='<?= $st->user['person_lastname'] ?>'>
	<?php $st->Header() ?>
	<main>
		<?php require_once($st->config["mainDirPath"]."/sidebar.php") ?>
		<div class="view">
			<div class='breadcrumb'>
				<a href='/'><img src='/images/ui/icon.home.png'></a>
				<span class='separator'>&raquo;</span>
				<span class='current'>Задачи</span>
			</div>
			<h2>Задачи</h2>
			<div class="controls">
				<?php if(in_array($st->user['authorization_level'], ['administrator', 'general_manager'])): ?>
				<div class='fieldset user'>
					<div class='label'>
						Сотрудник
					</div>
					<div class='value'>
						<select name='user'></select>
						<input type='hidden' name='user' value='<?= $user ?>'>
					</div>
				</div>
				<?php endif; ?>
				<div class='fieldset author_assignee'>
					<div class='label'>
						Автор / Исполнитель
					</div>
					<div class='value'>
						<input type='hidden' name='author_assignee' value='<?= $author_assignee ?>'>
						<select name='author_assignee'>
							<option value='author'>Созданные</option>
							<option value='assignee'>Назначенные</option>
							<option value='all'>Все</option>
						</select>
					</div>
				</div>
				<div class='fieldset status'>
					<div class='label'>
						Статус
					</div>
					<div class='value'>
						<input type='hidden' name='status' value='<?= $status ?>'>
						<select name='status'>
							<option value='created'>Создана</option>
							<option value='assigned'>Назначена</option>
							<option value='declined'>Отклонена</option>
							<option value='implementation'>Выполнение</option>
							<option value='completed'>Выполнена</option>
							<option value='cancelled'>Отменена</option>
							<option value='removed'>Отозвана</option>
							<option value='aborted'>Прервана</option>
							<option value='reviewed_by_author'>Получен отзыв автора</option>
							<option value='reviewed_by_assignee'>Получен отзыв исполнителя</option>
							<option value='closed'>Закрыта</option>
							<option value='all'>Все</option>
						</select>
					</div>
				</div>
				<div class='fieldset predefined_filter'>
					<div class='label'>
						Предопределённый фильтр
					</div>
					<div class='value'>
						<input type='hidden' name='predefined_filter' value='<?= $predefined_filter ?>'>
						<select name='predefined_filter'>
							<option value=''></option>
							<option value='in_progress'>Выполняется</option>
							<option value='completed'>Выполнено</option>
							<option value='completed_on_time'>Выполнено в срок</option>
							<option value='completed_overdue'>Выполнено с просрочкой</option>
							<option value='overdue'>Просрочено</option>
						</select>
					</div>
				</div>
				<button class="add">Создать</button>
			</div>
			<div class='ajax'></div>
		</div>
	</main>
	<?php require_once($st->config["mainDirPath"]."/footer.php") ?>
	<div class='window_task_add' data-window-titlebar-caption="Добавить задачу" style='display: none'>
		<div class="fieldset">
			<span class='label'>Название</span>
			<input name="name" type="text" autocomplete="off">
		</div>
		<div class="fieldset">
			<span class='label'>Описание</span>
			<textarea name='description'></textarea>
		</div>
		<div class="fieldset date_begin">
			<span class='label'>Начало</span>
			<div class="dateset date_begin">
				<input class="date" name="date_begin" type="text">
				<button class="pick date"></button>
				<input class="time" name="time_begin" type="text">
				<button class="pick time"></button>
			</div>
		</div>
		<div class="fieldset date_end">
			<span class='label'>Завершение</span>
			<div class="dateset date_end">
				<input class="date" name="date_end" type="text">
				<button class="pick date"></button>
				<input class="time" name="time_end" type="text">
				<button class="pick time"></button>
			</div>
		</div>
		<div class="fieldset assignee">
			<span class='label'>Назначение</span>
			<select class='assignment_candidates'>
				<option value=''></option>
				<?php foreach($assignment_candidates as $candidate): ?>
					<option value='<?= $candidate['user_id'] ?>'>
						<?= $candidate['person_firstname'] ?>&nbsp;<?= $candidate['person_lastname'] ?>&nbsp;/&nbsp;<?= $candidate['role_name'] ?>&nbsp;/&nbsp;<?= $candidate['department_name'] ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="controls">
			<button class="cancel">Отмена</button>
			<button class="save">Сохранить</button>
		</div>
		<div class="dummy" style='display: none'>
			<div class='selected_candidates'>
				<div class='profile'>
					<div class='avatar'></div>
					<div class='info'>
						<div class="name">
							<span class="firstname"></span>&nbsp;<span class="lastname"></span>
						</div>
						<div class="role_name"></div>
						<div class="department_name"></div>
					</div>
					<div class="button_close"></div>
				</div>
			</div>
		</div>
	</div>
	<div class='dummy' style='display: none'>
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
		<div class='terms'>
			<div class="task_date_begin">
				<div class="icon"></div>
				<div class="text"></div>
			</div>
			<div class="task_date_end">
				<div class="icon"></div>
				<div class="text"></div>
			</div>
			<div class="task_date_completed">
				<div class="icon"></div>
				<div class="text"></div>
			</div>
		</div>
	</div>
</body>
</html>