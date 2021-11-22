<?php
	require_once("./../st.php");
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
	<?= $st->Style("/tasks.css", true) ?>
	<?= $st->Style('/libs/nj-timepicker/njtimepicker.css') ?>
	<?= $st->JQuery() ?>
	<?= $st->Script('/libs/nj-timepicker/njtimepicker.js') ?>
</head>
<body>
	<?php $st->Header() ?>
	<main>
		<?php require_once($st->config["mainDirPath"]."/sidebar.php") ?>
		<div class="view">
			<h2>NJ Timepicker</h2>
		    <div class="wrapper">
		        <label for="format_12">12 Hour</label>
		        <div class="container" id="format_12">
		            --:-- --
		        </div>
		    </div>
		    <div class="wrapper">
		        <label for="format_24">24 Hour</label>
		        <div class="container" id="format_24">
		            --:--
		        </div>
		    </div>
		    <!-- <script src="/libs/nj-timepicker/njtimepicker.js"></script> -->
		    <script>
		        (function () {
		            let format_12 = document.querySelector('#format_12');
		            var format_12_picker = new NJTimePicker({
		                targetID: 'format_12',
		                autoSave: true,
		                texts: {
		                    header: '12-Hour Picker'
		                }
		            });
		            format_12_picker.on('save', function (data) {
		                if (data.fullResult)
		                    format_12.textContent = data.fullResult;
		            });
		            format_12_picker.on('ready', function (data) {
		                return
		                format_12_picker.setValue({
		                    hours: 12,
		                    minutes: 45,
		                    ampm: 'am'
		                });
		            });

		            let format_24 = document.querySelector('#format_24');
		            var format_24_picker = new NJTimePicker({
		                targetEl: format_24,
		                texts: {
		                    header: '24-Hour Picker'
		                },
		                format: '24'
		            });
		            format_24_picker.on('save', function (data) {
		                if (data.fullResult)
		                    format_24.textContent = data.fullResult;
		            });
		        })();
		    </script>
	</main>
	<?php $st->Footer() ?>
</body>
</html>