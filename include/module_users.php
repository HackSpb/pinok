<?php

	function send_task ($app, $request) {
		$email_friend = $request->get('email_friend');
		$task = $request->get('task');
		$code = $request->get('code');
		$description = $request->get('description');
		$deadline = $request->get('deadline');
		$t_date_create = date("Y-m-d H:i:s");
		if ($code == $_SESSION['code_for_send_task']) {
			global $dbh;
			$sql = "select * from tasks left join users_tasks USING (t_id) where ut_role=1 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=2) and t_date_create > NOW() - INTERVAL 1 DAY";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':u_id' => $_SESSION['user']['u_id']));
			$result = $sth->fetchAll();
			$count_result_today = count($result);
			if ($count_result_today<=5) {
				$sql = "SELECT * FROM users WHERE u_email = :email_friend";
				$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':email_friend' => $email_friend));
				$result_us = $sth->fetch(PDO::FETCH_ASSOC);
				if ($result == TRUE) {
					$sql = "INSERT INTO tasks (t_name, t_date_create, t_description, t_date_finish) VALUES (:t_name, :t_date_create, :t_description, :t_date_finish)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':t_name' => $task, ':t_date_create' => $t_date_create, ':t_description' => $description, ':t_date_finish' => $deadline));

					$sql = "SELECT * FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':t_name' => $task, ':t_date_create' => $t_date_create));
					$result_task = $sth->fetch(PDO::FETCH_ASSOC);

					$sql = "INSERT INTO users_tasks (u_id, t_id, ut_role) VALUES (:u_id_creat, :t_id, :ut_role_creat), (:u_id_exec, :t_id, :ut_role_exec)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':u_id_creat' => $_SESSION['user']['u_id'], ':t_id' => $result_task['t_id'], ':ut_role_creat' => 1, ':u_id_exec' => $result_us['u_id'], ':ut_role_exec' => 2));
					unset($_SESSION['code_for_send_task']);
					return "<div class=\"alert alert-dismissible alert-success\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						<strong>Поздравляем! </strong> Задача создана!<br>Название задачи:".$task."<br>Для пользователя:".$email_friend."!
					</div>";
				}else {
					$sql = "INSERT INTO tasks (t_name, t_date_create, t_description, t_date_finish) VALUES (:t_name, :t_date_create, :t_description, :t_date_finish)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':t_name' => $task, ':t_date_create' => $t_date_create, ':t_description' => $description, ':t_date_finish' => $deadline));
					
					$sql = "SELECT * FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':t_name' => $task, ':t_date_create' => $t_date_create));
					$result_task = $sth->fetch(PDO::FETCH_ASSOC);
					
					$sql = "INSERT INTO users (t_email) VALUES (:t_email)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':t_email' => $email_friend));
					
					$sql = "SELECT * FROM users WHERE t_email = :t_email";
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':t_email' => $email_friend));
					$result_user = $sth->fetch(PDO::FETCH_ASSOC);
					
					$sql = "INSERT INTO users_tasks (u_id, t_id, ut_role) VALUES (:u_id_creat, :t_id, :ut_role_creat), (:u_id_exec, :t_id, :ut_role_exec)";
					$stm = $dbh->prepare($sql);
					$stm->execute(array(':u_id_creat' => $_SESSION['user']['u_id'], ':t_id' => $result_task[0]['t_id'], ':ut_role_creat' => 1, ':u_id_exec' => $result_user[0]['u_id'], ':ut_role_exec' => 2));
					global $mailer;
					$message = \Swift_Message::newInstance()
						->setSubject('Для Вас есть новые задачи'.$email_friend)
						->setFrom(array('bramin90@gmail.com'))
						->setTo(array($email_friend))
						->setBody($app['twig']->render('parts/emails/email_new_task_for_new_user.twig', array('author_email' => $_SESSION['user']['u_email'], 'new_task' => $task, 'site' => $_SERVER['HTTP_HOST'])),'text/html');
					$mailer->send($message);
					unset($_SESSION['code_for_send_task']);
					return "<div class=\"alert alert-dismissible alert-success\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						<strong>Поздравляем! </strong> Задача создана!<br>Название задачи:".$task."<br>Для пользователя:".$email_friend."!
					</div>";				
				}			
			}else {
				$time_waiting = new DateTime($result[4]['t_date_create']);
				$time_waiting->add(new DateInterval('P1D'));
				$time_waiting->sub(new DateInterval($t_date_create));
				return "<div class=\"alert alert-dismissible alert-warning\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						<strong>Внимание! </strong> Вы уже отправили 5 задач за сегодня! Следующий раз, Вы сможите отправить задачу через ".$time_waiting->format('Y-m-d H:i:s')."<br>Последние задачи, которые Вы составили:<br>".$result[0]['t_name']."<br>".$result[1]['t_name']."<br>".$result[2]['t_name']."<br>".$result[3]['t_name']."<br>".$result[4]['t_name']."
					</div>";		
			}
		} else {
			return "<div class=\"alert alert-dismissible alert-warning\">
						<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
						<strong>Внимание! </strong> Код не совпадает!
					</div>";
		}
	}

function task_select ($request) {
	global $config;
	$lim = $config['settings']['count_task_on_one_page'];	//count of product on one page
	$page = $request->get('page');
	$task_type = $request->get('task');
	if (!isset($task_type)) $task_type='own_all';
	if ($task_type == 'own_all') {
		$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id";
		$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and u_id=? ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";			
	} elseif ($task_type == 'own') {
		$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id=:u_id and ut_role=1)";
		$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_id in (select t_id from users_tasks where u_id=? and ut_role=1) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";	
	} elseif ($task_type == 'other') {
		$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=1)";
		$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_id in (select t_id from users_tasks where u_id!=? and ut_role=1) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";	
	} elseif ($task_type == 'from_others') {
		$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=1 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=2)";
		$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=1 and u_id=? and t_id in (select t_id from users_tasks where u_id!=? and ut_role=2) ORDER BY t_status DESC, t_date_create DESC LIMIT ?,?";	
	} else {
		$result['number_0']['t_name'] = 'Ошибка!';
		return json_encode ($result);
		exit();
	}
	global $dbh;
	$sth = $dbh->prepare($sql_all, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->execute(array(':u_id' => $_SESSION['user']['u_id']));
	$result = $sth->fetchAll();
	$count_result = count($result);
		
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

	$result_new['count_all'] = $count_result;
	$result_new['lim'] = $lim;
	$result_new['task_type'] = $task_type;
	return json_encode ($result_new);		
}

	
function task_add_new($request){
	global $dbh;
	$task_name = $request->get('task_name');
	$task_description = $request->get('task_description');
	$task_date_finish = $request->get('task_date_finish');
	$task_date_create = date("Y-m-d H:i:s");

	if (text_valid($task_name) !== TRUE) {
		return "<div class=\"alert alert-dismissible alert-warning\">
				<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
				<strong>Ошибка! </strong> В названии указан недопустимый символ!
			</div>";
	}
	if (text_valid($task_description) !== TRUE) {
		return "<div class=\"alert alert-dismissible alert-warning\">
				<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
				<strong>Ошибка! </strong> В описании указан недопустимый символ!
			</div>";
	}

	$sql = "INSERT INTO tasks (t_name, t_description, t_type, t_date_create, t_date_finish) VALUES (:t_name, :t_description, :t_type, :t_date_create, :t_date_finish)";
	$stm = $dbh->prepare($sql);
	$stm->execute(array(':t_name' => $task_name, ':t_description' => $task_description, ':t_type' => $task_type, ':t_date_create' => $task_date_create, ':t_date_finish' => $task_date_finish));

	$sql = "SELECT * FROM tasks WHERE t_name = :t_name AND t_date_create = :t_date_create";
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->execute(array(':t_name' => $task_name, ':t_date_create' => $task_date_create));
	$result_task = $sth->fetch(PDO::FETCH_ASSOC);

	$sql = "INSERT INTO users_tasks (u_id, t_id, ut_role) VALUES (:u_id_creat, :t_id, :ut_role_creat), (:u_id_exec, :t_id, :ut_role_exec)";
	$stm = $dbh->prepare($sql);
	$write_a_task = $stm->execute(array(':u_id_creat' => $_SESSION['user']['u_id'], ':t_id' => $result_task['t_id'], ':ut_role_creat' => 1, ':u_id_exec' => $_SESSION['user']['u_id'], ':ut_role_exec' => 2));

	return "<div class=\"alert alert-dismissible alert-success\">
				<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
				<strong>Замечательно! </strong> Задача " . $task_name . " успешно создана!
			</div>";
			
}
		

	function update_task_status($request) {
		global $dbh;
		$t_id = $request->get('number');
		$sql = "UPDATE tasks SET t_status = 0 WHERE t_id in (select t_id from users_tasks where t_id = :t_id and ut_role=2 and u_id=:u_id)";
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
