$(function(e)
{
	var task_id = $('div.task_info > input[name="task_id"]').val().trim();	
	var task_status = $('div.task_info > input[name="task_status"]').val().trim();
	var new_comment_editor;
	var author_review_editor;
	var assignee_review_editor;
	function tabSwitch(e)
	{
		let tabname = e.data.tabname;
		$('div.task_info > div.tabs > div').removeClass('active');
		$('div.task_info > div.tab_content').hide();
		$('div.task_info > div.tab_content.'+tabname).show();
		$('div.task_info > div.tabs > div.'+tabname).addClass('active');
		if(tabname === 'comments')
		{
			if(!new_comment_editor && task_status !== 'closed')
			{
				new_comment_editor = new RichTextEditor($('div.task_info > div.tab_content.comments > div.new_comment > div.editor > textarea').get(0));
			}
		}
		if(tabname === 'author_review')
		{
			if(!author_review_editor)
			{
				author_review_editor = new RichTextEditor($('div.task_info > div.tab_content.author_review > div.field.author_comment > div.editor > textarea').get(0));
			}
		}
		if(tabname === 'assignee_review')
		{
			if(!assignee_review_editor)
			{
				assignee_review_editor = new RichTextEditor($('div.task_info > div.tab_content.assignee_review > div.field.assignee_comment > div.editor > textarea').get(0));
			}
		}
	}
	$('div.task_info > div.tabs > div.general').on('click', {tabname: 'general'}, tabSwitch);
	$('div.task_info > div.tabs > div.comments').on('click', {tabname: 'comments'}, tabSwitch);
	$('div.task_info > div.tabs > div.author_review').on('click', {tabname: 'author_review'}, tabSwitch);
	$('div.task_info > div.tabs > div.assignee_review').on('click', {tabname: 'assignee_review'}, tabSwitch);
	$('div.task_info > div.tabs > div.history').on('click', {tabname: 'history'}, tabSwitch);

	if($('div.task_info > div.tabs > div.author_review').length > 0)
	{
		let field_node = $('div.task_info > div.tab_content.author_review > div.field.author_rate');		
		let editable = field_node.find('input[name="editable"]');
		let value_node = field_node.find('div.value');
		let input_node = field_node.find('input[name="author_review_rate"]');
		let author_review_rate = input_node.val().trim();
		let stars_rating_node = $('<div>').addClass('stars_rating');
		if(editable)
		{
			stars_rating_node.addClass('editable');
		}
		for(let i = 1; i <= 5; i++)
		{
			let starbox_node = $('<div>').addClass('starbox');
			if(i <= author_review_rate)
			{
				starbox_node.addClass('set');
			}
			if(editable)
			{
				starbox_node.on('click',
					function(e)
					{
						input_node.val(i);
						$(this).css('background-image', 'url(/images/ui/icon.star.set.png)');
						$(this).prevAll().css('background-image', 'url(/images/ui/icon.star.set.png)');
						$(this).nextAll().css('background-image', 'url(/images/ui/icon.star.empty.png)');								
					});
			}
			stars_rating_node.append(starbox_node);
		}
		value_node.append(stars_rating_node);
	}
	if($('div.task_info > div.tabs > div.assignee_review').length > 0)
	{
		let smart_rating_node = $('div.task_info > div.tab_content.assignee_review > div.assignee_rate > div.smart_rating');
		let smart = ['specific', 'measurable', 'attainable', 'relevant', 'timebound'];
		let editable = Boolean(smart_rating_node.find('input[name="editable"]').val().trim());
		smart_rating_node.find('table > tbody > tr').each(
			function(ix)
			{
				let rate_value = smart_rating_node.find('input[name="'+smart[ix]+'"]').val().trim();
				let value_node = $(this).find('td.value');
				let stars_rating_node = $('<div>').addClass('stars_rating');
				if(editable)
				{
					stars_rating_node.addClass('editable');
				}
				for(let i = 1; i <= 5; i++)
				{
					let starbox_node = $('<div>').addClass('starbox');
					if(i <= rate_value)
					{
						starbox_node.addClass('set');
					}
					if(editable)
					{
						starbox_node.on('click',
							function(e)
							{
								smart_rating_node.find('input[name="'+smart[ix]+'"]').val(i);
								$(this).css('background-image', 'url(/images/ui/icon.star.set.png)');
								$(this).prevAll().css('background-image', 'url(/images/ui/icon.star.set.png)');
								$(this).nextAll().css('background-image', 'url(/images/ui/icon.star.empty.png)');								
							});
					}
					stars_rating_node.append(starbox_node);
				}
				value_node.append(stars_rating_node);
			});
	}
	$('div.task_info > div.tab_content.comments > div.new_comment > div.controls > button.add').on('click',
		function(e)
		{
			let comment_text = new_comment_editor.getHTMLCode().trim();
			if(comment_text !== '')
			{
				$.ajax(
					{
						url: '/tasks/view/ajax.add.comment.php', 
						method: 'POST',
						data: {task_id: task_id, comment_text: comment_text},
						dataType: 'json'
					})
				  	.done(function(response)
				  		{
				    		console.log(response);
				    		if(response.status === "success")
				    		{
								$.ajax(
									{
										url: "/tasks/view/ajax.get.comments.php", 
										method: "POST",
										data: {task_id: task_id},
										dataType: "json"
									})
								.done(function(response)
								{
				    				// update comments div
				    				// console.log(response);
				    				let comments = response.data;
				    				let comments_div_node = $('div.task_info > div.tab_content.comments > div.comments').empty();
				    				comments.forEach(function(val, ix, src)
				    					{
						    				let comment = val;
						    				let comment_node = $('body > div.dummy > div.comment').clone();
						    				comment_node.find('input[name="task_comment_id"]').val(comment.task_comment_id);
						    				comment_node.find('div.comment_caption > div.datetime > div.date_text').text(comment.task_comment_date_created.date);
						    				comment_node.find('div.comment_caption > div.datetime > div.time_text').text(comment.task_comment_date_created.time);
						    				comment_node.find('div.comment_caption > div.author > div.profile > div.avatar').css('backgroundImage', 'url("'+comment.person_photo+'")');
						    				comment_node.find('div.comment_caption > div.author > div.profile > div.avatar > div.status').addClass(comment.user_status);
						    				comment_node.find('div.comment_caption > div.author > div.profile > div.info > div.name > div.firstname').text(comment.person_firstname);
						    				comment_node.find('div.comment_caption > div.author > div.profile > div.info > div.name > div.lastname').text(comment.person_lastname);
						    				comment_node.find('div.comment_caption > div.author > div.profile > div.info > div.role_name').text(comment.role_name);
						    				comment_node.find('div.comment_caption > div.author > div.profile > div.info > div.department_name').text(comment.department_name);
						    				comment_node.find('div.comment_body').html(comment.task_comment);
						    				comments_div_node.append(comment_node);
				    					});
								});
				    		}
				    		else
				    		{
				    			alert('Ошибка при добавлении нового комментария задачи. Попробуйте повторить операцию позже или обратитесь в службу поддержки SmartTeams.');
				    		}
				  		});
			}
			else
			{
				alert('Текст комментария не может быть пустым.');
			}
		});
	$('div.view > div.controls > button.start').on('click',
		function(e)
		{
			$.ajax(
				{
					url: "/tasks/ajax.start.php", 
					method: "POST",
					data: {task_id: task_id},
					dataType: "json"
				})
			.done(function(response)
			{
				// update comments div
				console.log(response);
				if(response.status === "success")
				{
					window.location.href = "/tasks/view?id="+task_id;
				}
				else
				{
					alert('Ошибка при изменении статуса задачи. Попробуйте повторить операцию позже или обратитесь в службу поддержки SmartTeams.');
				}
			});
		});
	$('div.view > div.controls > button.complete').on('click',
		function(e)
		{
			$.ajax(
				{
					url: "/tasks/ajax.complete.php",
					method: "POST",
					data: {task_id: task_id},
					dataType: "json"
				})
			.done(function(response)
			{
				console.log(response);
				if(response.status === "success")
				{
					window.location.href = "/tasks/view?id="+task_id;
				}
				else
				{
					alert('Ошибка при изменении статуса задачи. Попробуйте повторить операцию позже или обратитесь в службу поддержки SmartTeams.');
				}
			});
		});
	$('div.view > div.controls > button.decline').on('click',
		function(e)
		{
			$.ajax(
				{
					url: "/tasks/ajax.decline.php",
					method: "POST",
					data: {task_id: task_id},
					dataType: "json"
				})
			.done(function(response)
			{
				console.log(response);
				if(response.status === "success")
				{
					window.location.href = "/tasks/view?id="+task_id;
				}
				else
				{
					alert('Ошибка при изменении статуса задачи. Попробуйте повторить операцию позже или обратитесь в службу поддержки SmartTeams.');
				}
			});
		});
	$('div.view > div.controls > button.review_by_author').on('click',
		function(e)
		{
			let review_rate = $('div.task_info > div.tab_content.author_review > div.field.author_rate > input[name="author_review_rate"]').val();
			let review_comment = author_review_editor.getHTMLCode().trim();			
			$.ajax(
				{
					url: "/tasks/ajax.review_by_author.php",
					method: "POST",
					data: 
						{
							task_id: task_id,
							review_rate: review_rate,
							review_comment: review_comment
						},
					dataType: "json"
				})
			.done(
				function(response)
				{
					if(response.status === "success")
					{
						window.location.href = "/tasks/view?id="+task_id;
					}
					else
					{
						console.log(response);
						alert('Ошибка при изменении статуса задачи. Попробуйте повторить операцию позже или обратитесь в службу поддержки SmartTeams.');
					}
				});
		});
	$('div.view > div.controls > button.review_by_assignee').on('click',
		function(e)
		{
			let review_comment = assignee_review_editor.getHTMLCode().trim();
			let smart_rate_node = $('div.task_info > div.tab_content.assignee_review > div.assignee_rate > div.smart_rating');
			let review_rate = {};
			review_rate.specific = smart_rate_node.find('input[name="specific"]').val();
			review_rate.measurable = smart_rate_node.find('input[name="measurable"]').val();
			review_rate.attainable = smart_rate_node.find('input[name="attainable"]').val();
			review_rate.relevant = smart_rate_node.find('input[name="relevant"]').val();
			review_rate.timebound = smart_rate_node.find('input[name="timebound"]').val();
			$.ajax(
				{
					url: "/tasks/ajax.review_by_assignee.php",
					method: "POST",
					data: 
						{
							task_id: task_id,
							review_comment: review_comment,
							review_rate: review_rate,
						},
					dataType: "json"
				})
			.done(
				function(response)
				{
					// console.log(response);
					if(response.status === "success")
					{
						window.location.href = "/tasks/view?id="+task_id;
					}
					else
					{
						alert('Ошибка при изменении статуса задачи. Попробуйте повторить операцию позже или обратитесь в службу поддержки SmartTeams.');
					}
				});
		});
	$('div.view > div.controls > button.close').on('click',
		function(e)
		{
			$.ajax(
				{
					url: "/tasks/ajax.close.php",
					method: "POST",
					data: {task_id: task_id},
					dataType: "json"
				})
			.done(function(response)
			{
				// console.log(response);
				if(response.status === "success")
				{
					window.location.href = "/tasks/view?id="+task_id;
				}
				else
				{
					alert('Ошибка при изменении статуса задачи. Попробуйте повторить операцию позже или обратитесь в службу поддержки SmartTeams.');
				}
			});
		});
	$('div.view > div.controls > button.duplicate').on('click',
		function(e)
		{
			window.location.href = '/tasks/duplicate?id='+task_id;
		});
});