<?php
	require_once("./st.php");
	/*
	$data = array();
	$st->db_connect();
	$s = $st->dbh->prepare("SELECT `id`, `name` FROM `departments`");
	$s->execute();
	$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
	if($fetch)
	{
		$data['departments'] = array();
		foreach($fetch as $row)
		{
			$data['departments'][] = $row;
		}
	}
	else
	{
		$data['departments'] = array();
	}
	$s = $st->dbh->prepare("SELECT `e`.`id` AS `employee_id`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `p`.`photo` AS `person_photo`, `e`.`date_started` AS `employee_date_started` FROM `employees` AS `e` JOIN `persons` AS `p` ON (`e`.`person_id` = `p`.`id`)");
	$s->execute();
	$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
	if($fetch)
	{
		$data['employees'] = array();
		foreach($fetch as $row)
		{
			$data['employees'][] = $row;
		}
	}
	else
	{
		$data['employees'] = array();
	}	
	$s = $st->dbh->prepare('SELECT `r`.`id` AS `role_id`, `r`.`name` AS `role_name`, `d`.`name` AS `department_name`, `p`.`firstname` AS `person_firstname`, `p`.`lastname` AS `person_lastname`, `r`.`rate` AS `role_employee_rate` FROM `roles` AS `r` JOIN `departments` AS `d` ON (`d`.`id` = `r`.`department_id`) LEFT JOIN `employees` AS `e` ON (`r`.`employee_id` = `e`.`id`) LEFT JOIN `persons` AS `p` ON (`e`.`person_id` = `p`.`id`)');
	$s->execute();
	$fetch = $s->fetchAll(PDO::FETCH_ASSOC);
	if($fetch)
	{
		$data['roles'] = array();
		foreach($fetch as $row)
		{
			$row['person_name'] = $row['person_firstname']." ".strtoupper($row['person_lastname']);
			if(empty(trim($row['person_name'])))
			{
				$row['person_name'] = '-';
			}
			if(empty($row['role_employee_rate']))
			{
				$row['role_employee_rate'] = '-';
			}
			$data['roles'][] = $row;
		}
	}
	else
	{
		$data['roles'] = array();
	}
	$st->db_close();
	*/
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