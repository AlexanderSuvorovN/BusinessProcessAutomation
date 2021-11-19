<?php
	require_once("./st.php");
	$data = array();
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
	<?= $st->Style("/stats.css", true) ?>
	<?= $st->JQuery() ?>
	<?= $st->Script('/libs/thedatepicker/the-datepicker.js') ?>
	<?= $st->Script('/libs/richtexteditor/rte.js') ?>
	<?= $st->Script('/libs/richtexteditor/lang/rte-lang-ru.js') ?>
	<?= $st->Script('/libs/richtexteditor/plugins/all_plugins.js') ?>
	<?= $st->Script("/st.js") ?>
	<?= $st->Script("/stats.js") ?>
	<?= $st->Script("https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js") ?>
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
				<span class='current'>Статистика</span>
			</div>
			<h2>Статистика</h2>
			<div class='links'>
				<a href='/stats/terminals.php'>Статистика терминалов</a>
				<a href='/stats/tasks.php'>Статистика задач</a>
			</div>
		</div>
	</main>
	<?php require_once($st->config["mainDirPath"]."/footer.php") ?>
</body>
</html>