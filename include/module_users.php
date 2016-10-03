<?php

	function create_task ($app, $request) {
		$task_for = ($request->get('task_for') == 'undefined') ? 0 : $request->get('task_for');
		$task[] = $email_friend = ($request->get('email_friend') == 'undefined') ? NULL : $request->get('email_friend');
		$task[] = $task_name = ($request->get('task_name') == 'undefined') ? NULL : $request->get('task_name');
		$name_array =  preg_split("//u", $task_name);
		$count_name_array = count($name_array) - 2;
		if ($count_name_array < 21) {
			$task[] = $task_short_name = $task_name;
		} else {
			$task[] = $task_short_name = mb_substr($task_name, 0, 20, 'UTF-8').'..';
		}
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
		
		//return $task_for.'<br>'.$email_friend.'<br>'.$task_name.'<br>'.$task_short_name.'<br>'.$task_date_create.'<br>'.$task_date_finish.'<br>'.$task_type;

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
		$t_email_fr=$task[0];
		$t_name=$task[1];
		$t_short_name=$task[2];
		$t_type=$task[3];
		$t_stars=$task[4];
		$t_create=$task[5];
		$t_finish=$task[6];

		$sql = "INSERT INTO tasks (t_name, t_short_name, t_date_create, t_date_finish, t_raiting, t_type, t_accept_with_task) VALUES (:t_name, :t_short_name, :t_date_create, :t_date_finish, :t_raiting, :t_type, :t_accept_with_task)";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':t_name' => $t_name, ':t_short_name' => $t_short_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_raiting' => $t_stars, ':t_type' => $t_type, ':t_accept_with_task' => 1));

		$sql = "SELECT * FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create));
		$result_task = $sth->fetch(PDO::FETCH_ASSOC);

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
		$t_email_fr=$task[0];
		$t_name=$task[1];
		$t_short_name=$task[2];
		$t_type=$task[3];
		$t_stars=$task[4];
		$t_create=$task[5];
		$t_finish=$task[6];

		$sql = "INSERT INTO tasks (t_name, t_short_name, t_date_create, t_date_finish, t_raiting, t_type, t_accept_with_task) VALUES (:t_name, :t_short_name, :t_date_create, :t_date_finish, :t_raiting, :t_type, :t_accept_with_task)";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':t_name' => $t_name, ':t_short_name' => $t_short_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_raiting' => $t_stars, ':t_type' => $t_type, ':t_accept_with_task' => 1));

		$sql = "SELECT t_id FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
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
		$t_email_fr=$task[0];
		$t_name=$task[1];
		$t_short_name=$task[2];
		$t_type=$task[3];
		$t_stars=$task[4];
		$t_create=$task[5];
		$t_finish=$task[6];

		if(empty($t_email_fr)){
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
			$sth->execute(array(':email_friend' => $t_email_fr));
			$result_us = $sth->fetch(PDO::FETCH_ASSOC);

			if ($result_us == TRUE) { //email есть

				if ($result_us['u_status'] == 1) { //пользователь активирован (создание задачи и отправка сообщения о новой задачи)
					$sql = "INSERT INTO tasks (t_name, t_short_name, t_date_create, t_date_finish, t_raiting, t_type) VALUES (:t_name, :t_short_name, :t_date_create, :t_date_finish, :t_raiting, :t_type)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':t_name' => $t_name, ':t_short_name' => $t_short_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_raiting' => $t_stars, ':t_type' => $t_type));

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
						//	->setTo(array($t_email_fr))
						//	->setBody($app['twig']->render('parts/emails/email_new_task.twig', array('author_email' => $_SESSION['user']['u_email'], 'new_task' => $t_name, 'name' => $_SESSION['user']['u_name'].$_SESSION['user']['u_surname'])),'text/html');
					//	$mailer->send($message);
					//}

					return "<div class=\"alert alert-dismissible alert-success\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						<strong>Поздравляем!</strong> Задача создана!<br>Название задачи: ".$t_name."<br>Для пользователя: ".$t_email_fr."!
						</div>";

				} elseif ($result_us['u_status'] == 0) { //пользователь не активирован (создание заачи без отправки сообщения)
					$sql = "INSERT INTO tasks (t_name, t_short_name, t_date_create, t_date_finish, t_raiting, t_type) VALUES (:t_name, :t_short_name, :t_date_create, :t_date_finish, :t_raiting, :t_type)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':t_name' => $t_name, ':t_short_name' => $t_short_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_raiting' => $t_stars, ':t_type' => $t_type));

					$sql = "SELECT t_id FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create));
					$result_task = $sth->fetch(PDO::FETCH_ASSOC);

					$sql = "INSERT INTO users_tasks (u_id, t_id, ut_role) VALUES (:u_id_creat, :t_id, :ut_role_creat), (:u_id_exec, :t_id, :ut_role_exec)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':u_id_creat' => $_SESSION['user']['u_id'], ':t_id' => $result_task['t_id'], ':ut_role_creat' => 1, ':u_id_exec' => $result_us['u_id'], ':ut_role_exec' => 2));

							return "<div class=\"alert alert-dismissible alert-success\">
								<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
								<strong>Поздравляем!</strong> Задача создана!<br>Название задачи: ".$t_name."<br>Для пользователя: ".$t_email_fr."!
							</div>";

				}
			} else { //email в базе нет (добавление пользователя, создание задачи и отправка одного собщения сприглашением в проект)
				$sql = "INSERT INTO users (u_email) VALUES (:u_email)";
				$stm = $dbh->prepare($sql);
				$stm->execute(array(':u_email' => $t_email_fr));
							
				$sql = "SELECT u_id FROM users WHERE u_email = :u_email";
				$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':u_email' => $t_email_fr));
				$result_use = $sth->fetch(PDO::FETCH_ASSOC);

				$sql = "INSERT INTO tasks (t_name, t_short_name, t_date_create, t_date_finish, t_raiting, t_type) VALUES (:t_name, :t_short_name, :t_date_create, :t_date_finish, :t_raiting, :t_type)";
				$stm = $dbh->prepare($sql);
				$stm->execute(array(':t_name' => $t_name, ':t_short_name' => $t_short_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_raiting' => $t_stars, ':t_type' => $t_type));

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
				//	->setTo(array($t_email_fr))
				//	->setBody($app['twig']->render('parts/emails/email_new_task_for_new_user.twig', array('author_email' => $_SESSION['user']['u_email'], 'new_task' => $t_name, 'site' => $_SERVER['HTTP_HOST'])),'text/html');
				//$mailer->send($message);

				return "<div class=\"alert alert-dismissible alert-success\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						<strong>Поздравляем!</strong> Задача создана!<br>Название задачи: ".$t_name."<br>Для пользователя: ".$t_email_fr."!
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
	
	function task_list_right($request) {
		global $dbh;

		$sql = "SELECT t_id, t_short_name FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE ut_role=2 AND u_id=:u_id AND t_raiting != 0 AND t_type != 4 AND t_accept_with_task = 1 AND t_status = 1 AND t_archive = 0 AND t_cancel = 0 AND t_delete = 0 ORDER BY t_raiting DESC, t_date_create DESC LIMIT 5";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_id' => $_SESSION['user']['u_id']));
		$important_tasks = $sth->fetchAll();
		
		if (count($important_tasks) > 0) {
			$count_important_tasks = count($important_tasks);
			
			for ($stroka_v_massive = 0; $stroka_v_massive < $count_important_tasks; $stroka_v_massive++) {
				$important_new_tasks['number_' . $stroka_v_massive] = $important_tasks[$stroka_v_massive];
			}
		} else {
			$important_new_tasks = 1;
		}


		$sql = "SELECT t_id, t_short_name FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE u_id=:u_id AND t_delete = 0 GROUP BY t_id ORDER BY t_date_create DESC LIMIT 5";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_id' => $_SESSION['user']['u_id']));
		$last_tasks = $sth->fetchAll();
		
		if (count($last_tasks) > 0) {
			$count_last_tasks = count($last_tasks);
			
			for ($stroka_v_massive = 0; $stroka_v_massive < $count_last_tasks; $stroka_v_massive++) {
				$last_new_tasks['number_' . $stroka_v_massive] = $last_tasks[$stroka_v_massive];
			}
		} else {
			$last_new_tasks = 1;
		}


		$sql = "SELECT t_id, t_short_name FROM tasks LEFT JOIN tasks_favourite USING (t_id) WHERE u_id = :u_id AND t_type != 4 AND t_delete = 0 ORDER BY tf_date DESC LIMIT 15";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_id' => $_SESSION['user']['u_id']));
		$favourite_tasks = $sth->fetchAll();
		
		if (count($favourite_tasks) > 0) {
			$count_favourite_tasks = count($favourite_tasks);
			
			for ($stroka_v_massive = 0; $stroka_v_massive < $count_favourite_tasks; $stroka_v_massive++) {
				$favourite_new_tasks['number_' . $stroka_v_massive] = $favourite_tasks[$stroka_v_massive];
			}
		} else {
			$favourite_new_tasks = 1;
		}


		$sql = "SELECT t_id, t_short_name FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE ut_role = 2 AND u_id = :u_id AND t_type != 4 AND t_accept_with_task = 0 AND t_archive = 0 AND t_cancel = 0 AND t_delete = 0 AND t_id IN (SELECT t_id FROM users_tasks WHERE u_id != :u_id AND ut_role = 1) ORDER BY t_date_create DESC LIMIT 5";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_id' => $_SESSION['user']['u_id']));
		$new_tasks = $sth->fetchAll();
		
		if (count($new_tasks) > 0) {
			$count_new_tasks = count($new_tasks);
			
			for ($stroka_v_massive = 0; $stroka_v_massive < $count_new_tasks; $stroka_v_massive++) {
				$new_new_tasks['number_' . $stroka_v_massive] = $new_tasks[$stroka_v_massive];
			}
		} else {
			$new_new_tasks = 1;
		}

		$result = array('imp' => $important_new_tasks, 'last' => $last_new_tasks, 'fav' => $favourite_new_tasks, 'new' => $new_new_tasks);
		return json_encode ($result);
	}
		

	function task_list_content($request) {
		global $config;
		$lim = $config['settings']['count_task_on_one_page'];	//count of product on one page
		$page = $request->get('page');
		$task_type = $request->get('task_t');
		$task_number = $request->get('task_n');
		if (isset($task_number)) exit;
		if (!isset($task_type)) $task_type='imp';
		if ($task_type == 'imp') {  //important task
			$sql_all = "SELECT t_id FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE ut_role=2 AND u_id=:u_id AND t_raiting != 0 AND t_type != 4 AND t_accept_with_task = 1 AND t_status = 1 AND t_archive = 0 AND t_cancel = 0 AND t_delete = 0";
			$sql_special = "SELECT t_short_name, t_id, t_type, t_raiting FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE ut_role = 2 AND u_id = ? AND u_id = ? AND t_raiting != 0 AND t_type != 4 AND t_accept_with_task = 1 AND t_status = 1 AND t_archive = 0 AND t_cancel = 0 AND t_delete = 0 ORDER BY t_raiting DESC, t_date_create DESC LIMIT ?,?";			
		} elseif ($task_type == 'last') { //last task
			$sql_all = "SELECT t_id FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE u_id=:u_id AND t_delete = 0 GROUP BY t_id";
			$sql_special = "SELECT t_short_name, t_id, t_type, t_raiting FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE u_id = ? AND u_id = ? AND t_delete = 0 GROUP BY t_id ORDER BY t_date_create DESC LIMIT ?,?";	
		} elseif ($task_type == 'fav') { //favourite task
			$sql_all = "SELECT t_id FROM tasks LEFT JOIN tasks_favourite USING (t_id) WHERE u_id = :u_id AND t_type != 4 AND t_delete = 0";
			$sql_special = "SELECT t_short_name, t_id, t_type, t_raiting FROM tasks LEFT JOIN tasks_favourite USING (t_id) WHERE u_id = ? AND u_id = ? AND t_type != 4 AND t_delete = 0 ORDER BY tf_date DESC LIMIT ?,?";
		} elseif ($task_type == 'new') { //favourite task
			$sql_all = "SELECT t_id FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE ut_role = 2 AND u_id = :u_id AND t_type != 4 AND t_accept_with_task = 0 AND t_archive = 0 AND t_cancel = 0 AND t_delete = 0 AND t_id IN (SELECT t_id FROM users_tasks WHERE u_id != :u_id AND ut_role = 1)";
			$sql_special = "SELECT t_short_name, t_id, t_type, t_raiting FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE ut_role = 2 AND u_id = ? AND t_type != 4 AND t_accept_with_task = 0 AND t_archive = 0 AND t_cancel = 0 AND t_delete = 0 AND t_id IN (SELECT t_id FROM users_tasks WHERE u_id != ? AND ut_role = 1) ORDER BY t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'proj') { //projects
			$sql_all = "SELECT t_id FROM tasks LEFT JOIN groups USING (t_id) WHERE u_id = :u_id AND t_delete = 0 GROUP BY t_id";
			$sql_special = "SELECT t_short_name, t_id, t_type, t_raiting FROM tasks LEFT JOIN groups USING (t_id) WHERE u_id = ? AND u_id = ? AND t_delete = 0 GROUP BY t_id ORDER BY t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'even') { //all events
			$sql_all = "SELECT t_id FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE u_id = :u_id AND t_delete = 0 AND t_type = 61 GROUP BY t_id";
			$sql_special = "SELECT t_short_name, t_id, t_type, t_raiting FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE u_id = ? AND u_id = ? AND t_delete = 0 AND t_type = 61 GROUP BY t_id ORDER BY t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'even_fm') { //all events for me
			//$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=1)";
			//$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=? and ut_role=1) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'even_fmbm') { //events for me by me
			//$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=1)";
			//$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=? and ut_role=1) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'even_fmba') { //events for me by another man
			//$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=1)";
			//$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=? and ut_role=1) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'even_fabm') { //events for another by me
			//$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=1)";
			//$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=? and ut_role=1) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'task') { //all tasks
			$sql_all = "SELECT t_id FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE u_id = :u_id AND t_delete = 0 AND t_type = 62 GROUP BY t_id";
			$sql_special = "SELECT t_short_name, t_id, t_type, t_raiting FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE u_id = ? AND u_id = ? AND t_delete = 0 AND t_type = 62 GROUP BY t_id ORDER BY t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'task_fm') { //all tasks for me
			//$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=1)";
			//$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=? and ut_role=1) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'task_fmbm') { //tasks for me by me
			//$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=1)";
			//$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=? and ut_role=1) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'task_fmba') { //tasks for me by another man
			//$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=1)";
			//$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=? and ut_role=1) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";
		} elseif ($task_type == 'task_fabm') { //tasks for another by me
			//$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=1)";
			//$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_accept_with_task=0 and t_id in (select t_id from users_tasks where u_id!=? and ut_role=1) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";	
		} else {
			$result['number_0']['t_name'] = 'Ошибка!';
			return json_encode ($result);
		}

		global $dbh;
		$sth = $dbh->prepare($sql_all, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_id' => $_SESSION['user']['u_id']));
		$result_simple = $sth->fetchAll();
		$count_result = count($result_simple);
			
		if (isset($page) && $page > 0) {
			$number_page = $page;
		} else {
			$number_page = 1;
		}
		$start = ($number_page - 1) * $lim;
			
		$stm = $dbh->prepare($sql_special);		
		$stm->bindValue(1, $_SESSION['user']['u_id'], PDO::PARAM_INT);	
		$stm->bindValue(2, $_SESSION['user']['u_id'], PDO::PARAM_INT);		
		$stm->bindValue(3, $start, PDO::PARAM_INT);
		$stm->bindValue(4, $lim, PDO::PARAM_INT);
		$stm->execute();
		$result_special = $stm->fetchAll();
		$count_result_special = count($result_special);
			
		for ($stroka_v_massive = 0; $stroka_v_massive < $count_result_special; $stroka_v_massive++) {
			$result_new['number_' . $stroka_v_massive] = $result_special[$stroka_v_massive];
		}
		$result['count_all'] = $count_result;
		$result['lim'] = $lim;
		$result['task_type'] = $task_type;
		$result['content'] = $result_new;
		return json_encode ($result);	
	}

	function task_information_render($request){
		global $dbh;
		global $config;
		$task_type = $request->get('task_t');
		$task_number = $request->get('task_n');
		if (isset($task_type)) exit;
		$sql = "SELECT t_type FROM tasks WHERE t_delete = 0 AND t_id = :t_id";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':t_id' => $task_number));
		$type = $stm->fetchAll();
		if ($type[0]['t_type'] == 4) {
			$page = $request->get('page');
			$lim = $config['settings']['count_task_under_project'];
			$sql = "SELECT * FROM tasks LEFT JOIN groups USING (t_id) WHERE t_delete = 0 AND t_id = :t_id AND u_id = :u_id GROUP BY t_id";
			$stm = $dbh->prepare($sql);
			$stm->execute(array(':t_id' => $task_number, ':u_id' => $_SESSION['user']['u_id']));
			$information = $stm->fetchAll();
			$inf_count = count($information);
			for ($stroka_v_massive = 0; $stroka_v_massive < $inf_count; $stroka_v_massive++) {
				$pr['project'] = $information[$stroka_v_massive];
			}
			$sql_all = "SELECT t_id, t_short_name FROM tasks WHERE t_type != 4 AND t_delete = 0 AND t_parent = :t_parent";
			$sql_special = "SELECT t_id, t_short_name FROM tasks WHERE t_type != 4 AND t_delete = 0 AND t_parent = ? ORDER BY t_date_create DESC LIMIT ?,?";

			$sth = $dbh->prepare($sql_all, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

			$sth->execute(array(':t_parent' => $task_number));
			$result_simple = $sth->fetchAll();
			$count_result = count($result_simple);
				
			if (isset($page) && $page > 0) {
				$number_page = $page;
			} else {
				$number_page = 1;
			}
			$start = ($number_page - 1) * $lim;
				
			$stm = $dbh->prepare($sql_special);		
			$stm->bindValue(1, $task_number, PDO::PARAM_INT);			
			$stm->bindValue(2, $start, PDO::PARAM_INT);
			$stm->bindValue(3, $lim, PDO::PARAM_INT);
			$stm->execute();
			$result_special = $stm->fetchAll();
			$count_result_special = count($result_special);
				
			for ($stroka_v_massive = 0; $stroka_v_massive < $count_result_special; $stroka_v_massive++) {
				$result_new['number_' . $stroka_v_massive] = $result_special[$stroka_v_massive];
			}

			$inf_new['project'] = $pr['project'];
			$inf_new['count_all'] = $count_result;
			$inf_new['lim'] = $lim;
			$inf_new['content'] = @$result_new;
			$inf_new['type'] = $type[0]['t_type'];
			$inf_new['id'] = $task_number;
		} else {
			$sql = "SELECT * FROM tasks LEFT JOIN users_tasks USING (t_id) WHERE t_delete = 0 AND t_id = :t_id AND u_id = :u_id GROUP BY t_id";
			$stm = $dbh->prepare($sql);
			$stm->execute(array(':t_id' => $task_number, ':u_id' => $_SESSION['user']['u_id']));
			$information = $stm->fetchAll();
			$inf_count = count($information);
			for ($stroka_v_massive = 0; $stroka_v_massive < $inf_count; $stroka_v_massive++) {
				$inf_new['task'] = $information[$stroka_v_massive];
			}
			$inf_new['type'] = $information[0]['t_type'];
		}
		return json_encode($inf_new);
	}

	function update_task_status($request) {
		global $dbh;
		$t_id = $request->get('task_n');
		$sql = "UPDATE tasks SET t_status = 0 WHERE t_id in (select t_id from users_tasks where t_id = :t_id and ut_role=2 and u_id=:u_id)";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_id' => $t_id, ':u_id' => $_SESSION['user']['u_id']));
		return "<div class=\"alert alert-dismissible alert-success\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						<strong>Ура! </strong> Поздравляем с выполнением задачи.
					</div>";
	}

	function update_task_accept($request) {
		global $dbh;
		$t_id = $request->get('task_n');
		$sql = "UPDATE tasks SET t_accept_with_task = 1 WHERE t_id in (select t_id from users_tasks where t_id = :t_id and ut_role=2 and u_id=:u_id)";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_id' => $t_id, ':u_id' => $_SESSION['user']['u_id']));
		exit;
	}

	function update_task_raiting($request) {
		global $dbh;
		$t_id = $request->get('task_n');
		$t_raiting = $request->get('raiting');
		$sql = "UPDATE tasks SET t_raiting = :t_raiting WHERE t_id in (select t_id from users_tasks where t_id = :t_id and ut_role=2 and u_id=:u_id)";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_raiting' => $t_raiting, ':t_id' => $t_id, ':u_id' => $_SESSION['user']['u_id']));
		exit;
	}

	function update_task_archive($request) {
		global $dbh;
		$t_id = $request->get('task_n');
		$sql = "UPDATE tasks SET t_archive = 1 WHERE t_id in (select t_id from users_tasks where t_id = :t_id and ut_role=2 and u_id=:u_id)";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_id' => $t_id, ':u_id' => $_SESSION['user']['u_id']));
		return "<div class=\"alert alert-dismissible alert-info\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						Задача успешно добавлена в архив.
					</div>";
	}
	
	function update_task_cancel($request) {
		global $dbh;
		$t_id = $request->get('task_n');
		$sql = "UPDATE tasks SET t_cancel = 1 WHERE t_id in (select t_id from users_tasks where t_id = :t_id and ut_role=2 and u_id=:u_id)";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_id' => $t_id, ':u_id' => $_SESSION['user']['u_id']));
		return "<div class=\"alert alert-dismissible alert-info\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						Задача успешно отменена.
					</div>";
	}
	
	function update_task_delete($request) {
		global $dbh;
		$t_id = $request->get('task_n');
		$sql = "UPDATE tasks SET t_delete = 1 WHERE t_id in (select t_id from users_tasks where t_id = :t_id and ut_role=2 and u_id=:u_id)";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_id' => $t_id, ':u_id' => $_SESSION['user']['u_id']));
		return "<div class=\"alert alert-dismissible alert-danger\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						Задача успешно удалена.
					</div>";
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
