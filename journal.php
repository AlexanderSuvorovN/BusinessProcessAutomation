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
	<?= $st->Style("/journal.css", true) ?>
	<?= $st->JQuery() ?>
	<?= $st->Script('/libs/thedatepicker/the-datepicker.js') ?>
	<?= $st->Script('/libs/richtexteditor/rte.js') ?>
	<?= $st->Script('/libs/richtexteditor/lang/rte-lang-ru.js') ?>
	<?= $st->Script('/libs/richtexteditor/plugins/all_plugins.js') ?>
	<?= $st->Script("/st.js") ?>
	<?= $st->Script("/journal.js") ?>
	<?= $st->Script("https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js") ?>
	<?= $st->Script("https://cdn.amcharts.com/lib/4/core.js") ?>
	<?= $st->Script("https://cdn.amcharts.com/lib/4/charts.js") ?>
	<?= $st->Script("https://cdn.amcharts.com/lib/4/themes/animated.js") ?>
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
				<span class='current'>Журнал</span>
			</div>
			<h2>Журнал</h2>
			<div class="controls">
				<?php if(in_array($st->user['authorization_level'], ['administrator', 'general_manager'])): ?>
				<div class='fieldset employee'>
					<div class='label'>
						Сотрудник
					</div>
					<div class='value'>
						<select name='employee'></select>
					</div>
				</div>
				<?php endif; ?>
				<div class='fieldset month'>
					<div class='label'>
						Месяц
					</div>
					<div class='value'>
						<button class='prev'></button>
						<select name='month'>
							<option value='0'>Январь</option>
							<option value='1'>Фервраль</option>
							<option value='2'>Март</option>
							<option value='3'>Апрель</option>
							<option value='4'>Май</option>
							<option value='5'>Июнь</option>
							<option value='6'>Июль</option>
							<option value='7'>Август</option>
							<option value='8'>Сентябрь</option>
							<option value='9'>Октябрь</option>
							<option value='10'>Ноябрь</option>
							<option value='11'>Декабрь</option>
						</select>
						<button class='next'></button>
					</div>
				</div>
				<div class='fieldset year'>
					<div class='label'>
						Год
					</div>
					<div class='value'>
						<!-- <input type='hidden' name='filter_status' value='<?= $filter['status'] ?>'> -->
						<select name='year'>					
							<option value='2021'>2021</option>
							<option value='2020'>2020</option>
						</select>
					</div>
				</div>
			</div>
			<div class='journal_ajax'>				
			</div>
		</div>
	</main>
	<?php require_once($st->config["mainDirPath"]."/footer.php") ?>
	<div class='dummy' style='display: none'>
		<div class='window journal_entry'>
			<div class='caption'>
				<div class='icon'></div>
				<div class='text'>Установить оценку</div>
				<div class='close'></div>
			</div>
			<div class='body'>
				<div class='fieldset date'>
					<div class='label'>
						Дата
					</div>
					<div class='value'>
						<div class='icon'></div>
						<div class='text'></div>
					</div>
				</div>
				<div class='fieldset comment'>
					<div class='label'>
						Комментарий
					</div>
					<div class='value'>
						<div class='editor'></div>
					</div>
				</div>
				<div class='fieldset criteria'>
					<div class='label'>
						Критерий
					</div>
					<div class='value'>
						<input type='text' disabled>
						<button></button>
					</div>
				</div>
				<div class='fieldset grade'>
					<div class='label'>
						Оценка
					</div>
					<div class='value'>
						<div class='grades disabled'>
							<div class='item'>1</div>
							<div class='item'>2</div>
							<div class='item'>3</div>
							<div class='item'>4</div>
							<div class='item'>5</div>
						</div>
					</div>
				</div>
				<div class='fieldset attachments'>
					<div class='label'>
						Приложения
					</div>
					<div class='value'>
					</div>
					<div class='controls'>
						<button class='add'>Добавить</button>
					</div>
				</div>
				<div class='controls'>
					<button class='cancel'>Отмена</button>
					<button class='save'>Сохранить</button>
				</div>
			</div>
		</div>
		<div class='window criteria'>
		<div class='caption'>
				<div class='icon'></div>
				<div class='text'>Установить критерий оценки</div>
				<div class='close'></div>
			</div>
			<div class='body'>
				<div class='fieldset criterias'>
					<div class='label'>
						Доступные критерии
					</div>
					<div class='value'>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>