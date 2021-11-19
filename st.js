var st = {};
st.ShowModal = 
	function(selector)
	{
		let modal = {};
		modal.create = function(selector)
		{	
		}
		let window_node = $('<div>').css(
			{
				display: 'flex',
				flexDirection: 'column',
				alignItems: 'center',
				boxShadow: '0px 0px 32px rgba(0, 0, 0, .25)',
				marginTop: '10vh'
			})
			.addClass('window');
		let window_body_node = $(selector).clone().css(
			{
				boxSizing: 'border-box'
			})
			.addClass('body');
		let window_titlebar_node = $('<div>').css(
			{
				boxSizing: 'border-box',
				width: '100%',
				padding: '8px',
				flex: '1 1 auto',
				backgroundColor: '#605ca8',
				color: 'white',
				display: 'flex',
				alignItems: 'center'
			})
			.addClass('titlebar');
		let window_titlebar_icon_node = $('<div>').css(
			{
				boxSizing: 'border-box',
				width: '14px',
				height: '14px',
				flex: '0 0 auto',
				marginRight: '8px',
				backgroundImage: 'url(\'/images/ui/window.titlebar.icon.default.png\')',
				backgroundSize: 'cover',
				backgroundPosition: 'center'
			})
			.addClass('icon');
		let titlebar_text = window_body_node.data('window-titlebar-caption').trim();
		let window_titlebar_caption_node = $('<div>').css(
			{
				flex: '1 1 auto'
			})
			.addClass('caption');
		if(titlebar_text !== '')
		{
			window_titlebar_caption_node.text(titlebar_text);
		}
		let window_titlebar_control_node = $('<div>').css(
			{
				display: 'block',
				boxSizing: 'border-box',
				width: '14px',
				height: '14px',
				flex: '0 0 auto',
				backgroundImage: 'url(\'/images/ui/icon.close.white.png\')',
				backgroundSize: 'cover',
				backgroundPosition: 'center',
				cursor: 'pointer'						
			})
			.addClass('button_close');
		let closeModal = 
			function(e)
			{
				$(this).parents('div.st_modal_overlay').remove();
			}
		window_titlebar_control_node.on('click', closeModal);
		window_body_node.find("div.controls > button.cancel").on('click', closeModal);
		let modal_overlay_node = $("<div>").css(
			{
				boxSizing: "border-box",
				width: "100vw",
				height: "100vh",
				position: "fixed",
				zIndex: "100",
				backgroundColor: "rgba(0, 0, 0, .25)",
				display: 'flex',
				flexDirection: 'column',
				justifyContent: 'flex-start',
				alignItems: 'center'
			})
			.addClass('st_modal_overlay');
		window_titlebar_node.append(window_titlebar_icon_node);
		window_titlebar_node.append(window_titlebar_caption_node);
		window_titlebar_node.append(window_titlebar_control_node);
		window_node.append(window_titlebar_node);
		window_node.append(window_body_node);
		modal_overlay_node.append(window_node);
		window_body_node.show();
		modal_overlay_node.show();
		$("body").append(modal_overlay_node);
		return window_node;
	};
st.DatePicker = 
	function(input)
	{
		let datepicker = new TheDatepicker.Datepicker(input.get(0));
		datepicker.options.setInputFormat("Y-n-d");
		datepicker.options.translator.setDayOfWeekTranslation(TheDatepicker.DayOfWeek.Monday, "Пн");
		datepicker.options.translator.setDayOfWeekTranslation(TheDatepicker.DayOfWeek.Tuesday, "Вт");
		datepicker.options.translator.setDayOfWeekTranslation(TheDatepicker.DayOfWeek.Wednesday, "Ср");
		datepicker.options.translator.setDayOfWeekTranslation(TheDatepicker.DayOfWeek.Thursday, "Чт");
		datepicker.options.translator.setDayOfWeekTranslation(TheDatepicker.DayOfWeek.Friday, "Пт");
		datepicker.options.translator.setDayOfWeekTranslation(TheDatepicker.DayOfWeek.Saturday, "Сб");
		datepicker.options.translator.setDayOfWeekTranslation(TheDatepicker.DayOfWeek.Sunday, "Вс");
		datepicker.options.translator.setDayOfWeekFullTranslation(TheDatepicker.DayOfWeek.Monday, "Понедельник");
		datepicker.options.translator.setDayOfWeekFullTranslation(TheDatepicker.DayOfWeek.Tuesday, "Вторник");
		datepicker.options.translator.setDayOfWeekFullTranslation(TheDatepicker.DayOfWeek.Wednesday, "Среда");
		datepicker.options.translator.setDayOfWeekFullTranslation(TheDatepicker.DayOfWeek.Thursday, "Четверг");
		datepicker.options.translator.setDayOfWeekFullTranslation(TheDatepicker.DayOfWeek.Friday, "Пятница");
		datepicker.options.translator.setDayOfWeekFullTranslation(TheDatepicker.DayOfWeek.Saturday, "Суббоота");
		datepicker.options.translator.setDayOfWeekFullTranslation(TheDatepicker.DayOfWeek.Sunday, "Воскресенье");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.January, "Янв");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.February, "Фев");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.March, "Мар");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.April, "Апр");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.May, "Май");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.June, "Июн");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.July, "Июл");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.August, "Авг");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.September, "Сен");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.October, "Окт");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.November, "Ноя");
		datepicker.options.translator.setMonthTranslation(TheDatepicker.Month.December, "Дек");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.January, "Январь");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.February, "Февраль");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.March, "Март");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.April, "Апрель");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.May, "Май");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.June, "Июнь");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.July, "Июль");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.August, "Август");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.September, "Сентябрь");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.October, "Октябрь");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.November, "Ноябрь");
		datepicker.options.translator.setMonthShortTranslation(TheDatepicker.Month.December, "Декабрь");
		datepicker.options.translator.setTitleTranslation(TheDatepicker.TitleName.GoBack, "Назад");
		datepicker.options.translator.setTitleTranslation(TheDatepicker.TitleName.GoForward, "Вперёд");
		datepicker.options.translator.setTitleTranslation(TheDatepicker.TitleName.Close, "Закрыть");
		datepicker.options.translator.setTitleTranslation(TheDatepicker.TitleName.Reset, "Сбросить");
		return datepicker;
	};
st.TimePicker =
	function(options)
	{
		let timepicker = {};
		timepicker.show = function()
			{			
				function closeModal(e)
				{
					$(this).parents('div.st_modal_overlay').remove();
				}
				function quickSet(e)
				{
					e.data.node.val($(this).text().trim().padStart(2, '0'));
				}
				let timepicker_node = $('<div>').addClass('timepicker');
				let caption_node = $('<div>').addClass('caption');
				let icon_node = $('<div>').addClass('icon').addClass('info');
				let text_node = $('<div>').addClass('text').text('Задать время');
				let button_node = $('<div>').addClass('button').addClass('close');
				button_node.on('click', closeModal);
				caption_node
					.append(icon_node)
					.append(text_node)
					.append(button_node);
				let body_node = $('<div>').addClass('body');
				let time_val_current = options.targetNode.val();
				let time_re = /([0-9]{1,2}):([0-9]{1,2})/;
				let match = time_val_current.match(time_re);
				let time_val_current_hh = match[1];
				let time_val_current_mm = match[2];
				let input_hh_node = $('<input type="text" name="hh">');
				input_hh_node.val(time_val_current_hh);
				let input_mm_node = $('<input type="text" name="mm">');
				input_mm_node.val(time_val_current_mm);
				let display_node = $('<div>').addClass('display');
				display_node
					.append(input_hh_node)
					.append(input_mm_node);
				let label_hh_node = $('<div>').addClass('label').addClass('hh').text('Час');
				let quick_hh_node = $('<div>').addClass('quick').addClass('hh');
				quick_hh_node
					.append($('<div>').text('0'))
					.append($('<div>').text('3'))
					.append($('<div>').text('6'))
					.append($('<div>').text('9'))
					.append($('<div>').text('12'))
					.append($('<div>').text('15'))
					.append($('<div>').text('18'))
					.append($('<div>').text('21'));
				quick_hh_node.find('div').on('click', {node: input_hh_node}, quickSet);
				let slider_hh_node = $('<input type="range" min="0" max="23" value="0">').css({});
				slider_hh_node.val(time_val_current_hh);
				let label_mm_node = $('<div>').addClass('label').addClass('mm').text('Минута');
				let quick_mm_node = $('<div>').addClass('quick').addClass('mm');
				quick_mm_node
					.append($('<div>').text('0'))
					.append($('<div>').text('15'))
					.append($('<div>').text('30'))
					.append($('<div>').text('45'));
				let slider_mm_node = $('<input type="range" min="0" max="59" value="0">').css({});
				slider_mm_node.val(time_val_current_mm);
				quick_mm_node.find('div').on('click', {node: input_mm_node}, quickSet);
				slider_hh_node.on('input', function(e)
					{
						let hh = $(this).val().trim().toString();
						if(hh.length < 2)
						{
							hh = '0' + hh;
						}
						input_hh_node.val(hh);
					});
				slider_mm_node.on('input', function(e)
					{
						let mm = $(this).val().trim().toString();
						if(mm.length < 2)
						{
							mm = '0' + mm;
						}
						input_mm_node.val(mm);
					});
				let controls_node = $('<div>').addClass('controls');
				let cancel_button_node = $('<button>');
				let okay_button_node = $('<button>');
				cancel_button_node.text('Отмена');
				okay_button_node.text('ОК');
				cancel_button_node.on('click', closeModal);
				okay_button_node.on('click', function(e)
					{
						let time = input_hh_node.val() + ':' + input_mm_node.val();
						options.targetNode.val(time);
						$(this).parents('div.st_modal_overlay').remove();
					});
				controls_node
					.append(cancel_button_node)
					.append(okay_button_node);
				body_node
					.append(display_node)
					.append(label_hh_node)
					.append(quick_hh_node)
					.append(slider_hh_node)
					.append(label_mm_node)
					.append(quick_mm_node)
					.append(slider_mm_node)
					.append(controls_node);				
				timepicker_node
					.append(caption_node)
					.append(body_node);
				let modal_overlay_node = $("<div>").css(
					{
						boxSizing: 'border-box',
						width: '100vw',
						height: '100vh',
						position: 'fixed',
						zIndex: '101',
						backgroundColor: 'rgba(0, 0, 0, .25)',
						display: 'flex',
						flexDirection: 'column',
						justifyContent: 'flex-start',
						alignItems: 'center'
					})
					.addClass('st_modal_overlay');
				modal_overlay_node.append(timepicker_node);
				$('body').append(modal_overlay_node);
			}
		return timepicker;
	}
st.DatePicker2 = 
	function(o)
	{
		let st_overlay_node = $('<div>').addClass('st_modal_overlay');
		let window_node = $('<div>').addClass('window').css(
			{
				boxSizing: 'border-box',
				width: '300px',
				display: 'flex',
				flexDirection: 'column',
				alignItems: 'center',
				boxShadow: 'rgba(0, 0, 0, 0.25) 0px 0px 32px',
				margin: 'auto'
			});
		let titlebar_node = $('<div>').addClass('titlebar').css(
			{
				boxSizing: 'border-box',
				flex: '1 1 auto',
				width: '100%',
				padding: '8px',
				display: 'flex',
				alignItems: 'center',
				backgroundColor: 'rgb(96, 92, 168)',
				color: 'white'
			});
		let icon_node = $('<div>').addClass('icon').css(
			{
				boxSizing: 'border-box',
				width: '14px',
				height: '14px',
				flex: '0 0 auto',
				marginRight: '8px',
				backgroundImage: 'url(\'/images/ui/icon.info.svg\')',
				backgroundSize: 'cover',
				backgroudPosition: 'center'
			});
		let text_node = $('<div>').addClass('text').text('Задание даты').css(
			{
				flex: '1 1 auto',
				color: 'white'
			});
		let close_node = $('<div>').addClass('close').css(
			{
				boxSizing: 'border-box',
				width: '10px',
				height: '10px',
				flex: '0 0 auto',
				backgroundImage: 'url(\'/images/ui/icon.close.white.png\')',
				backgroundSize: 'cover',
				backgroundPosition: 'center',
				marginLeft: '8px',
				cursor: 'pointer'
			});
		close_node.on('click', (e) => st_overlay_node.remove());
		titlebar_node
			.append(icon_node)
			.append(text_node)
			.append(close_node);
		let body_node = $('<div>').addClass('body').css(
			{
				boxSizing: 'border-box',
				width: '100%',
				padding: '8px',
				backgroundColor: 'white'
			});
		let controls_node = $('<div>').addClass('controls').css(
			{
				display: 'flex',
				justifyContent: 'space-between',
				marginBottom: '8px'
			});
		let prev_node = $('<button>').addClass('prev').css(
			{
				boxSizing: 'border-box',
				width: '20px',
				height: '20px',
				backgroundImage: 'url(\'/images/ui/icon.prev.png\')',
				backgroundSize: 'cover',
				backgroundPosition: 'center',
				border: 'none'
			});
		let next_node = $('<button>').addClass('next').css(
			{
				boxSizing: 'border-box',
				width: '20px',
				height: '20px',
				backgroundImage: 'url(\'/images/ui/icon.next.png\')',
				backgroundSize: 'cover',
				backgroundPosition: 'center',
				border: 'none'
			});
		let display_node = $('<div>').addClass('display').css(
			{
				textAlign: 'center',
				border: '1px solid lightgrey',
				padding: '4px 14px'
			});
		let hidden_month_node = $('<input>').attr('type', 'hidden').attr('name', 'month');
		let hidden_year_node = $('<input>').attr('type', 'hidden').attr('name', 'year');
		controls_node
			.append(prev_node)
			.append(display_node)
			.append(next_node)
			.append(hidden_month_node)
			.append(hidden_year_node);
		body_node.append(controls_node);
		let grid_container_node = $('<div>').addClass('grid').css(
			{
				display: 'grid',
				gridRowGap: '4px',
				gridColumnGap: '4px',
				gridTemplateColumns: 'auto auto auto auto auto auto auto',
				gridTemplateRows: 'auto',
			});
		let weekdays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
		let months = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
		let this_month = {};
		let prev_month = {};
		let next_month = {};
		function displayHeader()
		{		
			for(let e of weekdays)
			{
				let item_node = $('<div>').addClass('header').css(
					{
						// border: '1px solid lightgrey',
						padding: '2px',
						textAlign: 'center',
						fontWeight: '600',
						fontSize: '12px'
					});
				if(e === 'Сб' || e === 'Вс')
				{
					item_node.addClass('weekend').css(
						{
							backgroundColor: '#f0f0f0'
						});
				}
				item_node.text(e);
				grid_container_node.append(item_node);
			}
		}
		function displayDays(_o)
		{
			let tmp;
			tmp = _o.dto;
			this_month.dto = new Date(tmp.getFullYear(), tmp.getMonth(), 1);
			hidden_month_node = this_month.dto.getMonth();
			hidden_year_node = this_month.dto.getFullYear();
			if(this_month.dto.getMonth() > 0)
			{
				prev_month.dto = new Date(this_month.dto.getFullYear(), this_month.dto.getMonth() - 1, 1);
			}
			else
			{
				prev_month.dto = new Date(this_month.dto.getFullYear() - 1, 11, 1);			
			}
			if(this_month.dto.getMonth() < 11)
			{
				next_month.dto = new Date(this_month.dto.getFullYear(), this_month.dto.getMonth() + 1, 1);
			}
			else
			{
				next_month.dto = new Date(this_month.dto.getFullYear() + 1, 0, 1);
			}
			tmp = new Date(this_month.dto - 1);
			prev_month.last_day = tmp.getDate();
			tmp = new Date(next_month.dto - 1);
			this_month.last_day = tmp.getDate();
			let day_items = [];
			let weekday = (this_month.dto.getDay() !== 0) ? this_month.dto.getDay() - 1 : 6;
			for(let i = weekday - 1; i >= 0; i--)
			{
				day_items.push(prev_month.last_day - i);
			}
			for(let i = 1; i <= this_month.last_day; i++)
			{
				day_items.push(i);
			}
			weekday = (next_month.dto.getDay() !== 0) ? next_month.dto.getDay() - 1 : 6;
			if(weekday > 0)
			{
				for(let i = 1; weekday < 7; i++, weekday++)
				{
					day_items.push(i);
				}			
			}
			grid_container_node.find('div.item:not(.header)').remove();
			for(i = 0; i < day_items.length; i++)
			{
				let item_node = $('<div>').addClass('item').css(
					{
						border: '1px solid lightgrey',
						padding: '2px',
						textAlign: 'center',
						fontSize: '12px',
						cursor: 'pointer'
					});				
				let mod = i % 7;
				if(mod === 5 || mod === 6)
				{
					item_node.css(
						{
							backgroundColor: '#f0f0f0'
						});
				}
				item_node.on('mouseenter', (e) => item_node.css({backgroundColor: 'cornflowerblue', color: 'white'}));
				item_node.on('mouseleave', (e) => item_node.css({backgroundColor: 'transparent', color: 'inherit'}));
				let date_text = this_month.dto.getFullYear().toString().padStart(4, '0') + '-' + (this_month.dto.getMonth() + 1).toString().padStart(2, '0') + '-' + day_items[i].toString().padStart(2, '0');
				item_node.on('click',
					function(e)
					{
						o.target.val(date_text);
						st_overlay_node.remove();
						o.target.trigger('change');
					});
				item_node.text(day_items[i]);
				grid_container_node.append(item_node);
			}
			let display_text = months[this_month.dto.getMonth()] + ' ' + this_month.dto.getFullYear();
			display_node.text(display_text);
		}
		prev_node.on('click', (e) => displayDays({dto: prev_month.dto}));
		next_node.on('click', (e) => displayDays({dto: next_month.dto}));
		displayHeader();
		displayDays({dto: new Date()});
		body_node
			.append(grid_container_node);
		window_node
			.append(titlebar_node)
			.append(body_node);
		st_overlay_node.append(window_node);
		$('body').append(st_overlay_node);
	};
st.generateUserPhoto = 
	function(user)
	{
		let photo_node = $('<div>').addClass('generated').css(
			{
				background: 'linear-gradient(to bottom right, #dfbfff, mediumpurple)',
				color: 'white',
				fontSize: '42px',
				fontWeight: '100',
				boxSizing: 'border-box',
				width: '100%',
				height: '100%',
				display: 'flex',
				padding: '8px',
				justifyContent: 'center',
				alignItems: 'center'
			});
		let text = user.firstname.charAt(0) + user.lastname.charAt(0);
		text = text.toUpperCase();
		photo_node.text(text);
		return photo_node;
	}
st.user = {};
$(function()
{
	st.navitem = JSON.parse($('div.sidebar').find('div.nav').find('input[name="st_navitem"]').val().trim());
	for(let i = 0, node = $('div.sidebar').find('div.nav').find('a.navitem'); node.length > 0; i++, node = node.find('a.navitem'))
	{
		node.filter('a.navitem.'+st.navitem[0]).addClass('active');
	}
	$(document).keydown(
		function(e)
		{ 
	    	if(e.keyCode === 27)
	    	{
	    		$('div.st_modal_overlay').last().remove();
	    	} 
		});
	st.user.id = Number($('body > input[name="user_id"]').val().trim());
	st.user.authorization_level = $('body > input[name="user_authorization_level"]').val().trim();
	st.user.firstname = $('body > input[name="person_firstname"]').val().trim();
	st.user.lastname = $('body > input[name="person_lastname"]').val().trim();
});