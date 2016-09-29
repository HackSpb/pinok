<?php

	function create_task ($app, $request) {
		$task_for = ($request->get('task_for') == 'undefined') ? 0 : $request->get('task_for');
		$task[] = $email_friend = ($request->get('email_friend') == 'undefined') ? NULL : $request->get('email_friend');
		$task[] = $task_name = ($request->get('task_name') == 'undefined') ? NULL : $request->get('task_name');
		$task[] = $task_short_name = substr($task_name, 0, 20).'..';
		$task_deadline_turn = ($request->get('task_deadline_turn') == 'undefined') ? 0 : $request->get('task_deadline_turn');
		$task_deadline_year = ($request->get('task_deadline_year') == 'undefined') ? 0 : $request->get('task_deadline_year');
		$task_deadline_month = ($request->get('task_deadline_month') == 'undefined') ? 0 : $request->get('task_deadline_month');
		$task_deadline_day = ($request->get('task_deadline_day') == 'undefined') ? 0 : $request->get('task_deadline_day');
		$task_deadline_hour = ($request->get('task_deadline_hour') == 'undefined') ? 0 : $request->get('task_deadline_hour');
		$task[] = $task_type = ($request->get('task_type') == 'undefined') ? 0 : $request->get('task_type');
		$task[] = $task_stars = ($request->get('task_stars') == 'undefined') ? 0 : $request->get('task_stars');
		$task[] = $task_date_create = date("Y-m-d H:i:s");
		$task[] = $task_date_finish = ($task_deadline_turn == 0) ? 'NULL' : $task_deadline_year.'-'.$task_deadline_month.'-'.$task_deadline_day.' '.$task_deadline_hour.':00:00';

		if(empty($task_name)){
			return "<div class=\"alert alert-dismissible alert-warning\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						<strong>Внимание! </strong> Укажите название для задачи.
					</div>";
		}
		
		//return $task_for.'<br>'.$email_friend.'<br>'.$task_name.'<br>'.$task_date_create.'<br>'.$task_date_finish.'<br>'.$task_type;

		global $dbh;
		global $config;
		global $mailer;

		if ($task_type == 4) {
			$answer = create_project($task, $dbh);
		} else {
			if ($task_for == 1) {
				$answer = create_personal_task($task, $dbh);
			} elseif($task_for == 2) {
				$answer = create_task_for_another($task, $dbh, $config, $mailer);
			}
		}
		return $answer;
	}

	function create_project($task, $dbh){
		$t_name=$task[1];
		$t_type=$task[2];
		$t_create=$task[3];
		$t_finish=$task[4];

		$sql = "INSERT INTO tasks (t_name, t_date_create, t_date_finish, t_type, t_accept_with_task) VALUES (:t_name, :t_date_create, :t_date_finish, :t_type, :t_accept_with_task)";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_type' => $t_type, ':t_accept_with_task' => 1));

		$sql = "SELECT * FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create));
		$result_task = $sth->fetch(PDO::FETCH_ASSOC);

		$sql = "INSERT INTO users_tasks (u_id, t_id, ut_role) VALUES (:u_id_creat, :t_id, :ut_role_creat), (:u_id_exec, :t_id, :ut_role_exec)";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':u_id_creat' => $_SESSION['user']['u_id'], ':t_id' => $result_task['t_id'], ':ut_role_creat' => 1, ':u_id_exec' => $_SESSION['user']['u_id'], ':ut_role_exec' => 2));

		$sql = "INSERT INTO groups (t_id, u_id, g_user_role) VALUES (:t_id, :u_id, :g_user_role_cr), (:t_id, :u_id, :g_user_role_ad)";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':u_id' => $_SESSION['user']['u_id'], ':t_id' => $result_task['t_id'], ':g_user_role_cr' => 2, ':g_user_role_ad' => 4));

		return "<div class=\"alert alert-dismissible alert-success\">
					<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
					<strong>Замечательно! </strong> проект " . $t_name . " успешно создана!
				</div>";
	}

	function create_personal_task($task, $dbh){
		//for me
		$t_name=$task[1];
		$t_type=$task[2];
		$t_create=$task[3];
		$t_finish=$task[4];

		$sql = "INSERT INTO tasks (t_name, t_date_create, t_date_finish, t_type, t_accept_with_task) VALUES (:t_name, :t_date_create, :t_date_finish, :t_type, :t_accept_with_task)";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_type' => $t_type, ':t_accept_with_task' => 1));

		$sql = "SELECT * FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create));
		$result_task = $sth->fetch(PDO::FETCH_ASSOC);

		$sql = "INSERT INTO users_tasks (u_id, t_id, ut_role) VALUES (:u_id_creat, :t_id, :ut_role_creat), (:u_id_exec, :t_id, :ut_role_exec)";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':u_id_creat' => $_SESSION['user']['u_id'], ':t_id' => $result_task['t_id'], ':ut_role_creat' => 1, ':u_id_exec' => $_SESSION['user']['u_id'], ':ut_role_exec' => 2));

		return "<div class=\"alert alert-dismissible alert-success\">
					<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
					<strong>Замечательно! </strong> Задача " . $t_name . " успешно создана!
				</div>";
	}

	function create_task_for_another($task, $dbh, $config, $mailer){
		 //for another man
		$t_fr_email=$task[0];
		$t_name=$task[1];
		$t_type=$task[2];
		$t_create=$task[3];
		$t_finish=$task[4];

		if(empty($t_fr_email)){
			return "<div class=\"alert alert-dismissible alert-warning\">
				<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
				<strong>Внимание! </strong> Укажите email получателя задачи.
				</div>";
		}

		$sql = "select * from tasks left join users_tasks USING (t_id) where ut_role=1 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=2) and t_date_create > NOW() - INTERVAL 1 DAY";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_id' => $_SESSION['user']['u_id']));
		$result = $sth->fetchAll();
		$count_result_today = count($result);

		if ($_SESSION['user']['u_tarif'] == 1) $max_count = $config['settings']['count_task_for_another_people']['tarif_free'];
		if ($_SESSION['user']['u_tarif'] == 2) $max_count = $config['settings']['count_task_for_another_people']['tarif_sms'];
		if ($_SESSION['user']['u_tarif'] == 3) $max_count = $config['settings']['count_task_for_another_people']['tarif_call'];

		if ($count_result_today<$max_count) {
			$sql = "SELECT * FROM users left join user_settings USING (u_id) WHERE u_email = :email_friend";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':email_friend' => $t_fr_email));
			$result_us = $sth->fetch(PDO::FETCH_ASSOC);

			if ($result_us == TRUE) { //email есть

				if ($result_us['u_status'] == 1) { //пользователь активирован (создание задачи и отправка сообщения о новой задачи)
					$sql = "INSERT INTO tasks (t_name, t_date_create, t_date_finish, t_type) VALUES (:t_name, :t_date_create, :t_date_finish, :t_type)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_type' => $t_type));

					$sql = "SELECT t_id FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create));
					$result_task = $sth->fetch(PDO::FETCH_ASSOC);

					$sql = "INSERT INTO users_tasks (u_id, t_id, ut_role) VALUES (:u_id_creat, :t_id, :ut_role_creat), (:u_id_exec, :t_id, :ut_role_exec)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':u_id_creat' => $_SESSION['user']['u_id'], ':t_id' => $result_task['t_id'], ':ut_role_creat' => 1, ':u_id_exec' => $result_us['u_id'], ':ut_role_exec' => 2));

					//if ($result_us['us_turn_notification_about_new_task'] == 1) {
						//$message = \Swift_Message::newInstance()
						//	->setSubject('Для Вас есть новая задача')
						//	->setFrom(array($config['mail']['user']))
						//	->setTo(array($t_fr_email))
						//	->setBody($app['twig']->render('parts/emails/email_new_task.twig', array('author_email' => $_SESSION['user']['u_email'], 'new_task' => $t_name, 'name' => $_SESSION['user']['u_name'].$_SESSION['user']['u_surname'])),'text/html');
					//	$mailer->send($message);
					//}

					return "<div class=\"alert alert-dismissible alert-success\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						<strong>Поздравляем!</strong> Задача создана!<br>Название задачи: ".$t_name."<br>Для пользователя: ".$t_fr_email."!
						</div>";

				} elseif ($result_us['u_status'] == 0) { //пользователь не активирован (создание заачи без отправки сообщения)
					$sql = "INSERT INTO tasks (t_name, t_date_create, t_date_finish, t_type) VALUES (:t_name, :t_date_create, :t_date_finish, :t_type)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_type' => $t_type));

					$sql = "SELECT t_id FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create));
					$result_task = $sth->fetch(PDO::FETCH_ASSOC);

					$sql = "INSERT INTO users_tasks (u_id, t_id, ut_role) VALUES (:u_id_creat, :t_id, :ut_role_creat), (:u_id_exec, :t_id, :ut_role_exec)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':u_id_creat' => $_SESSION['user']['u_id'], ':t_id' => $result_task['t_id'], ':ut_role_creat' => 1, ':u_id_exec' => $result_us['u_id'], ':ut_role_exec' => 2));

							return "<div class=\"alert alert-dismissible alert-success\">
								<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
								<strong>Поздравляем!</strong> Задача создана!<br>Название задачи: ".$t_name."<br>Для пользователя: ".$t_fr_email."!
							</div>";

				}
			} else { //email в базе нет (добавление пользователя, создание задачи и отправка одного собщения сприглашением в проект)
				$sql = "INSERT INTO users (u_email) VALUES (:u_email)";
				$stm = $dbh->prepare($sql);
				$stm->execute(array(':u_email' => $t_fr_email));
							
				$sql = "SELECT u_id FROM users WHERE u_email = :u_email";
				$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':u_email' => $t_fr_email));
				$result_use = $sth->fetch(PDO::FETCH_ASSOC);

				$sql = "INSERT INTO tasks (t_name, t_date_create, t_date_finish, t_type) VALUES (:t_name, :t_date_create, :t_date_finish, :t_type)";
				$stm = $dbh->prepare($sql);
				$stm->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_type' => $t_type));

				$sql = "SELECT t_id FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
				$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create));
				$result_task = $sth->fetch(PDO::FETCH_ASSOC);

				$sql = "INSERT INTO users_tasks (u_id, t_id, ut_role) VALUES (:u_id_creat, :t_id, :ut_role_creat), (:u_id_exec, :t_id, :ut_role_exec)";
				$stm = $dbh->prepare($sql);
				$stm->execute(array(':u_id_creat' => $_SESSION['user']['u_id'], ':t_id' => $result_task['t_id'], ':ut_role_creat' => 1, ':u_id_exec' => $result_use['u_id'], ':ut_role_exec' => 2));

				//$message = \Swift_Message::newInstance()
				//	->setSubject('Приглашаем Вас в проект!')
				//	->setFrom(array($config['mail']['user']))
				//	->setTo(array($t_fr_email))
				//	->setBody($app['twig']->render('parts/emails/email_new_task_for_new_user.twig', array('author_email' => $_SESSION['user']['u_email'], 'new_task' => $t_name, 'site' => $_SERVER['HTTP_HOST'])),'text/html');
				//$mailer->send($message);

				return "<div class=\"alert alert-dismissible alert-success\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						<strong>Поздравляем!</strong> Задача создана!<br>Название задачи: ".$t_name."<br>Для пользователя: ".$t_fr_email."!
						</div>";	
			}
		} else {
			$p='P'.date("Y").'Y'.date("m").'M'.date("d").'DT'.date("H").'H'.date("i").'M'.date("s").'S';
			$time_waiting = new DateTime($result[4]['t_date_create']);
			$time_waiting->format('Y-m-d H:i:s');
			$time_waiting->modify('+1 day');
			$time_waiting->sub(new DateInterval($p));

			return "<div class=\"alert alert-dismissible alert-warning\">
					<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
					<strong>Внимание! </strong> Вы уже отправили 5 задач за сегодня! Следующий раз, Вы сможите отправить задачу через ".$time_waiting->format('H:i:s')."<br>Последние задачи, которые Вы составили:<br>".$result[0]['t_name']."<br>".$result[1]['t_name']."<br>".$result[2]['t_name']."<br>".$result[3]['t_name']."<br>".$result[4]['t_name']."
					</div>";	
		}
	}

	function email_settings($request, $result_task, $dbh) {
			$email_rule = ($request->get('email_rule') == 'undefined') ? 0 : $request->get('email_rule');
			$email_once = ($request->get('email_once') == 'undefined') ? 0 : $request->get('email_once');
			$email_frequency = ($request->get('email_frequency') == 'undefined') ? 0 : $request->get('email_frequency');
			$email_time = ($request->get('email_time') == 'undefined') ? 0 : $request->get('email_time');
			$email_week = ($request->get('email_week') == 'undefined') ? 0 : $request->get('email_week');
			$email_day = ($request->get('email_day') == 'undefined') ? 0 : $request->get('email_day');
			$email_month = ($request->get('email_month') == 'undefined') ? 0 : $request->get('email_month');

			if ($email_rule == 1) {
				if ($_SESSION['user']['u_tarif'] > 0) {
					$sql = "INSERT INTO task_email_remainder (t_id, ter_turn, ter_once, ter_frequency, ter_time, ter_day, ter_week, ter_month) VALUES (:t_id, :ter_turn, :ter_once, :ter_frequency, :ter_time, :ter_day, :ter_week, :ter_month)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':t_id' => $result_task['t_id'], ':ter_turn' => $email_rule, ':ter_once' => $email_once, ':ter_frequency' => $email_frequency, ':ter_time' => $email_time, ':ter_day' => $email_day, ':ter_week' => $email_week, ':ter_month' => $email_month));
				}
			}
		}

	function sms_settings($request, $result_task, $dbh) {
		$sms_rule = ($request->get('sms_rule') == 'undefined') ? 0 : $request->get('sms_rule');
		$sms_once = ($request->get('sms_once') == 'undefined') ? 0 : $request->get('sms_once');
		$sms_frequency = ($request->get('sms_frequency') == 'undefined') ? 0 : $request->get('sms_frequency');
		$sms_time = ($request->get('sms_time') == 'undefined') ? 0 : $request->get('sms_time');
		$sms_week = ($request->get('sms_week') == 'undefined') ? 0 : $request->get('sms_week');
		$sms_day = ($request->get('sms_day') == 'undefined') ? 0 : $request->get('sms_day');
		$sms_month = ($request->get('sms_month') == 'undefined') ? 0 : $request->get('sms_month');

		if ($sms_rule == 1) {
			if ($_SESSION['user']['u_tarif'] > 1) {
				$sql = "INSERT INTO task_sms_remainder (t_id, tsr_turn, tsr_once, tsr_frequency, tsr_time, tsr_day, tsr_week, tsr_month) VALUES (:t_id, :tsr_turn, :tsr_once, :tsr_frequency, :tsr_time, :tsr_day, :tsr_week, :tsr_month)";
				$stm = $dbh->prepare($sql);
				$stm->execute(array(':t_id' => $result_task['t_id'], ':tsr_turn' => $sms_rule, ':tsr_once' => $sms_once, ':tsr_frequency' => $sms_frequency, ':tsr_time' => $sms_time, ':tsr_day' => $sms_day, ':tsr_week' => $sms_week, ':tsr_month' => $sms_month));
			}
		}
	}

	function call_settings($request, $result_task, $dbh){
		$call_rule = ($request->get('call_rule') == 'undefined') ? 0 : $request->get('call_rule');
		$call_style = ($request->get('call_style') == 'undefined') ? 0 : $request->get('call_style');
		$call_once = ($request->get('call_once') == 'undefined') ? 0 : $request->get('call_once');
		$call_frequency = ($request->get('call_frequency') == 'undefined') ? 0 : $request->get('call_frequency');
		$call_time = ($request->get('call_time') == 'undefined') ? 0 : $request->get('call_time');
		$call_week = ($request->get('call_week') == 'undefined') ? 0 : $request->get('call_week');
		$call_day = ($request->get('call_day') == 'undefined') ? 0 : $request->get('call_day');
		$call_month = ($request->get('call_month') == 'undefined') ? 0 : $request->get('call_month');

		if ($call_rule == 1) {
			if ($_SESSION['user']['u_tarif'] > 2) {
				$sql = "INSERT INTO task_call_remainder (t_id, tcr_turn, tcr_style, tcr_once, tcr_frequency, tcr_time, tcr_day, tcr_week, tcr_month) VALUES (:t_id, :tcr_turn, :tcr_style, :tcr_once, :tcr_frequency, :tcr_time, :tcr_day, :tcr_week, :tcr_month)";
				$stm = $dbh->prepare($sql);
				$stm->execute(array(':t_id' => $result_task['t_id'], ':tcr_turn' => $call_rule, ':tcr_style' => $call_style, ':tcr_once' => $call_once, ':tcr_frequency' => $call_frequency, ':tcr_time' => $call_time, ':tcr_day' => $call_day, ':tcr_week' => $call_week, ':tcr_month' => $call_month));
			}
		}
	}
	
	function task_select_list($request) {
		global $dbh;

		$sql = "SELECT t_id, t_name FROM tasks WHERE t_type = 61 OR t_type = 62";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute();
		$result_tasks = $sth->fetchAll();
		
		$count_result_tasks = count($result_tasks);
			
		for ($stroka_v_massive = 0; $stroka_v_massive < $count_result_tasks; $stroka_v_massive++) {
			$result_new_tasks['number_' . $stroka_v_massive] = $result_tasks[$stroka_v_massive];
		}
		if(count(@$result_new_tasks)<1) $result_new_tasks = 1;

		$sql = "SELECT t_id, t_name FROM tasks WHERE t_type = 63";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute();
		$result_notes = $sth->fetchAll();
		
		$count_result_notes = count($result_notes);
			
		for ($stroka_v_massive = 0; $stroka_v_massive < $count_result_notes; $stroka_v_massive++) {
			$result_new_notes['number_' . $stroka_v_massive] = $result_notes[$stroka_v_massive];
		}
		if(count(@$result_new_notes)<1) $result_new_notes = 1;

		$sql = "SELECT t_id, t_name FROM tasks WHERE t_type = 4";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute();
		$result_projects = $sth->fetchAll();
		
		$count_result_projects = count($result_projects);
			
		for ($stroka_v_massive = 0; $stroka_v_massive < $count_result_projects; $stroka_v_massive++) {
			$result_new_projects['number_' . $stroka_v_massive] = $result_projects[$stroka_v_massive];
		}
		if(count(@$result_new_projects)<1) $result_new_projects = 1;

		$result = array('tasks' => @$result_new_tasks, 'notes' => @$result_new_notes, 'projects' => @$result_new_projects);
		return json_encode ($result);
	}
	

	function update_task_status($request) {
		global $dbh;
		$t_id = $request->get('number');
		$sql = "UPDATE tasks SET t_status = 0 WHERE t_id in (select t_id from users_tasks where t_id = :t_id and ut_role=2 and u_id=:u_id)";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_id' => $t_id, ':u_id' => $_SESSION['user']['u_id']));
		return 1;
	}

	function update_task_accept($request) {
		global $dbh;
		$t_id = $request->get('number');
		$sql = "UPDATE tasks SET t_accept_with_task = 1 WHERE t_id in (select t_id from users_tasks where t_id = :t_id and ut_role=2 and u_id=:u_id)";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_id' => $t_id, ':u_id' => $_SESSION['user']['u_id']));
		return 1;
	}

	function statistics_tasks ($request){
		global $dbh;
		$u_id = $request->get('id');
		
		$sql = "SELECT * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id=:u_id and ut_role=1)";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_id' => $u_id));
		$result_author_executer = $sth->fetch(PDO::FETCH_ASSOC);
		$count_result_author_executer = count($result_author_executer);

		$sql = "SELECT * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id=:u_id and ut_role=1) and t_status=1";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_id' => $u_id));
		$result_author_executer_active = $sth->fetch(PDO::FETCH_ASSOC);
		$count_result_author_executer_active = count($result_author_executer_active);



		$sql = "SELECT * from tasks left join users_tasks USING (t_id) where ut_role=1 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=2)";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_id' => $u_id));
		$result_author_noexecuter = $sth->fetch(PDO::FETCH_ASSOC);
		$count_result_author_noexecuter = count($result_author_noexecuter);

		$sql = "SELECT * from tasks left join users_tasks USING (t_id) where ut_role=1 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=2) and t_status=1";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_id' => $u_id));
		$result_author_noexecuter_active = $sth->fetch(PDO::FETCH_ASSOC);
		$count_result_author_noexecuter_active = count($result_author_noexecuter_active);

		$all_task_author = $count_result_author_executer + $count_result_author_noexecuter;

		$statistics['a_e']=$count_result_author_executer;
		$statistics['a_e_a']=$count_result_author_executer_active;
		$statistics['a_ne']=$count_result_author_noexecuter;
		$statistics['a_ne_a']=$count_result_author_noexecuter_active;
		$statistics['a_a']=$all_task_author;

		return json_encode($statistics); 
	}
