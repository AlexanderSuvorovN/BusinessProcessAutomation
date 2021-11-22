$(function(e)
{
	let controls = {};
	controls.employee_node = $('div.view > div.controls > div.fieldset.employee > div.value > select[name="employee"]');
	controls.date_node = $('div.view > div.controls > div.fieldset.date > div.value > input');
	controls.employee_node.on('change', 
		function(e)
		{
			controls.date_node.trigger('input');
		});
	controls.date_node.on('input change', 
		function(e)
		{
			let node = $(this);
			let value = $(this).val().trim();
			let regex = /([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/;			
			let match = value.match(regex);
			if(match !== null)
			{
				let year = match[1];
				let month = match[2];
				let day = match[3];			
				if(month >= 1 || month <= 12)
				{
					let dto = new Date(year, month, 1);
					let lastday = (new Date(dto - 1)).getDate();
					if(day >= 1 && day <= lastday)
					{
						displayStatistics();
					}
				}
			}
		});
	$('div.view > div.controls > div.fieldset.date > div.value > button').on('click', (e) => st.DatePicker2({target: controls.date_node}));	
	// $('div.view > div.controls > button.apply').on('click', (e) => displayStatistics());
	let ajax_node = $('div.view > div.ajax');
	if(controls.employee_node.length > 0)
	{
		$.ajax(
			{
				url: "/stats/terminals/ajax.get.employees.php", 
				method: "POST",
				data: null,
				dataType: 'json'
			})
			.done(
				function(response)
				{
					// console.log(response);
					let employees = response.data.employees;
					for(let e of employees)
					{
						let option_node = $('<option>').text(e.person_firstname+' '+e.person_lastname.toUpperCase()).val(e.user_id);
						controls.employee_node.append(option_node);
					}
					controls.employee_node.val(st.user.id);
					let dto = new Date();
					let date = dto.getFullYear().toString().padStart(2, '0') + '-' + (dto.getMonth() + 1).toString().padStart(2, '0') + '-' + dto.getDate().toString().padStart(2, '0');
					controls.date_node.val(date);
					displayStatistics();
				});
	}
	function displayStatistics()
	{
		let user_id = (controls.employee_node.length > 0) ? Number(controls.employee_node.val().trim()) : st.user.id;
		let date = controls.date_node.val().trim();
		if(user_id !== null && date !== null)
		{
			$.ajax(
				{
					url: "/stats/terminals/ajax.get.php", 
					method: "POST",
					data:
					{
						user_id: user_id,
						date: date
					},
					dataType: 'json'
				})
				.done(
					function(response)
					{
						console.log(response);
						if(response.data !== null)
						{
							let summary = response.data.summary;
							let summary_node = $('<div>').addClass('summary');
							let properties = ['start', 'end', 'work', 'active', 'idle'];
							let labels = ['Начало работы', 'Окончание работы', 'Рабочий день', 'Активность', 'Простой'];
							for(let ix in properties)
							{
								let item_node = $('<div>').addClass(properties[ix]);
								let label_node = $('<div>').addClass('label');
								let value_node = $('<div>').addClass('value');
								label_node.html(labels[ix]);
								value_node.html((summary[properties[ix]] !== null) ? summary[properties[ix]] : '-');
								item_node
									.append(label_node)
									.append(value_node);
								summary_node.append(item_node);
							}
							let start_node = $('<div>').addClass('start');
							let end_node = $('<div>').addClass('end');
							let table = {};
							table.node = $('<table>');
							table.header_node = $('<thead>');
							let tr_header_node = $('<tr>');
							let header_date_node = $('<th>').text('Дата');
							let header_time_node = $('<th>').text('Время');
							let header_type_node = $('<th>').text('Тип записи');
							let header_application_node = $('<th>').text('Приложение');
							let header_details_node = $('<th>').text('Дополнительно');
							tr_header_node
								.append(header_date_node)
								.append(header_time_node)
								.append(header_type_node)
								.append(header_application_node)
								.append(header_details_node);
							table.header_node.append(tr_header_node);
							table.body_node = $('<tbody>');
							let records = response.data.records;
							for(let e of records)
							{
								let tr_node = $('<tr>');
								let date_node = $('<td>').addClass('date').text(e.date.date);
								let time_node = $('<td>').addClass('time').text(e.date.time);
								let type_node = $('<td>').addClass('type').text(e.type);
								let application_name_node = $('<td>').addClass('application_name').text(e.application_name);
								let details_node = $('<td>').addClass('details').text(e.details);
								tr_node
									.append(date_node)
									.append(time_node)
									.append(type_node)
									.append(application_name_node)
									.append(details_node);
								table.body_node.append(tr_node);
							}
							table.node
								.append(table.header_node)
								.append(table.body_node);							
							let chart_node = $('<div>').addClass('chart');
							let canvas_node = $('<canvas>');
							chart_node.append(canvas_node);
							// ajax_node.append(chart_node);
							let chart = {};							
							chart.labels = response.data.chart.labels;
							chart.datasets = 
								[{
									label: 'Активность пользователя',
									backgroundColor: '#d4eddab5',
									borderColor: '#c3e6cb',
									data: response.data.chart.data
								}];
							for(let ix in chart.datasets[0].data)
							{
								// https://stackoverflow.com/questions/15762768/javascript-math-round-to-two-decimal-places
								chart.datasets[0].data[ix] = (chart.datasets[0].data[ix] / 60).toFixed(2);
							}
							ajax_node
								.empty()
								.append(summary_node)
								.append(chart_node);
							// table.node.css('width', summary_node.width().toString()+'px');
							ajax_node.append(table.node);
							var myChart = new Chart(
								canvas_node, 
								{
							    	type: 'line',
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
										                max: 60,
										                stepSize: 15
										            }
										        }]
										    }
									    }
								});
						}
						else
						{
							let empty_node = $('<div>').addClass('empty');
							empty_node.text('Нет записей с данными параметрами.');
							ajax_node
								.empty()
								.append(empty_node);
						}
					});
		}
	}

});