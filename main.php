<?php
	require_once("./st.php");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<?= $st->Meta() ?>
	<?= $st->Title() ?>
	<?= $st->FavIcon() ?>
    <?= $st->Fonts() ?>
	<?= $st->Style("/st.css", true) ?>
	<?= $st->Style("/header.css", true) ?>
	<?= $st->Style("/sidebar.css", true) ?>
	<?= $st->Style("/footer.css", true) ?>
	<?= $st->Style("/main.css", true) ?>
	<?= $st->JQuery() ?>
	<?= $st->Script("/st.js") ?>
	<?= $st->Script("/main.js") ?>
</head>
<body>
	<?php $st->Header() ?>
	<main>
		<?php require_once($st->config["mainDirPath"]."/sidebar.php") ?>
		<div class="view">
			<h2>Главная</h2>
			<div class='dashboard'>
				<div class='col-1'>
					<h3><a href='/tasks'>Задачи</a></h3>
					<div class='tasks_ajax'>
					</div>
					<!-- Оповещения -->
					<h3>Уведомления</h3>
					<div class='notifications_ajax'>
					</div>
					<!-- Активные задачи -->
				</div>
				<div class='col-2'>
					<embed src='<?= $st->user['roles'][0]['role_duties'] ?>' width='100%' height='100%' />
				</div>
			</div>
		</div>
	</main>
	<?php require_once($st->config["mainDirPath"]."/footer.php") ?>
</body>
</html>