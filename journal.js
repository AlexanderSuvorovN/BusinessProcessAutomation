$(function()
{
	function journalEntryDialog(o)
	{
		let journal_entry = {};
		if(o.type === 'new')
		{
			journal_entry.date = o.date;
			journal_entry.comment = null;
			journal_entry.criteria = null;
			journal_entry.grade = null;
			journal_entry.attachments = null;
		}
		if(o.type === 'edit')
		{
			journal_entry = o.journal_entry;
		}
		let overlay_node = $('<div>').addClass('st_modal_overlay');
		let window_node = $('body > div.dummy > div.window.journal_entry').clone();
		window_node.find('div.caption > div.close').on('click',
			function(e)
			{
				overlay_node.remove();
			});
		window_node.find('div.body > div.controls > button.cancel').on('click',
			function(e)
			{
				overlay_node.remove();
			});
		window_node.find('div.body > div.fieldset.date > div.value > div.text').html(journal_entry.date);
		if(journal_entry.comment !== null)
		{
			window_node.find('div.body > div.fieldset.comment > div.value > div.editor').html(journal_entry.comment);
		}
		if(journal_entry.criteria !== null)
		{
			window_node.find('div.body > div.fieldset.criteria > div.value > input[type="text"]').val(journal_entry.criteria);
		}
		if(['administrator', 'general_manager'].includes(st.user.authorization_level))
		{
			window_node.find('div.body > div.fieldset.criteria > div.value > input[type="text"]').attr('disabled', false);
		}
		if(['administrator', 'general_manager'].includes(st.user.authorization_level))
		{
			window_node.find('div.body > div.fieldset.criteria > div.value > button').on('click', 
				function(e)
				{
					let input_node = window_node.find('div.body > div.fieldset.criteria > div.value > input[type="text"]');
					let overlay_node = $('<div>').addClass('st_modal_overlay');
					let criteria_window_node = $('body > div.dummy > div.window.criteria').clone();
					criteria_window_node.find('div.caption > div.close').on('click',
						function(e)
						{
							overlay_node.remove();
						});
					criteria_window_node.find('div.body > div.controls > button.cancel').on('click',
						function(e)
						{
							overlay_node.remove();
						});
					$.ajax(
					{
						url: "/journal/ajax.get.criterias.php", 
						method: "POST",
						data:
							{
								user_id: Number(controls_employee.val().trim())
							},
						dataType: 'json'
					})
					.done(
						function(response)
						{
							// console.log(response);
							if(response.status === 'success')
							{													
								let criterias = response.data.criterias;
								let value_node = criteria_window_node.find('div.body > div.fieldset.criterias > div.value');													
								criterias.forEach(
									function(val, ix)
									{
										if(val.criteria === null)
										{
											return true;
										}
										let criteria_node = $('<div>').addClass('item').html(val.criteria);
										criteria_node.on('click', function(e)
											{
												// $(this).addClass('set');
												// $(this).siblings('.set').removeClass('set');
												input_node.val($(this).html().trim());
												overlay_node.remove();
											});
										value_node.append(criteria_node);
									});
							}
							else
							{
								alert('Возникла ошибка при выборке записей критерий из базы данных. Пожалуйста, обратитесь в службу поддержки SmartTeams.');
								overlay_node.remove();
							}
						});
					overlay_node.append(criteria_window_node);
					$('body').append(overlay_node);
				});
		}
		if(['employee'].includes(st.user.authorization_level))
		{
			window_node.find('div.body > div.fieldset.criteria > div.value > button').remove();
		}
		if(journal_entry.grade !== null)
		{
			let ix = journal_entry.grade - 1;
			let value_node = window_node.find('div.body > div.fieldset.grade > div.value > div.grades > div.item').eq(ix).addClass('set');
		}
		if(['administrator', 'general_manager'].includes(st.user.authorization_level))
		{
			window_node.find('div.body > div.fieldset.grade > div.value > div.grades').removeClass('disabled');
			window_node.find('div.body > div.fieldset.grade > div.value > div.grades > div.item').on('click',
				function(e)
				{
					$(this).siblings().removeClass('set');
					$(this).toggleClass('set');
				});
		}		
		if(journal_entry.attachments !== null)
		{
			let value_node = window_node.find('div.body > div.fieldset.attachments > div.value');
			journal_entry.attachments.forEach(
				function(val, ix)
				{
					let attachment_node = $('<div>').addClass('attachment');
					let filename_node = $('<div>').addClass('filename');
					let link_node = $('<a>')
						.attr('href', val.upload_dir+'/'+val.filename+'.'+val.ext)
						.attr('target', '_blank')
						.html(val.filename+'.'+val.ext);
					filename_node.append(link_node);
					attachment_node.append(filename_node);
					value_node.append(attachment_node);
				});
		}
		if(['administrator', 'general_manager'].includes(st.user.authorization_level))
		{
			window_node.find('div.body > div.fieldset.attachments > div.value > div.attachment').each(
				function(ix, val)
				{
					let attachment_node = $(val);
					let remove_node = $('<div>').addClass('remove');
					remove_node.on('click', 
						function(e)
						{
							attachment_node.addClass('remove');
						});
					attachment_node.append(remove_node);					
				});
			function fileChange(e)
			{
				// console.log(e);
				// console.log(this);
				let input_node = $(this);
				let basename = input_node.val().split('\\').pop();
				let attachment_node = input_node.parent('div.attachment');
				attachment_node.siblings().each(
					function(ix, val)
					{
						let existing  = $(val).find('div.filename > a');
						let specified = $(val).find('input[type="file"]');
						let filename;
						if(existing.length > 0)
						{
							filename = existing.text().trim();
						}
						if(specified.length > 0)
						{
							filename = specified.val().trim();
						}
						if(filename === basename)
						{
							alert('Файл с таким именем уже существует: удалите существующий файл с таким же именем.');
							input_node.val('');
						}
					});
			}
			window_node.find('div.body > div.fieldset.attachments > div.controls > button').on('click',
					function(e)
					{
						let value_node = window_node.find('div.body > div.fieldset.attachments > div.value');
						let attachment_node = $('<div>').addClass('attachment').addClass('input');
						let input_node = $('<input>').attr('type', 'file');
						let remove_node = $('<div>').addClass('remove');
						input_node.on('change', fileChange);
						remove_node.on('click', (e) => attachment_node.remove());
						attachment_node
							.append(input_node)
							.append(remove_node);
						value_node.append(attachment_node);
					});
			let attachment_node = $('<div>').addClass('attachment').addClass('input');
			let input_node = $('<input>').attr('type', 'file');
			let remove_node = $('<div>').addClass('remove');
			input_node.on('change', fileChange);
			remove_node.on('click', (e) => attachment_node.remove());
			attachment_node
				.append(input_node)
				.append(remove_node);
			window_node.find('div.body > div.fieldset.attachments > div.value').append(attachment_node);
		}
		if(['employee'].includes(st.user.authorization_level))
		{
			window_node.find('div.body > div.fieldset.attachments > div.controls').remove();
		}
		if(['administrator', 'general_manager'].includes(st.user.authorization_level))
		{
			window_node.find('div.body > div.controls > button.save').on('click',
				function(e)
				{												
					let formData = new FormData();
					let user_id = Number(controls_employee.val().trim());
					formData.append('user_id', user_id);
					let date = window_node.find('div.body > div.fieldset.date > div.value > div.text').text().trim();
					formData.append('date', date);
					let comment = gradeEditor.getHTMLCode();
					formData.append('comment', comment);
					let grade = window_node.find('div.body > div.fieldset.grade > div.value > div.grades > div.item.set').index();
					if(grade >= 0)
					{
						grade += 1;
						formData.append('grade', grade);
					}
					else
					{
						grade = null;
					}
					let criteria = window_node.find('div.body > div.fieldset.criteria > div.value > input[type="text"]').val().trim();
					if(criteria !== '')
					{
						formData.append('criteria', criteria);
					}
					let remove_nodes = window_node.find('div.body > div.fieldset.attachments > div.value > div.attachment.remove');
					if(remove_nodes.length > 0)
					{
						remove_nodes.each(
							function(ix, val)
							{
								formData.append('remove[]', $(val).find('div.filename > a').text().trim());
							});
					}
					else
					{
						remove = null;
					}
					window_node.find('div.body > div.fieldset.attachments > div.value > div.attachment > input[type="file"]').each(
						function(ix, val)
						{
							formData.append('attachments[]', val.files[0]);
						});
				    $.ajax({
				        type: "POST",
				        url: "/journal/ajax.add.journal_entry.php",
				        async: true,
				        data: formData,
				        cache: false,
				        contentType: false,
				        processData: false,
				        timeout: 60000,
				        dataType: 'json'
				    })
				    .done(
				    	function(response)
				    	{
				    		// console.log(response);
				    		if(response.status === 'success')
				    		{
								overlay_node.remove();										
								updateJournal();							    		
				    		}
				    		else
				    		{
					    		// console.log(response);
					    		alert('Возникла ошибка при создании / обновлении записи журнала. Пожалуйста, обратитесь в службу поддержки SmartTeams.');
				    		}
				    	});
				});
		}
		if(['employee'].includes(st.user.authorization_level))
		{
			window_node.find('div.body > div.controls').remove();
		}
		overlay_node.append(window_node);
		$('body').append(overlay_node);
		if(['administrator', 'general_manager'].includes(st.user.authorization_level))
		{
			var gradeEditor = new RichTextEditor(window_node.find('div.body > div.fieldset.comment > div.value > div.editor').get(0));
		}
	}
	function updateJournal(e)
	{
		let user_id = (controls_employee.length > 0) ? Number(controls_employee.val().trim()) : st.user.id;
		let journal_month = controls_month.val();
		let journal_year = controls_year.val();		
		// получим последний день месяца: 
		// https://stackoverflow.com/questions/222309/calculate-last-day-of-month-in-javascript	
		let first_day = 1;
		let last_day;
		if(journal_month !== 11)
		{
			// console.log('journal year: ' + journal_year);
			// console.log('journal month: ' + journal_month);
			let tmp = new Date(journal_year, Number(journal_month) + 1, 1);
			tmp = new Date(tmp - 1);			
			last_day  = tmp.getDate();
			// console.log('last day: ' + last_day);
		}
		else
		{
			let tmp = new Date(journal_year + 1, 0, 1);
			tmp = new Date(tmp - 1);
			last_day  = tmp.getDate();
		}
		let journal_node = $('<div>').addClass('journal');
		for(let day = 1; day <= last_day; day++)
		{
			let dto_day = new Date(journal_year, journal_month, day);
			let weekday = {};
			weekday.number = dto_day.getDay();
			weekday.abbr = weekdays_abbr[weekday.number];
			let item_node = $('<div>').addClass('item');
			if(weekday.number === 0 || weekday.number === 6)
			{
				item_node.addClass('weekend');
			}
			let date_node = $('<div>').addClass('date');		
			let weekday_node = $('<div>').addClass('weekday');
			weekday_node.html(weekday.abbr);
			let day_node = $('<div>').addClass('day');
			day_node.html(day);
			date_node
				.append(weekday_node)
				.append(day_node);
			date_node.on('click',
				function(e)
				{
					journalEntryDialog(
						{
							type: 'new',
							date: journal_year.toString().padStart(2, '0') + '-' + (Number(journal_month) + 1).toString().padStart(2, '0') + '-' + day.toString().padStart(2, '0')
						});
				});
			item_node
				.append(date_node);
			journal_node.append(item_node);
		}
		journal_ajax_node.empty().append(journal_node);		
		$.ajax(
		{
			url: "/journal/ajax.get.journal_entries.php", 
			method: "POST",
			data: 
				{
					user_id: user_id,
					month: Number(journal_month) + 1,
					year: journal_year
				},
			dataType: "json"
		})
		.done(
			function(response)
			{
				// console.log(response);
				if(response.status === 'success')
				{
					let journal_entries = response.data.journal_entries;
					let criteria_entries = {};
					for(let entry of response.data.criteria_entries)
					{
						let criteria = (entry.criteria !== null) ? entry.criteria : '';
						criteria_entries[criteria] = {};
						criteria_entries[criteria].node = $('<div>').addClass('criteria');
						criteria_entries[criteria].header_node = $('<h3>').html(criteria);
						criteria_entries[criteria].grades_node = $('<div>').addClass('grades');
						criteria_entries[criteria].node
							.append(criteria_entries[criteria].header_node)
							.append(criteria_entries[criteria].grades_node);
						criteria_entries[criteria].grades = {};
						journal_ajax_node.append(criteria_entries[criteria].node);
					}
					journal_entries.forEach(
						function(val, ix)
						{
							let criteria = (val.criteria !== null) ? val.criteria : '';
							criteria_entries[criteria].grades[val.date] = 
								{
									grade: (val.grade !== null) ? Number(val.grade) : null,
									comment: (val.comment !== null) ? true : false,
									attachments: (val.attachments_count > 0) ? true : false
								};
						});
					for(let criteria in criteria_entries)
					{
						for(let day = 1; day <= last_day; day++)
						{
							let item_node = $('<div>').addClass('item');
							let attachments_node = $('<div>').addClass('attachments');
							let comment_node = $('<div>').addClass('comment');
							let date = journal_year.toString().padStart(2, '0') + '-' + (Number(journal_month) + 1).toString().padStart(2, '0') + '-' + day.toString().padStart(2, '0');
							if(criteria_entries[criteria].grades[date] !== undefined && criteria_entries[criteria].grades[date].grade !== null)
							{
								let grade = criteria_entries[criteria].grades[date].grade;
								item_node.addClass('grade-' + grade.toString()).html(grade);
								if(criteria_entries[criteria].grades[date].comment === true)
								{
									item_node.append(comment_node);
								}
								if(criteria_entries[criteria].grades[date].attachments === true)
								{
									item_node.append(attachments_node);
								}
							}
							else
							{
								item_node.html('-');
							}
							item_node.on('click', 
								function(e)
								{
									$.ajax(
									{
										url: "/journal/ajax.get.journal_entry.php", 
										method: "POST",
										data: 
											{
												user_id: user_id,
												date: date,
												criteria: criteria
											},
										dataType: 'json'
									})
									.done(
										function(response)
										{
											// console.log(response);
											if(response.status === 'success')
											{
												let journal_entry = {};
												if(response.data.journal_entry !== null)
												{
													journal_entry = response.data.journal_entry;
												}
												else
												{
													journal_entry.date = date;
													journal_entry.comment = null;
													journal_entry.grade = null;
													journal_entry.criteria = criteria;
													journal_entry.attachments = null;
												}
												journalEntryDialog(
													{
														type: 'edit',
														journal_entry: journal_entry
													});
											}
											else
											{
												alert('Ошибка при отображении записи журнала. Пожалуйста, обратитесь в службу технической поддержки SmartTeams.');
											}
										});
								});
							criteria_entries[criteria].grades_node.append(item_node);
						}
					}
					let chart_node = $('<div>').addClass('chart').css('width', journal_node.width()+'px');
					let canvas_node = $('<canvas>').addClass('canvas');
					chart_node.append(canvas_node);					
					journal_ajax_node.append(chart_node);
					let chart = {};
					chart.labels = [];
					chart.datasets = [];
					for(let day = 1; day <= last_day; day++)
					{
						chart.labels.push(day);
					}
					let ix = 0;
					for(let criteria in criteria_entries)
					{
						let dataset = {};
						dataset.label = criteria;
						dataset.borderColor = chart_colors[ix];
						dataset.backgroundColor = dataset.borderColor;
						// dataset.backgroundColor = '#d4eddab5';
						// dataset.borderColor = '#c3e6cb';
						dataset.data = [];
						for(let day = 1; day <= last_day; day++)
						{
							let date = journal_year.toString().padStart(2, '0') + '-' + (Number(journal_month) + 1).toString().padStart(2, '0') + '-' + day.toString().padStart(2, '0');
							if(criteria_entries[criteria].grades[date] !== undefined && criteria_entries[criteria].grades[date].grade !== null)
							{
								dataset.data.push(criteria_entries[criteria].grades[date].grade);
							}
							else
							{
								dataset.data.push(0);
							}
						}
						// dataset.fill = false;
						chart.datasets.push(dataset);
						ix++;
					}
					var myChart = new Chart(
						canvas_node, 
						{
					    	type: 'bar',
						    data: 
						    	{
							        labels: chart.labels,
							        datasets: chart.datasets
						    	},
						    options: 
							    {
							        layout: 
							        	{
							            	padding: 
							            	{
							                	left: 28,
							                	right: 28,
							                	top: 28,
							                	bottom: 28
							            	}
							    		},
									maintainAspectRatio: false,
								    scales: {
								        yAxes: [{
								            display: true,
								            ticks: {
								                min: 0,
								                max: 6,
								                stepSize: 1
								            }
								        }]
								    }
							    }
						});					
					/*
					am4core.useTheme(am4themes_animated);
					var chart = am4core.create(canvas_node.get(0), am4charts.XYChart)
					chart.colors.step = 2;
					chart.legend = new am4charts.Legend()
					chart.legend.position = 'top'
					chart.legend.paddingBottom = 20
					chart.legend.labels.template.maxWidth = 95

					var xAxis = chart.xAxes.push(new am4charts.CategoryAxis())
					xAxis.dataFields.category = 'category'
					xAxis.renderer.cellStartLocation = 0.1
					xAxis.renderer.cellEndLocation = 0.9
					xAxis.renderer.grid.template.location = 0;

					var yAxis = chart.yAxes.push(new am4charts.ValueAxis());
					yAxis.min = 0;

					function createSeries(value, name) {
					    var series = chart.series.push(new am4charts.ColumnSeries())
					    series.dataFields.valueY = value
					    series.dataFields.categoryX = 'category'
					    series.name = name
					    series.events.on("hidden", arrangeColumns);
					    series.events.on("shown", arrangeColumns);
					    var bullet = series.bullets.push(new am4charts.LabelBullet())
					    bullet.interactionsEnabled = false
					    bullet.dy = 30;
					    bullet.label.text = '{valueY}'
					    bullet.label.fill = am4core.color('#ffffff')
					    return series;
					}
					chart.data = [
					    {
					        category: 'Place #1',
					        first: 40,
					        second: 55,
					        third: 60
					    },
					    {
					        category: 'Place #2',
					        first: 30,
					        second: 78,
					        third: 69
					    },
					    {
					        category: 'Place #3',
					        first: 27,
					        second: 40,
					        third: 45
					    },
					    {
					        category: 'Place #4',
					        first: 50,
					        second: 33,
					        third: 22
					    }
					]
					createSeries('first', 'The First');
					createSeries('second', 'The Second');
					createSeries('third', 'The Third');

					function arrangeColumns() {

					    var series = chart.series.getIndex(0);

					    var w = 1 - xAxis.renderer.cellStartLocation - (1 - xAxis.renderer.cellEndLocation);
					    if (series.dataItems.length > 1) {
					        var x0 = xAxis.getX(series.dataItems.getIndex(0), "categoryX");
					        var x1 = xAxis.getX(series.dataItems.getIndex(1), "categoryX");
					        var delta = ((x1 - x0) / chart.series.length) * w;
					        if (am4core.isNumber(delta)) {
					            var middle = chart.series.length / 2;
					            var newIndex = 0;
					            chart.series.each(function(series) {
					                if (!series.isHidden && !series.isHiding) {
					                    series.dummyData = newIndex;
					                    newIndex++;
					                }
					                else {
					                    series.dummyData = chart.series.indexOf(series);
					                }
					            })
					            var visibleCount = newIndex;
					            var newMiddle = visibleCount / 2;
					            chart.series.each(function(series) {
					                var trueIndex = chart.series.indexOf(series);
					                var newIndex = series.dummyData;
					                var dx = (newIndex - trueIndex + middle - newMiddle) * delta
					                series.animate({ property: "dx", to: dx }, series.interpolationDuration, series.interpolationEasing);
					                series.bulletsContainer.animate({ property: "dx", to: dx }, series.interpolationDuration, series.interpolationEasing);
					            })
					        }
					    }
					}
					*/
				}
			});
	}
	let weekdays_abbr = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт','Пт', 'Сб'];
	let chart_colors = ['#cce5ff', '#d4edda', '#f8d7da', 'maroon', 'navy', 'lime', 'brown', 'purple', 'darkgreen'];
	let journal_ajax_node = $('div.view > div.journal_ajax');
	let controls_employee = $('div.view > div.controls > div.fieldset.employee > div.value > select[name="employee"]');	
	let controls_month = $('div.view > div.controls > div.fieldset.month > div.value > select[name="month"]');
	let controls_year = $('div.view > div.controls > div.fieldset.year  > div.value > select[name="year"]');
	controls_month.on('change', updateJournal);
	controls_year.on('change', updateJournal);	
	let dto_today = new Date();
	let today_month = dto_today.getMonth();
	let today_year = dto_today.getFullYear();
	controls_month.val(today_month);
	controls_year.val(today_year);
	$('div.view > div.controls > div.fieldset.month > div.value > button.next').on('click', function(e)
		{
			let month = Number($(this).siblings('select[name="month"]').val().trim());
			if(month + 1 <= 11)
			{
				month++;
				controls_month.val(month);
				if(month === 11)
				{
					$(this).attr('disabled', true);
				}
				if(month === 1)
				{
					$(this).siblings('button.prev').attr('disabled', false);
				}
			}
			updateJournal();
		});
	$('div.view > div.controls > div.fieldset.month > div.value > button.prev').on('click', function(e)
		{
			let month = Number($(this).siblings('select[name="month"]').val().trim());
			if(month - 1 >= 0)
			{
				month--;
				controls_month.val(month);
				if(month === 0)
				{
					$(this).attr('disabled', true);
				}
				if(month === 10)
				{
					$(this).siblings('button.next').attr('disabled', false);
				}
			}
			updateJournal();
		});
	if(controls_employee.length > 0)
	{	
		$.ajax(
		{
			url: "/journal/ajax.get.employees.php", 
			method: "POST",
			data: null,
			dataType: "json"
		})
		.done(
			function(response)
			{
				// console.log(response);
				if(response.status === 'success')
				{
					let employees = response.data.employees;
					for(let e of employees)
					{
						let option_node = $('<option>');
						option_node.val(e.user_id);
						option_node.text(e.person_firstname+' '+e.person_lastname.toUpperCase());
						controls_employee.append(option_node);
					}
					controls_employee.val(st.user.id);
					updateJournal();
				}
				else
				{
					alert('Возникла ошибка при выполнения запроса к базе данных сотрудников. Пожалуйста, обратитесь в службу поддержки SmartTeams.');
				}
			});
		controls_employee.on('change', updateJournal);
	}
	else
	{
		updateJournal();
	}
});