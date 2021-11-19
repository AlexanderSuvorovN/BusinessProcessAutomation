$(function()
{
	function FormCheck()
	{
		let email = email_node.val().trim();
		let password = password_node.val().trim();
		let email_re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	    if(email === '' || password === '')
	    {
	    	alert('Необходимо ввести e-mail и пароль');
	    	return false;
	    }
	    if(email_re.test(String(email).toLowerCase()) === false)
	    {
	    	alert('Неверный формат e-mail');
	    	return false;
	    }
	    return true;
	}
	let form = $('div.form.login');
	let email_node = form.find("input[name='email']");
	let password_node = form.find("input[name='password']");
	let remember_node = form.find("div.remember").find("input[type='hidden']");
	let button_node = form.find('div.controls > button');
	$("div.checkbox").on("click",
		function(e)
		{
            let checkbox = $(this);
            let input = checkbox.siblings("input[type='hidden']");
            let tick = checkbox.find("div.tick");
            let value = (tick.length > 0);
            if(value)
            {
                tick.remove();
                input.val(false);
            }
            else
            {
                $("<div>").addClass("tick").appendTo(checkbox);
                input.val(true);
            }
		});
	$(document).on('keydown',
		function(e)
		{
			if(e.keyCode === 13)
			{
				console.log('submit');
				$('div.form button').trigger('click');
			}
		})
	button_node.on("click",
		function(e)
		{
			if(FormCheck())
			{
				let email = email_node.val().trim();
				let password = password_node.val().trim();
				let remember = form.find("div.remember").find("input[type='hidden']").val().trim();
				$.ajax(
					{
						url: "/auth", 
						method: "POST",
						data: 
							{
								'email': email,
								'password': password,
								'remember': remember
							},
						dataType: "json"
					})
				  	.done(function(response)
				  		{
				    		console.log(response);
				    		if(response.status === "success")
				    		{
								window.location.href = "/main";
				    		}
				    		else
				    		{
				    			alert('Неверные имя пользователя и/или пароль');
				    		}
				  		})
				  	.fail(function()
				  		{
				  			// console.log("AJAX failed");
						})
					.always(function()
						{
							// console.log("AJAX complete");
				  		});
			}
		});
});