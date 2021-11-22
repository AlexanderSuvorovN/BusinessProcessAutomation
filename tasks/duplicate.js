$(function(e)
{
	var editor_description = new RichTextEditor($('div.task_data > div.field.description > div.value > textarea').get(0));
	$('div.controls > button.cancel').on('click', 
		function(e)
		{
			window.location.href = '/tasks';
		});
	var date_begin = {};
	date_begin.date_icon = $('div.task_data > div.field.date_begin > div.value > div.date > div.icon');
	date_begin.time_icon = $('div.task_data > div.field.date_begin > div.value > div.time > div.icon');
	date_begin.date_input = $('div.task_data > div.field.date_begin').find('input[name="date_begin"]');
	date_begin.time_input = $('div.task_data > div.field.date_begin').find('input[name="time_begin"]');
	date_begin.datepicker = st.DatePicker(date_begin.date_input);
	date_begin.date_icon.on('click', function(e){ date_begin.datepicker.open() });
	date_begin.time_icon.on('click', function(e)
			{
				timepicker = st.TimePicker({targetNode: date_begin.time_input});
				timepicker.show();
			});
	var date_end = {};
	date_end.date_icon = $('div.task_data > div.field.date_end > div.value > div.date > div.icon');
	date_end.time_icon = $('div.task_data > div.field.date_end > div.value > div.time > div.icon');	
	date_end.date_input = $('div.task_data > div.field.date_end').find('input[name="date_end"]');
	date_end.time_input = $('div.task_data > div.field.date_end').find('input[name="time_end"]');
	date_end.datepicker = st.DatePicker(date_end.date_input);
	date_end.date_icon.on('click', function(e){date_end.datepicker.open()});
	date_end.time_icon.on('click', function(e)
			{
				timepicker = st.TimePicker({targetNode: date_end.time_input});
				timepicker.show();
			});
	$('div.task_data > div.field.assignees > div.value > div.profile > div.button_close').on('click',
		function(e)
		{
			$(this).parent('div.profile').remove();
		});
	$('div.task_data > div.field.select_assignee > select').on('change',
		function(e)
		{
			let select_node = $(this);
			let user_id = select_node.val().trim();
			let assignees_node = $(this).parent().prev().children('div.value');
			if(user_id)
			{
				let exists = false;
    			assignees_node.find('div.profile').each(
					function(ix)
					{
						if($(this).find('input[name="user_id"]').val().trim() === user_id)
						{
							exists = true;
						}
					});
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
				    		console.log(response);
				    		if(response.status === "success")
				    		{
				    			let profile_node = $('div.dummy > div.profile').clone();
				    			profile_node.find('input[name="user_id"]').val(response.data.user_id);
				    			profile_node.find('div.avatar').css(
				    				{
				    					backgroundImage: 'url('+response.data.person_photo+')'
				    				});
				    			profile_node.find('div.avatar > div.status').addClass(response.data.status);
				    			profile_node.find('span.firstname').text(response.data.person_firstname);
				    			profile_node.find('span.lastname').text(response.data.person_lastname);
				    			profile_node.find('div.role_name').text(response.data.role_name);
				    			profile_node.find('div.department_name').text(response.data.department_name);
				    			profile_node.find('div.button_close').on('click', function(e)
				    				{
				    					profile_node.remove();
				    				});
				    			assignees_node.append(profile_node);
				    			select_node.val('');
				    		}
				  		});
				}
				else
				{
					select_node.val('');
				}
    		}
			else
			{
				select_node.val('');
			}
		});
$('div.controls > button.submit').on('click', 
		function(e)
		{
			let new_task = {};
			new_task.name = $('div.task_data > div.field.name > div.value > input[name="name"]').val();
			new_task.description = editor_description.getHTMLCode();
			new_task.date_begin = date_begin.date_input.val().trim() + ' ' + date_begin.time_input.val().trim();
			new_task.date_end   = date_end.date_input.val().trim() + ' ' + date_end.time_input.val().trim();
			new_task.assignees = [];
			let assignees_node = $('div.task_data > div.field.assignees > div.value');
			let profile_nodes = assignees_node.find('div.profile');
			if(profile_nodes.length > 0)
			{
				profile_nodes.each(function(ix)
				{
					let user_id = $(this).find('input[name="user_id"]').val().trim();
					new_task.assignees.push(user_id);
				});
			}
			console.log(new_task);
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
						window.location.href = '/tasks';
		    		}
		    		else
		    		{
		    			alert('Ошибка при добавлении новой задачи. Попробуйте повторить операцию позже или обратитесь в службу поддержки SmartTeams.');
		    		}
		  		});
		});
});