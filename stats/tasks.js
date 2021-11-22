$(function(e)
{
	let controls = {};
	controls.user = {};
	controls.user.select_node = $('div.view > div.controls > div.fieldset.user > div.value > select[name="user"]');
	if(controls.user.select_node.length > 0)
	{
		controls.user.select_node.on('change', 
			function(e)
				{
					displayStatistics();
				}); 
	}
	let ajax_node = $('div.view > div.ajax');
	function displayStatistics()
	{
		let user_id;
		if(controls.user.select_node.length > 0)
		{
			let value = controls.user.select_node.val().trim();
			user_id = (value !== '') ? Number(value) : null;
		}
		else
		{
			user_id = st.user.id;
		}
		console.log(user_id);
		$.ajax(
			{
				url: "/stats/tasks/ajax.get.php", 
				method: "POST",
				data: 
					{
						user_id: user_id
					},
				dataType: 'json'
			})
			.done(
				function(response)
				{
					console.log(response);
					if(response.data.stats !== null)
					{
						let table_node = $('<table>');
						let table_header_node = $('<thead>');
						let tr_node = $('<tr>');
						let th_node;
						th_node = $('<th>').addClass('user').text('Сотрудник');
						tr_node.append(th_node);
						th_node = $('<th>').addClass('chart').text('Диаграмма');
						tr_node.append(th_node);
						th_node = $('<th>').addClass('in_progress').text('Выполняется');
						tr_node.append(th_node);
						th_node = $('<th>').addClass('completed_on_time').text('Выполнено в срок');
						tr_node.append(th_node);
						th_node = $('<th>').addClass('completed_overdue').text('Выполнено с просрочкой');
						tr_node.append(th_node);
						th_node = $('<th>').addClass('overdue').text('Просрочено');
						tr_node.append(th_node);
						table_header_node.append(tr_node);
						let table_body_node = $('<tbody>');
						table_node
							.append(table_header_node)
							.append(table_body_node);
						ajax_node
							.empty()
							.append(table_node);
						for(let item of response.data.stats)
						{
							let tr_node;
							let td_node;
							let a_node;
							let canvas_node;
							tr_node = $('<tr>')
							td_node = $('<td>').addClass('user');
							a_node = $('<a>').attr('href', '/users/view?user_id='+item.user_id).text(item.person_firstname+' '+item.person_lastname.toUpperCase());
							let profile_node = $('<div>').addClass('profile');
							let photo_node = $('<div>').addClass('photo');
							let status_node = $('<div>').addClass('status').addClass(item.user_status);
							let name_node = $('<div>').addClass('name');
							let role_node = $('<div>').addClass('role');
							let department_node = $('<div>').addClass('department');					
							name_node.append(a_node);
							if(item.person_photo !== null)
							{
								photo_node.css('backgroundImage', 'url(\''+item.person_photo+'\')');							
							}
							else
							{
								let user = {};
								user.firstname = item.person_firstname;
								user.lastname = item.person_lastname;
								photo_node.append(st.generateUserPhoto(user));
							}
							photo_node.append(status_node);
							role_node.text(item.role_name);
							department_node.text(item.department_name);
							profile_node
								.append(photo_node)
								.append(name_node)
								.append(role_node)
								.append(department_node);
							td_node.append(profile_node);
							tr_node.append(td_node);
							td_node = $('<td>').addClass('chart');
							canvas_node = $('<div>').addClass('canvas');
							td_node.append(canvas_node);
							tr_node.append(td_node);
							td_node = $('<td>').addClass('in_progress');
							a_node = $('<a>').attr('href', '/tasks?user_id='+item.user_id+'&predefined_filter=in_progress').text(item.count.in_progress);
							td_node.append(a_node);
							tr_node.append(td_node);
							td_node = $('<td>').addClass('completed_on_time');
							a_node = $('<a>').attr('href', '/tasks?user_id='+item.user_id+'&predefined_filter=completed_on_time').text(item.count.completed_on_time);
							td_node.append(a_node);
							tr_node.append(td_node);
							td_node = $('<td>').addClass('completed_overdue');
							a_node = $('<a>').attr('href', '/tasks?user_id='+item.user_id+'&predefined_filter=completed_overdue').text(item.count.completed_overdue);
							td_node.append(a_node);
							tr_node.append(td_node);
							td_node = $('<td>').addClass('overdue');
							a_node = $('<a>').attr('href', '/tasks?user_id='+item.user_id+'&predefined_filter=overdue').text(item.count.overdue);
							td_node.append(a_node);
							tr_node.append(td_node);
							table_body_node.append(tr_node);
							/*
							let chart = {};		
							chart.labels = ['Выполняется', 'Выполнено в срок', 'Выполнено с просрочкой', 'Просрочено'];
							chart.datasets = [];
							let ds = {};
							ds.data = [item.count.in_progress, item.count.completed_on_time, item.count.completed_overdue, item.count.overdue];
							ds.backgroundColor = ['#67b6dc', '#6794dc', '#456494', '#8068dc'];
							ds.label = 'Статистика задач';
							chart.datasets.push(ds);
							chart.chart = new Chart(
								canvas_node, 
								{
							    	type: 'doughnut',
								    data: 
								    	{
									        datasets: chart.datasets,
									        labels: chart.labels
								    	},
								    options: 
									    {
											responsive: true,
											legend: 
												{
													position: 'left',
												},
											title: 
												{
													display: false
												},
											animation: 
												{
													animateScale: true,
													animateRotate: true
												},
									        layout: 
									        	{
									            	padding: 
									            	{
									                	left: 0,
									                	right: 0,
									                	top: 8,
									                	bottom: 8
									            	}
									    		},
											maintainAspectRatio: false
									    }
								});
							*/
							am4core.useTheme(am4themes_animated);
							var chart = am4core.create(canvas_node.get(0), am4charts.PieChart);
							chart.data = [
								{
									"category": "Выполняется",
									"count": item.count.in_progress
								},
								{
									"category": "Выполнено в срок",
									"count": item.count.completed_on_time
								},
								{
									"category": "Выполнено с просрочкой",
									"count": item.count.completed_overdue
								},
								{
									"category": "Просрочено",
									"count": item.count.overdue
								}];
							chart.innerRadius = am4core.percent(25);
							var pieSeries = chart.series.push(new am4charts.PieSeries());
							pieSeries.dataFields.value = "count";
							pieSeries.dataFields.category = "category";
							pieSeries.slices.template.stroke = am4core.color("#ddd");
							pieSeries.slices.template.strokeWidth = 1;
							pieSeries.slices.template.strokeOpacity = 1;
							// This creates initial animation
							pieSeries.hiddenState.properties.opacity = 1;
							pieSeries.hiddenState.properties.endAngle = -90;
							pieSeries.hiddenState.properties.startAngle = -90;
							$('div.canvas').find('title:contains("Chart created using amCharts library")').parent().remove();
							profile_node.height(tr_node.children('td.user').height());
						}
					}
					else
					{
						let empty_node = $('<div>').addClass('empty');
						empty_node.text('Записей статистики задач не найдено.');
						ajax_node
							.empty()
							.append(empty_node);
					}
				});
	}
	if(controls.user.select_node.length > 0)
	{
		$.ajax(
			{
				url: '/stats/tasks/ajax.get.users.php', 
				method: 'post',
				data: null,
				dataType: 'json'
			})
			.done(
				function(response)
				{
					// console.log(response);
					if(response.data !== null)
					{
						$('<option>').text('').val('').appendTo(controls.user.select_node);
						for(let item of response.data.users)
						{
							let option_node = $('<option>').text(item.person_firstname+' '+item.person_lastname.toUpperCase());
							option_node.val(item.user_id);
							controls.user.select_node.append(option_node);
						}
						controls.user.select_node.val(st.user.id);
						displayStatistics();
					}
				});
	}
	else
	{
		displayStatistics();
	}
});