<?php

function app_authorization ($request){
	$user_email = $request->get('user_email');
	$user_password = $request->get('user_password');

	if (email_valid($user_email) !== TRUE) {
		return array('status' => 34);
	}

	if (password_valid($user_password) !== TRUE) {
		return array('status' => 34);
	}

	global $dbh;

	$sql = "SELECT * FROM users left join user_settings USING (u_id) WHERE u_email = :user_email";
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->execute(array(':user_email' => $user_email));
	$result = $sth->fetch(PDO::FETCH_ASSOC);
	if ($result == false){
		return array('status' => 33);
	} else {
		if (password_verify($user_password, $result['u_password'])) {
			
				$cookie_auth= mt_rand() . $user_password;
				$auth_key = md5($cookie_auth);

				$sql = "INSERT INTO session_authorization (u_id, sa_auth_key, sa_browser, sa_ip, sa_date_last_active) VALUES (:u_id, :sa_auth_key, :sa_browser, :sa_ip, :sa_date_last_active)";
				$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':u_id' => $result['u_id'], ':sa_auth_key' => $auth_key, ':sa_browser' => $_SERVER['HTTP_USER_AGENT'], ':sa_ip' => $_SERVER['REMOTE_ADDR'], ':sa_date_last_active' => date("Y-m-d H:i:s")));
				unset($result['u_password']);
				unset($result['u_activation']);
				return array('status' => 11, 'result' => $result, 'auth_key' => $auth_key);
		} else {
			return array('status' => 33);
		}
	}
}

function app_upload_task ($request) {
	global $dbh;
	$key_auth = $request->get('key_auth');
	$sql = "SELECT t_id, t_name, t_short_name, t_description, t_date_create, t_date_finish FROM tasks left join users_tasks USING (t_id) left join session_authorization USING (u_id) WHERE sa_auth_key = :key_auth AND ut_role = 2 and t_parent = 0 /*and t_id in (select t_id from users_tasks where sa_auth_key = :key_auth and ut_role=1) */ORDER BY t_date_create DESC";
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->execute(array(':key_auth' => $key_auth));
	$result = $sth->fetchAll();
	return array('status' => 11, 'result' => $result);
}

function app_import_task($request) {

	$task_for = ($request->get('task_for') == 'undefined') ? 0 : $request->get('task_for');
	if($task_for == 0) {
		return array('status' => 34);
	}
	$task[] = $email_friend = ($request->get('email_friend') == 'undefined') ? NULL : $request->get('email_friend');
	$task[] = $task_name = ($request->get('task_name') == 'undefined') ? NULL : $request->get('task_name');
	if($task_name == NULL) {
		return array('status' => 34);
	}
	$name_array =  preg_split("//u", $task_name);
	$count_name_array = count($name_array) - 2;
	if ($count_name_array < 21) {
		$task[] = $task_short_name = $task_name;
	} else {
		$task[] = $task_short_name = mb_substr($task_name, 0, 20, 'UTF-8').'..';
	}
	$task_deadline_turn = ($request->get('task_deadline_turn') == 'undefined') ? 0 : $request->get('task_deadline_turn');
	if($task_deadline_turn == 0) {
		return array('status' => 34);
	}
	$task_deadline_year = ($request->get('task_deadline_year') == 'undefined') ? 0 : $request->get('task_deadline_year');
	$task_deadline_month = ($request->get('task_deadline_month') == 'undefined') ? 0 : $request->get('task_deadline_month');
	$task_deadline_day = ($request->get('task_deadline_day') == 'undefined') ? 0 : $request->get('task_deadline_day');
	$task_deadline_hour = ($request->get('task_deadline_hour') == 'undefined') ? 0 : $request->get('task_deadline_hour');
	$task[] = $task_type = ($request->get('task_type') == 'undefined') ? 0 : $request->get('task_type');
	$task[] = $task_stars = 0;
	$task[] = $task_date_create = date("Y-m-d H:i:s");
	$task[] = $task_date_finish = ($task_deadline_turn == 0) ? 'NULL' : $task_deadline_year.'-'.$task_deadline_month.'-'.$task_deadline_day.' '.$task_deadline_hour.':00:00';
	$task[] = $key_auth = ($request->get('key_auth') == 'undefined') ? NULL : $request->get('key_auth');
	if($key_auth == NULL) {
		return array('status' => 34);
	}
	
		global $dbh;
		global $config;
		global $mailer;

	if ($task_for == 1) {
		$answer = app_create_personal_task($task, $dbh);
	} elseif($task_for == 2) {
		$answer = app_create_task_for_another($task, $dbh, $config, $mailer);
	}

		return $answer;
}

function app_create_personal_task($task, $dbh){
		//for me
		$t_email_fr=$task[0];
		$t_name=$task[1];
		$t_short_name=$task[2];
		$t_type=$task[3];
		$t_stars=$task[4];
		$t_create=$task[5];
		$t_finish=$task[6];
		$t_key_auth=$task[7];

		$sql = "INSERT INTO tasks (t_name, t_short_name, t_date_create, t_date_finish, t_raiting, t_type, t_accept_with_task) VALUES (:t_name, :t_short_name, :t_date_create, :t_date_finish, :t_raiting, :t_type, :t_accept_with_task)";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':t_name' => $t_name, ':t_short_name' => $t_short_name, ':t_date_create' => $t_create, ':t_date_finish' => $t_finish, ':t_raiting' => $t_stars, ':t_type' => $t_type, ':t_accept_with_task' => 1));

		$sql = "SELECT t_id FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':t_name' => $t_name, ':t_date_create' => $t_create));
		$result_task = $sth->fetch(PDO::FETCH_ASSOC);

		$sql = "SELECT u_id FROM session_authorization WHERE sa_auth_key = :sa_auth_key";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array('sa_auth_key' => $t_key_auth));
		$result_user_author = $sth->fetch(PDO::FETCH_ASSOC);
		if($result_user_author == false) {
			return array('status' => 33);
		}

		$sql = "INSERT INTO users_tasks (u_id, t_id, ut_role) VALUES (:u_id_creat, :t_id, :ut_role_creat), (:u_id_exec, :t_id, :ut_role_exec)";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':u_id_creat' => $result_user_author['u_id'], ':t_id' => $result_task['t_id'], ':ut_role_creat' => 1, ':u_id_exec' => $result_user_author['u_id'], ':ut_role_exec' => 2));

		return array('status' => 11);
	}

function app_create_task_for_another($task, $dbh, $config, $mailer){
		 //for another man
		$t_email_fr=$task[0];
		$t_name=$task[1];
		$t_short_name=$task[2];
		$t_type=$task[3];
		$t_stars=$task[4];
		$t_create=$task[5];
		$t_finish=$task[6];
		$t_key_auth=$task[7];

		if($t_email_fr == NULL) {
			return array('status' => 34);
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