$(function()
{
	let notifications_pane = $('div.view > div.dashboard > div.col-1 > div.notifications_ajax');
    let tasks_pane = $('div.view > div.dashboard > div.col-1 > div.tasks_ajax');
	$.ajax(
	{
		url: "/notifications/ajax.get.php", 
		method: "POST",
		data: null,
		dataType: "json"
	})
  	.done(function(response)
  		{
    		// console.log(response);
    		if(response.status === "success")
    		{
    			let notifications = response.data;
                if(notifications.length > 0)
                {                
        			let table_node = $('<table>').addClass('notifications');
        			let thead_node = $('<thead>');
        			let tr_node = $('<tr>');
        			let th_date_node = $('<th>').html('Дата');
        			let th_message_node = $('<th>').html('Сообщение');
                    let th_control_node = $('<th>').html('Сообщение');
        			tr_node.append(th_date_node);
        			tr_node.append(th_message_node);
                    tr_node.append(th_control_node);
        			thead_node.append(tr_node);
        			let tbody_node = $('<tbody>');
        			notifications.forEach(
        				function(val, ix, src)
        				{
        					let tr_node = $('<tr>');
        					let date_created_node = $('<td>').html(val.date_created);
        					let message_node = $('<td>').html(val.message);                        
                            let control_node = $('<td>');
                            let button_node = $('<button>').html('Скрыть');
                            button_node.on('click', 
                                function(e)
                                {
                                    $.ajax(
                                        {
                                            url: '/notifications/ajax.hide.php',
                                            method: 'POST',
                                            data: {notification_id: val.id},
                                            dataType: 'json'
                                        })
                                        .done(
                                            function(response)
                                            {
                                                tr_node.remove();
                                            });
                                });
                            control_node.append(button_node);
        					tr_node
        						.append(date_created_node)
        						.append(message_node)
                                .append(control_node);
        					tbody_node.append(tr_node);
        				});
        			table_node.append(thead_node);
        			table_node.append(tbody_node);    			
        			notifications_pane.append(table_node);
                }
                else
                {
                    let empty_node = $('<div>').addClass('empty').html('Нет новых оповещений.');
                    notifications_pane.append(empty_node);
                }
    		}
  		});
    $.ajax(
        {
            url: '/tasks/ajax.get.summary.php',
            method: 'POST',
            data: null,
            dataType: 'json'
        })
    .done(
        function(response)
        {
            console.log(response);
            tasks_pane.empty();
            let summary_node = $('<div>').addClass('summary');
            let created_node = $('<div>').addClass('created');
            let label_node = $('<div>').addClass('label').html('Созданные');
            let value_node = $('<div>').addClass('value').html(response.data.tasks.count.created);
            created_node
                .append(label_node)
                .append(value_node);
            summary_node.append(created_node);
            let assigned_node = $('<div>').addClass('assigned');
            label_node = $('<div>').addClass('label').html('Назначенные');
            value_node = $('<div>').addClass('value').html(response.data.tasks.count.assigned);
            assigned_node
                .append(label_node)
                .append(value_node);
            summary_node.append(assigned_node);
            let implementation_node = $('<div>').addClass('implementation');
            label_node = $('<div>').addClass('label').html('Выполнение');
            value_node = $('<div>').addClass('value').html(response.data.tasks.count.implementation);
            implementation_node
                .append(label_node)
                .append(value_node);
            summary_node.append(implementation_node);
            let assignee_review_node = $('<div>').addClass('assignee_review');
            label_node = $('<div>').addClass('label').html('Отзыв исполнителя');
            value_node = $('<div>').addClass('value').html(response.data.tasks.count.reviewed_by_author);
            assignee_review_node
                .append(label_node)
                .append(value_node);
            summary_node.append(assignee_review_node);
            tasks_pane.append(summary_node);
            function tasksTable(status, heading)
            {
                let h4_node = $('<h4>').html(heading);
                tasks_pane.append(h4_node);
                if(response.data.tasks[status].length > 0)
                {
                    let container_node = $('<div>').addClass('container').addClass(status);
                    let table_node = $('<table>').addClass('tasks').addClass(status);
                    let thead_node = $('<thead>');
                    let thead_tr_node = $('<tr>');
                    let th_id_node = $('<th>').addClass('id').html('Код');
                    let th_name_node = $('<th>').addClass('name').html('Название');
                    let th_status_node = $('<th>').addClass('status').html('Статус');
                    let th_terms_node = $('<th>').addClass('terms').html('Сроки');
                    thead_tr_node
                        .append(th_id_node)
                        .append(th_name_node)
                        .append(th_status_node)
                        .append(th_terms_node);
                    thead_node.append(thead_tr_node);
                    table_node.append(thead_node);
                    tbody_node = $('<tbody>');
                    response.data.tasks[status].forEach(
                        function(val)
                        {
                            let tr_node = $('<tr>');
                            let td_id_node = $('<td>').addClass('id');
                            let a_node = $('<a>').attr('href', '/tasks/view?id='+val.id).html(val.id);
                            td_id_node.append(a_node);
                            let td_name_node = $('<td>').addClass('name').html(val.name);
                            let td_status_node = $('<td>').addClass('status').addClass(val.status).html(val.status_display);
                            let td_terms_node = $('<td>').addClass('terms');
                            let date_begin_node = $('<div>').html(val.date_begin);
                            let date_end_node = $('<div>').html(val.date_end);
                            td_terms_node
                                .append(date_begin_node)
                                .append(date_end_node);
                            tr_node
                                .append(td_id_node)
                                .append(td_name_node)
                                .append(td_status_node)
                                .append(td_terms_node);
                            tbody_node.append(tr_node);
                        });
                    table_node.append(tbody_node);
                    tasks_pane.append(table_node);
                    let pagination_node = $('<div>');
                    /*

                    */
                }
                else
                {
                    let empty_node = $('<div>').addClass('empty').html('Нет задач в данном статусе');
                    tasks_pane.append(empty_node);
                }
            }
            tasksTable('created', 'Созданные');
            tasksTable('assigned', 'Назначенные');
            tasksTable('implementation', 'Выполнение');
            tasksTable('reviewed_by_author', 'Отзыв исполнителя');
        });
});