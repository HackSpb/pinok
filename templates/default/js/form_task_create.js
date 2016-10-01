				function task_for_user() {
					var task_for = $('input:radio[name=task_for]:checked').val();
						$("#answer").html('');
		  				if (task_for == '1') { 
		  					this.className = 'email';
		  					email.innerHTML = '';
		  					this.className = 'user_ico';
		  					user_ico.innerHTML = '<div class="icon-user button_task_user">';
		  				} else if (task_for == '2') { 
		  					this.className = 'email';
		  					email.innerHTML = '<div class="form-group"><label for="email_friend" class="col-sm-3 control-label">Email</label><div class="col-sm-9"><input type="email" name="email_friend" class="form-control" id="email_friend" required></div></div>';
		  					this.className = 'user_ico';
		  					user_ico.innerHTML = '<div class="icon-for_other button_task_for_other">';
		  				}
				};

				function deadline_for_task() {
					var deadline = $('input:radio[name=deadline]:checked').val();
	  				if (deadline == '1') { 

	  					var now = new Date();
	  					var year = now.getFullYear();
	  					var month = now.getMonth();
	  					var day = now.getDate();
	  					var hour = now.getHours() + 1;

	  					$('#deadline_date').append('<select name="task_deadline_year" class="form-control" id="task_deadline_year"></select>');
	  					for (i=2016;i<2026;i++){
	  						if (i == year) {
	  							$('#task_deadline_year').append('<option selected value="'+i+'">'+i+'</option>');
	  						} else {
	  							$('#task_deadline_year').append('<option value="'+i+'">'+i+'</option>');
	  						}
	  					}

	  					$('#deadline_date').append('<select name="task_deadline_month" class="form-control" id="task_deadline_month"></select>');
	  					var arr = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
						arr.forEach(function(item, i, arr) {
						 	if (i == month) {
	  							$('#task_deadline_month').append('<option selected value="'+i+'">'+item+'</option>');
	  						} else {
	  							$('#task_deadline_month').append('<option value="'+i+'">'+item+'</option>');
	  						}
						});

						$('#deadline_date').append('<select name="task_deadline_day" class="form-control" id="task_deadline_day"></select>');
	  					for (i=1;i<31;i++){
	  						if (i == day) {
	  							$('#task_deadline_day').append('<option selected value="'+i+'">'+i+'</option>')
	  						} else {
	  							$('#task_deadline_day').append('<option value="'+i+'">'+i+'</option>')
	  						}
	  					}

	  					$('#deadline_date').append('<select name="task_deadline_hour" class="form-control" id="task_deadline_hour"></select>');
	  					
	  					for (i=0;i<23;i++){
	  						if (i == hour) {
	  							$('#task_deadline_hour').append('<option selected value="'+i+'">'+i+':00</option>')
	  						} else {
	  							$('#task_deadline_hour').append('<option value="'+i+'">'+i+':00</option>')
	  						}
	  					}

	  					
	  					this.className = 'calendar_ico';
	  					calendar_ico.innerHTML = '<div class="icon-calendar button_task_calendar">';
	  				} else if (deadline == '2') { 
	  					this.className = 'deadline_date';
					  	deadline_date.innerHTML = '';
					  	this.className = 'calendar_ico';
	  					calendar_ico.innerHTML = '<div class="icon-calendar button_task_no_calendar">';
	  				}
				};

				function raiting_for_task() {
					var stars = $('input:radio[name=stars]:checked').val();
	  				if (stars == '1') { 
	  					this.className = 'importance_ico';
		  				importance_ico.innerHTML = '<div class="button_task_imp">1</div>';
	  				} else if (stars == '2') { 
	  					this.className = 'importance_ico';
		  				importance_ico.innerHTML = '<div class="button_task_imp">2</div>';
	  				} else if (stars == '3') { 
	  					this.className = 'importance_ico';
		  				importance_ico.innerHTML = '<div class="button_task_imp">3</div>';
	  				} else if (stars == '4') { 
	  					this.className = 'importance_ico';
		  				importance_ico.innerHTML = '<div class="button_task_imp">4</div>';
	  				} else if (stars == '5') { 
	  					this.className = 'importance_ico';
		  				importance_ico.innerHTML = '<div class="button_task_imp">5</div>';
	  				}
				};

				function type_for_task() {
					var type = $('input:radio[name=type]:checked').val();
	  				if (type == '4') { 
	  					this.className = 'tags_ico';
		  				tags_ico.innerHTML = '<div class="button_task_project">П</div>';
	  				} else if (type == '61') { 
	  					this.className = 'tags_ico';
		  				tags_ico.innerHTML = '<div class="button_task_event">С</div>';
	  				} else if (type == '62') { 
	  					this.className = 'tags_ico';
		  				tags_ico.innerHTML = '<div class="button_task_task">З</div>';
	  				}
				};


				create_task_form.onsubmit = function() {
					var task_for = $('input:radio[name=task_for]:checked').val();
					var email_friend = $('#email_friend').val();
					var task_name = $('#task_name').val();
					var task_deadline_turn = $('input:radio[name=deadline]:checked').val();		
					var task_deadline_year = $('#task_deadline_year').val();
					var task_deadline_month = $('#task_deadline_month').val();
					var task_deadline_day = $('#task_deadline_day').val();
					var task_deadline_hour = $('#task_deadline_hour').val();
					var task_stars = $('input:radio[name=stars]:checked').val();
					var task_type = $('input:radio[name=type]:checked').val();

					//alert(task_for+' '+email_friend+' '+task_name+' '+task_deadline_turn+' '+task_deadline_year+' '+task_deadline_month+' '+task_deadline_day+' '+task_deadline_hour+' '+task_stars+' '+task_type);
					if (task_for == 2 && task_type == 4) {
						var answer = '<div class="alert alert-dismissible alert-warning"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Внимание! </strong> Вы можете создать проект только для себя.</div>';
						$("#answer").html(answer);
						return false;
					} else {
						var query = 'task_for='+task_for+'&email_friend='+email_friend+'&task_name='+task_name+'&task_deadline_turn='+task_deadline_turn+'&task_deadline_year='+task_deadline_year+'&task_deadline_month='+task_deadline_month+'&task_deadline_day='+task_deadline_day+'&task_deadline_hour='+task_deadline_hour+'&task_stars='+task_stars+'&task_type='+task_type;	
						//alert(query);
						//return false;
						$.ajax({
							type: 'POST',
							url: '/task/do/create',
							data: query,
							success: function(answer) {
								$("#answer").html(answer);
								download_list_right();
							}
						});	
						$('#create_task_form')[0].reset();
						this.className = 'user_ico';
		  				user_ico.innerHTML = '<div class="icon-user button_task_user"></div>';
		  				this.className = 'calendar_ico';
		  				calendar_ico.innerHTML = '<div class="icon-calendar button_task_no_calendar"></div>';
		  				this.className = 'importance_ico';
		  				importance_ico.innerHTML = '<div class="icon-notification button_task_importan"></div>';
		  				this.className = 'tags_ico';
		  				tags_ico.innerHTML = '<div class="button_task_task">З</div>';

		  				this.className = 'email';
		  				email.innerHTML = '';
		  				this.className = 'deadline_date';
		  				deadline_date.innerHTML = '';
						return false;
					}
				};

				$(document).ready(function(){
				    download_list_right();
				    render_list_content();
				});

				function download_list_right(){
					$.getJSON('/task/list/right',
				        function(data){
				        console.log(data);
				        var i; 
				       
				        var s = $('#task_list_right').html();// берем образцовый хтмл из скрытого шаблона 
				        if (data.imp != 1) {
							for (i=0;i<50;i++){
								directives = {
									link: {
									    href: function(params) {
									    	return "?task_n=" + data.imp['number_'+i].t_id;
										}
									}
								};
								$('#imp').append("<div id='id_imp_"+i+"'>"+ s + "</div>"); // плодим дивы по нашему шаблону с уникальными ид
					
								$('#id_imp_'+i).render(data.imp['number_'+i], directives); //рендерим свежевставленные дивы с шаблонами
							}	
						}
						if (data.last != 1) {
							for (i=0;i<50;i++){
								directives = {
									link: {
									    href: function(params) {
									    	return "?task_n=" + data.last['number_'+i].t_id;
									    }
									}
								};
							
								$('#last').append("<div id='id_last_"+i+"'>"+ s + "</div>"); 
					
								$('#id_last_'+i).render(data.last['number_'+i], directives); 
							}
						}
						if (data.fav != 1) {
							for (i=0;i<50;i++){
								directives = {
									link: {
									    href: function(params) {
									    	return "?task_n=" + data.fav['number_'+i].t_id;
									    }
									}
								};
					
								$('#favourite').append("<div id='id_fav_"+i+"'>"+ s + "</div>"); 
					
								$('#id_fav_'+i).render(data.fav['number_'+i], directives); 
							}	
						}
						if (data.new != 1) {
							for (i=0;i<50;i++){
								directives = {
									link: {
									    href: function(params) {
									    	return "?task_n=" + data.new['number_'+i].t_id;
									    }
									}
								};
					
								$('#new').append("<div id='id_new_"+i+"'>"+ s + "</div>"); 
					
								$('#id_new_'+i).render(data.new['number_'+i], directives); 
							}	
						}
				    });
				};

				function render_list_content(){
					var y = location.search;
					$.getJSON('/task/list/content' + y,
						function(data){
							console.log(data);
							var countall = data['count_all'];
							var lim = data['lim'];
							var task = data['task_type'];
							var page = countall/lim;
							var newpage = Math.ceil(page);
							var z;
							if (countall>lim){
								for (z=1; z<=newpage; z++) {
									$('#page').append('<a onclick="sendData(\'?task='+task+'&page='+z+'\');" href="javascript://"><button>'+z+'</button></a>');  
								}
							}
							var i;  
							var s = $('#task_list_content').html();
							for (i=0;i<=lim;i++) {
								directives = {
											link: {
											    href: function(params) {
											    	return "?task_n=" + data.content['number_'+i].t_id;
											    }
											}
										};	
								$('#content').append("<div id='id_content_list_"+i+"'>"+ s + "</div>"); 
						
								$('#id_content_list_'+i).render(data.content['number_'+i], directives); 
							};
						}
					)
				};


			function sendData (sData) {
				location.search = sData;
			}; 
	