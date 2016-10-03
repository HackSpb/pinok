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
				    task_information();
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

									$('#page').append('<a onclick="sendData(\'?task_t='+task+'&page='+z+'\');" href="javascript://"><button>'+z+'</button></a>');  
								}
							}
							var i;  
							
							for (i=0;i<=lim;i++) {

								directives = {
											link: {
											    href: function(params) {
											    	return "?task_n=" + data.content['number_'+i].t_id;
											    }
											}
										};
								if (task == 'imp') {
									if (data.content['number_'+i]['t_raiting'] == 5) {
										var s = $('#task_warn_5').html();
									} else if (data.content['number_'+i]['t_raiting'] == 4) {
										var s = $('#task_warn_4').html();
									} else if (data.content['number_'+i]['t_raiting'] == 3) {
										var s = $('#task_warn_3').html();
									} else if (data.content['number_'+i]['t_raiting'] == 2) {
										var s = $('#task_warn_2').html();
									} else if (data.content['number_'+i]['t_raiting'] == 1) {
										var s = $('#task_warn_1').html();
									}
								} else {
									if (data.content['number_'+i]['t_type'] == 4) {
										var s = $('#project_list_content').html();
									} else if (data.content['number_'+i]['t_type'] == 61) {
										var s = $('#event_list_content').html();
									} else if (data.content['number_'+i]['t_type'] == 62) {
										var s = $('#task_list_content').html();
									}
								}
								
								$('#list').append("<div id='id_content_list_"+i+"'>"+ s + "</div>");
								$('#id_content_list_'+i).render(data.content['number_'+i], directives); 
							};
						}
					)
				};


			function sendData (sData) {
				location.search = sData;
			}; 
		
			function task_information() {
				var y = location.search;
					$.getJSON('/task/information/content' + y,
						function(data){
							console.log(data);
							if (data.type == 4) {
								if (data.project.t_type == 4) {
									data.project.t_type = 'Проект';
								}
								if (data.project.t_status == 1) {
									data.project.t_status = 'Активен';
								} else if (data.project.t_status == 0) {
									data.project.t_status = 'Выполнен';
								}
								if (data.project.t_date_finish == '0000-00-00 00:00:00') {
									data.project.t_date_finish = 'Не назначено';
								}
								var i;  
								var s = $('#task_information_content').html();
								
								$('#project').append("<div id='id_proj_inf'>"+ s + "</div>"); 
							
								$('#id_proj_inf').render(data['project']);

								var countall = data['count_all'];
								var lim = data['lim'];
								var page = countall/lim;
								var newpage = Math.ceil(page);
								var id = data['id'];
								var z;
								if (countall>lim){
									for (z=1; z<=newpage; z++) {
										
										$('#page').append('<a onclick="sendData(\'?task_n='+id+'&page='+z+'\');" href="javascript://"><button>'+z+'</button></a>');  
									}
								}
 
								var s = $('#task_list_content').html();
								
								for (i=0;i<=lim;i++) {
									if ( [('number_' + i)] in data.content ) {
										directives = {
													link: {
													    href: function(params) {
													    	return "?task_n=" + data.content['number_'+i].t_id;
													    }
													}
												};	
										$('#list').append("<div id='id_content_list_"+i+"'>"+ s + "</div>"); 
								
										$('#id_content_list_'+i).render(data.content['number_'+i], directives); 
									} 
								};
								
							} else {
								if (data.task.t_type == 62) {
									data.task.t_type = 'Задача';
								} else if (data.task.t_type == 61) {
									data.task.t_type = 'Событие';
								} else if (data.task.t_type == 4) {
									data.task.t_type = 'Проект';
								}
								if (data.task.t_status == 1) {
									data.task.t_status = 'Активна';
								} else if (data.task.t_status == 0) {
									data.task.t_status = 'Выполнена';
								}
								if (data.task.t_date_finish == '0000-00-00 00:00:00') {
									data.task.t_date_finish = 'Не назначено';
								}
								var i;  
								var s = $('#task_information_content').html();
								
								$('#list').append("<div id='id_content_inf'>"+ s + "</div>"); 
							
								$('#id_content_inf').render(data['task']); 
							}
						}
					)
			}

			function update_status() {
				var y = location.search;
				$.ajax({
					type: 'POST',
					url: '/task/update/status'+y,
					success: function(answer) {
						$("#answer").html(answer);
						this.className = 'project';
		  				project.innerHTML = '';
		  				this.className = 'list';
		  				list.innerHTML = '';
		  				this.className = 'page';
		  				page.innerHTML = '';
		  				task_information();
					}
				});	
			}

			function update_archive() {
				var y = location.search;
				$.ajax({
					type: 'POST',
					url: '/task/update/archive'+y,
					success: function(answer) {
						$("#answer").html(answer);
						this.className = 'project';
		  				project.innerHTML = '';
		  				this.className = 'list';
		  				list.innerHTML = '';
		  				this.className = 'page';
		  				page.innerHTML = '';
		  				task_information();
					}
				});	
			}

			function update_cancel() {
				var y = location.search;
				$.ajax({
					type: 'POST',
					url: '/task/update/cancel'+y,
					success: function(answer) {
						$("#answer").html(answer);
						this.className = 'project';
		  				project.innerHTML = '';
		  				this.className = 'list';
		  				list.innerHTML = '';
		  				this.className = 'page';
		  				page.innerHTML = '';
		  				task_information();
					}
				});	
			}

			function update_delete() {
				var y = location.search;
				$.ajax({
					type: 'POST',
					url: '/task/update/delete'+y,
					success: function(answer) {
						$("#answer").html(answer);
						this.className = 'project';
		  				project.innerHTML = '';
		  				this.className = 'list';
		  				list.innerHTML = '';
		  				this.className = 'page';
		  				page.innerHTML = '';
		  				task_information();
					}
				});	
			}