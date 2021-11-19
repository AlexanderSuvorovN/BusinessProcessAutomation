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
	<?= $st->Style($st->config['thisDirUrl']."login.css", true) ?>
	<?= $st->JQuery() ?>
	<?= $st->Script($st->config['thisDirUrl']."login.js") ?>
</head>
<body>
	<div class="abstract">
		<div class="center">
			<div class="smart-teams">
				<span class="bold">Smart</span><span class="light">Teams&trade;</span>&nbsp;Бизнес
			</div>
			<div class="form login">
				<div class="caption">
					Авторизация
				</div>
				<div class="inputs">
					<input name="email" type="text" placeholder="Email">
					<input name="password" type="password" placeholder="Пароль">
				</div>
				<div class="controls">
					<div class="remember">
						<div class="checkbox"></div>
						<div class="label">Запомнить меня</div>
						<input name="remember" type="hidden">
					</div>
					<button>
						Войти
					</button>
				</div>
				<div class="links">
					<a href="/forgotpassword">Я не помню пароль</a>
					<a href="/newuser">Регистрация нового пользователя</a>
				</div>
			</div>
		</div>
	</div>
</body>
</html>