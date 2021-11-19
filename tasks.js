$(function()
{
	var controls = {};
	controls.user = {};
	controls.user.select_node = $('div.controls > div.fieldset.user > div.value > select[name="user"]');
	controls.user.input_node = controls.user.select_node.siblings('input[name="user"]');
	controls.author_assignee = {};
	controls.author_assignee.select_node = $('div.controls > div.fieldset.author_assignee > div.value > select[name="author_assignee"]');
	controls.author_assignee.input_node = controls.author_assignee.select_node.siblings('input[name="author_assignee"]');
	controls.author_assignee.value = controls.author_assignee.input_node.val().trim();
	controls.author_assignee.select_node.val(controls.author_assignee.value);
	controls.status = {};
	controls.status.select_node = $('div.controls > div.fieldset.status > div.value > select[name="status"]');
	controls.status.input_node = controls.status.select_node.siblings('input[name="status"]');
	controls.status.value = controls.status.input_node.val().trim();
	controls.status.select_node.val(controls.status.value);
	controls.predefined_filter = {};
	controls.predefined_filter.select_node = $('div.controls > div.fieldset.predefined_filter > div.value > select[name="predefined_filter"]');
	controls.predefined_filter.input_node = controls.predefined_filter.select_node.siblings('input[name="predefined_filter"]');
	controls.predefined_filter.value = controls.predefined_filter.input_node.val().trim();
	controls.predefined_filter.select_node.val(controls.predefined_filter.value);
	if(controls.predefined_filter.value !== '')
	{
		controls.status.select_node.parents('div.fieldset').hide();
	}
	var ajax_node = $('div.view > div.ajax');
	function getTasks(e)
	{
		let user_id = (controls.user.select_node.length > 0) ? Number(controls.user.select_node.val().trim()) : st.user.id;
		let filter = {};
		filter.author_assignee = controls.author_assignee.select_node.val().trim();
		filter.status = controls.status.select_node.val().trim();
		filter.predefined_filter = controls.predefined_filter.select_node.val().trim();
		$.ajax(
			{
				url: "/tasks/ajax.get.php",
				method: "POST",
				data: {user_id: user_id, filter: filter},
				dataType: "json"
			})
	  	.done(function(response)
	  		{
	    		// console.log(response);
	    		if(response.status === "success")
	    		{
	    			if(response.data.tasks.length > 0)
	    			{
						let table_node = $('<table>').addClass('tasks');
						let thead_node = $('<thead>');
						let tbody_node = $('<tbody>');
						thead_node.append($('<th>').text('Код'));
						thead_node.append($('<th>').text('Название'));
						thead_node.append($('<th>').text('Статус'));
						thead_node.append($('<th>').text('Автор'));
						thead_node.append($('<th>').text('Сроки'));
						thead_node.append($('<th>').text('Назначена'));
						thead_node.append($('<th>').text('Выполнение'));
						thead_node.append($('<th>').text(''));					
						table_node.append(thead_node);
		    			table_node.append(tbody_node);
		    			response.data.tasks.forEach(
		    				function(val, ix, src)
		    				{
				    			let tr_node = $('<tr>');
				    			let task_id_node = $('<td>').addClass('task_id').text(val.task_id);
				    			let task_name_node = $('<td>').addClass('task_name').html(val.task_name);
				    			let task_status_node = $('<td>').addClass('task_status').addClass(val.task_status).html(val.task_status_display);
				    			let task_author_node = $('<td>').addClass('task_author');
				    			let profile_node = $('body > div.dummy > div.profile').clone();
				    			profile_node.find('div.avatar').css(
				    				{
				    					backgroundImage: 'url('+val.author_person_photo+')'
				    				});
				    			profile_node.find('div.avatar > div.status').addClass(val.author_user_status);
				    			profile_node.find('div.info > div.name > span.firstname').html(val.author_person_firstname);
				    			profile_node.find('div.info > div.name > span.lastname').html(val.author_person_lastname);
								profile_node.find('div.info > div.role_name').html(val.author_role_name);
				    			task_author_node.append(profile_node);
				    			let task_terms_node = $('<td>').addClass('task_terms');
				    			let terms_node = $('body > div.dummy > div.terms').clone();
				    			terms_node.find('div.task_date_begin > div.text').html(val.task_date_begin.datetime_html);
				    			terms_node.find('div.task_date_end > div.text').html(val.task_date_end.datetime_html);
				    			terms_node.find('div.task_date_completed > div.text').html(val.task_date_completed.datetime_html);
				    			task_terms_node.append(terms_node.children());
				    			let task_assignees_node = $('<td>').addClass('task_assignees');
				    			val.task_assignees.forEach(
				    				function(assignee)
				    				{
						    			let profile_node = $('body > div.dummy > div.profile').clone();
						    			profile_node.find('div.avatar').css(
						    				{
						    					backgroundImage: 'url('+assignee.person_photo+')'
						    				});
						    			profile_node.find('div.avatar > div.status').addClass(assignee.user_status);
						    			profile_node.find('div.info > div.name > span.firstname').text(assignee.person_firstname);
						    			profile_node.find('div.info > div.name > span.lastname').text(assignee.person_lastname);					    			
						    			profile_node.find('div.info > div.role_name').text(assignee.role_name);
						    			profile_node.find('div.info > div.department_name').text(assignee.department_name);
				    					task_assignees_node.append(profile_node);
				    				});
				    			let task_progress_node = $('<td>').addClass('task_progress').text(val.task_progress);			    			
				    			let task_controls_node = $('<td>').addClass('task_controls');
				    			let button_details = $('<button>').addClass('details').text('Подробнее');			    			
				    			button_details.on('click', function(e)
				    				{
				    					window.location.href = "/tasks/view?id="+val.task_id;
				    				});
				    			task_controls_node.append(button_details);
				    			tr_node
				    				.append(task_id_node)
				    				.append(task_name_node)
				    				.append(task_status_node)
				    				.append(task_author_node)
				    				.append(task_terms_node)
				    				.append(task_assignees_node)
				    				.append(task_progress_node)
				    				.append(task_controls_node);
				    			tbody_node.append(tr_node);			    			
		    				});
		    			ajax_node
		    				.empty()
		    				.append(table_node);
	    			}
	    			else
	    			{
	    				let empty_node = $('<div>').addClass('empty').html('Нет задач с заданными параметрами.');
	    				ajax_node
	    					.empty()
	    					.append(empty_node);
	    			}
	    		}
	  		});
	}
	controls.user.select_node.on('change', getTasks);
	controls.author_assignee.select_node.on('change', getTasks);
	controls.status.select_node.on('change', getTasks);
	controls.predefined_filter.select_node.on('change',
		function(e)
		{
			let value = $(this).val().trim();
			if(value !== '')
			{
				controls.status.select_node.parents('div.fieldset').hide();
				getTasks();
			}
			else
			{
				controls.status.select_node.parents('div.fieldset').show();
				getTasks();
			}
		});
	$('div.controls button.add').on('click',
		function(e)
		{
			// console.log('show add task');
			let window_task_add = st.ShowModal('div.window_task_add');
			$.ajax(
				{
					url: '/tasks/ajax.get.users.php',
					method: 'post',
					data: null,
					dataType: 'json'
				})
				.done(
					function(response)
					{
						// console.log(response);
						let select_node = window_task_add.find('select.assignment_candidates');
						for(let user of response.data.users)
						{
							let option_node = $('<option>').text(user.person_firstname+' '+user.person_lastname.toUpperCase());
							option_node.val(user.user_id);
							select_node.append(option_node);
						}
						select_node.val('');
					});
			window_task_add
				.find('select.assignment_candidates').on('change',
					function(e)
					{
						// console.log('add candidate');
						let user_id = $(this).val().trim();
						let fieldset_node = $(this).parent();
						if(user_id)
						{
		    				let exists = false;
			    			let selected_candidates_node = fieldset_node.find('div.selected_candidates');
			    			if(selected_candidates_node.length > 0)
			    			{
			    				selected_candidates_node.find('div.profile').each(
			    					function(ix)
			    					{
			    						if($(this).data('user_id') === user_id)
			    						{
			    							exists = true;
			    						}
			    					});
			    			}
			    			if(!exists)
			    			{		    				
								$.ajax(
								{
									url: "/tasks/ajax.get_candidate_profile.php", 
									method: "POST",
									data: {user_id: user_id},
									dataType: "json"
								})
							  	.done(function(response)
							  		{
							    		// console.log(response);
							    		if(response.status === "success")
							    		{
							    			if(selected_candidates_node.length === 0)
							    			{
							    				selected_candidates_node = window_task_add.find('div.dummy').find('div.selected_candidates').clone().empty();
								    			selected_candidates_node.insertBefore(fieldset_node.find('select.assignment_candidates'));
							    			}
							    			let profile_node = window_task_add.find('div.dummy').find('div.selected_candidates').find('div.profile').clone();
							    			profile_node.data('user_id', response.data.user_id);
							    			profile_node.find('div.avatar').css(
							    				{
							    					backgroundImage: 'url('+response.data.person_photo+')'
							    				});
							    			profile_node.find('span.firstname').text(response.data.person_firstname);
							    			profile_node.find('span.lastname').text(response.data.person_lastname);
							    			profile_node.find('div.role_name').text(response.data.role_name);
							    			profile_node.find('div.department_name').text(response.data.department_name);
							    			profile_node.find('div.button_close').on('click', function(e)
							    				{
							    					profile_node.remove();
							    				});
							    			selected_candidates_node.append(profile_node);
							    			fieldset_node.find('select.assignment_candidates').val('');
							    		}
							  		});
			    			}
			    			else
			    			{
			    				fieldset_node.find('select.assignment_candidates').val('');
			    			}
						}
					});
			let description_node = window_task_add.find('div.fieldset').find('textarea[name="description"]');
			let description_editor = new RichTextEditor(description_node.get(0));
			let jdo = new Date();
			let today_date = jdo.getFullYear() + '-' + padDate(jdo.getMonth() + 1) + '-' + padDate(jdo.getDate());
			let today_time = padDate(jdo.getHours()) + ':' + padDate(jdo.getMinutes());
			window_task_add.find('input[name="date_begin"]').val(today_date);
			window_task_add.find('input[name="time_begin"]').val(today_time);
			let due_date = jdo.getFullYear() + '-' + padDate(jdo.getMonth() + 1) + '-' + padDate(jdo.getDate() + 1);
			let due_time = today_time;
			let datepicker_date_begin = st.DatePicker(window_task_add.find('div.dateset.date_begin').find('input[name="date_begin"]'));
			let datepicker_date_end = st.DatePicker(window_task_add.find('div.dateset.date_end').find('input[name="date_end"]'));
			window_task_add.find('input[name="date_end"]').val(due_date);
			window_task_add.find('input[name="time_end"]').val(due_time);
			window_task_add.find('div.dateset.date_begin').find('button.pick.date').on('click',
				function(e)
				{
					datepicker_date_begin.open();
				});
			window_task_add.find('div.dateset.date_end').find('button.pick.date').on('click',
				function(e)
				{
					datepicker_date_end.open();
				});
			window_task_add.find('div.dateset.date_begin').find('button.pick.time').on('click', function(e)
				{
					timepicker = st.TimePicker(
						{
							targetNode: window_task_add.find('div.dateset.date_begin').find('input[name="time_begin"]')
						});
					timepicker.show();
				});
			window_task_add.find('div.dateset.date_end').find('button.pick.time').on('click', function(e)
				{
					timepicker = st.TimePicker(
						{
							targetNode: window_task_add.find('div.dateset.date_end').find('input[name="time_end"]')
						});
					timepicker.show();
				});
			window_task_add.find('div.controls').find('button.save').on('click', function(e)
				{
					// console.log('save');
					// console.log(window_task_add.find('div.dateset.date_begin').find('input[name="time_begin"]').val());
					let new_task = {};
					new_task.name = window_task_add.find('input[name="name"]').val();
					new_task.description = description_editor.getHTMLCode();
					new_task.date_begin = 
						window_task_add.find('div.dateset.date_begin').find('input[name="date_begin"]').val().trim() + 
						' ' +
						window_task_add.find('div.dateset.date_begin').find('input[name="time_begin"]').val().trim();
					new_task.date_end = 
						window_task_add.find('div.dateset.date_end').find('input[name="date_end"]').val().trim() + 
						' ' +
						window_task_add.find('div.dateset.date_end').find('input[name="time_end"]').val().trim();
					new_task.assignees = [];
					let selected_candidates_node = window_task_add.find('div.fieldset').find('div.selected_candidates');
					if(selected_candidates_node.length > 0)
					{
						let candidates_nodes = selected_candidates_node.find('div.profile');
						if(candidates_nodes.length > 0)
						{
							candidates_nodes.each(function(ix)
								{
									new_task.assignees.push($(this).data('user_id'));
								});
						}
					}
					$.ajax(
					{
						url: "/tasks/ajax.add.php", 
						method: "POST",
						data: new_task,
						dataType: "json"
					})
				  	.done(function(response)
				  		{
				    		console.log(response);
				    		if(response.status === "success")
				    		{
				    			location.reload();
				    		}
				    		else
				    		{
				    			alert('Ошибка при добавлении новой задачи. Попробуйте повторить операцию позже или обратитесь в службу поддержки SmartTeams.');
				    		}
				  		});
				  	description_editor.closeCurrentPopup();
					window_task_add.parents('div.st_modal_overlay').remove();
				});
		});
	if(controls.user.select_node.length > 0)
	{
		$.ajax(
			{
				url: '/tasks/ajax.get.users.php',
				method: 'post',
				data: null,
				dataType: 'json'
			})
			.done(
				function(response)
				{
					// console.log(response);
					for(let user of response.data.users)
					{
						let option_node = $('<option>').text(user.person_firstname+' '+user.person_lastname.toUpperCase());
						option_node.val(user.user_id);
						controls.user.select_node.append(option_node);
					}
					controls.user.select_node.val(controls.user.input_node.val().trim());
					getTasks();
				});
	}
	else
	{
		getTasks();
	}
});
function padDate(d)
{
	d = d.toString();
	if(d.length < 2)
	{
		d = '0' + d;
	}
	return d;
}