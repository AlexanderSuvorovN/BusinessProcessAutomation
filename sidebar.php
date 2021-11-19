<?php
	if(!empty($st->user['user_avatar']))
	{
		$avatar = $st->user['user_avatar'];
	}
	else
	{
		$avatar = $st->user['person_photo'];
	}
?>
	<div class="sidebar">
		<div class="profile">
			<div class="avatar" style="background-image: url('<?= $avatar ?>')"></div>
			<div class="info">
				<div class="name">
					<span class="firstname"><?= $st->user['person_firstname'] ?></span>
					<span class="lastname"><?= $st->user['person_lastname'] ?></span>
				</div>
				<div class="status online">
					<span class="text"><?= $st->user['status'] ?></span>
				</div>
			</div>
		</div>
		<div class="nav">
			<input name='st_navitem' type='hidden' value='<?= $st->navitem ?>'>
			<a class="navitem main" href="/main">
				<img src="/images/icon_main.png">
				<span>Главная</span>
			</a>
			<a class="navitem staff" href="/staff">
				<img src="/images/icon_staff.png">
				<span>Сотрудники и отделы</span>
			</a>
			<a class="navitem tasks" href="/tasks">
				<img src="/images/icon_tasks.png">
				<span>Задачи</span>
			</a>
			<a class="navitem journal" href="/journal">
				<img src="/images/ui/icon.journal.png">
				<span>Журнал</span>
			</a>		
			<a class="navitem stats" href="/stats">
				<img src="/images/icon_statistics.png">
				<span>Статистика</span>
			</a>
			<a class="navitem conference" href="https://videoconf.smart-teams.ru:5443" target="_blank">
				<img src="/images/icon_conference.png">
				<span>Конференция</span>
			</a>
			<?php
			/*
			<a class="navitem assignments" href="/assignments">
				<img src="/images/icon_assignments.png">
				<span>Домашнее задание</span>
			</a>		
			<a class="navitem documents" href="/documents">
				<img src="/images/icon_documents.png">
				<span>Учебные материалы</span>
			</a>
			<a class="navitem schedule" href="/schedule">
				<img src="/images/icon_schedule.png">
				<span>Расписание уроков</span>
			</a>
			*/
			?>
		</div>
	</div>