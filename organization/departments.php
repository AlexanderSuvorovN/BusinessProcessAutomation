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
	<?= $st->Style("/staff.css", true) ?>
	<?= $st->JQuery() ?>
	<?= $st->Script("/st.js") ?>
</head>
<body>
	<?php $st->Header() ?>
	<main>
		<?php require_once($st->config["mainDirPath"]."/sidebar.php") ?>
		<div class="view">
			<h2>Отделы</h2>
			<div class='ajax'></div>
			<table id="departments">
				<tbody>
					<?php foreach($data['departments'] as $department): ?>
						<tr>
							<td><?= $department['name'] ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<h2>Сотрудники</h2>
			<table id="employees">
				<tbody>
					<?php foreach($data['employees'] as $employee): ?>
						<tr>
							<td>
								<div class='avatar' style='background-image: url(<?= $employee['person_photo'] ?>)'></div>
							</td>
							<td>
								<?= $employee['employee_id'] ?>						
							</td>
							<td>
								<?= $employee['person_firstname'] ?>						
							</td>
							<td>
								<?= $employee['person_lastname'] ?>
							</td>
							<td>
								<?= $employee['employee_date_started'] ?>						
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<h2>Должности</h2>
			<table id="roles">
				<thead>
					<th>Код</th>
					<th>Должность</th>
					<th>Отдел</th>
					<th>Сотрудник</th>
					<th>Ставка</th>
				</thead>
				<tbody>
					<?php foreach($data['roles'] as $role): ?>
						<tr>
							<td><?= $role['role_id'] ?></td>
							<td><?= $role['role_name'] ?></td>
							<td><?= $role['department_name'] ?></td>
							<td><?= $role['person_name'] ?></td>
							<td><?= $role['role_employee_rate'] ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</main>
	<?php require_once($st->config["mainDirPath"]."/footer.php") ?>
</body>
</html>