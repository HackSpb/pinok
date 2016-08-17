<?php	

function registration ($request, $app){
	$reg_email = $request->get('reg_email');
	$reg_password = $request->get('reg_password');
	$reg_password_r = $request->get('reg_password_r');
	$reg_name = $request->get('reg_name');
	$reg_surname = $request->get('reg_surname');
	$reg_phone_number = $request->get('reg_phone_number');
	$u_date_registration = $u_date_active = date("Y-m-d H:i:s");
	global $dbh;
	//check NAME
	if($reg_name==TRUE) {
		if (preg_match("|[\\<>'\"-/]+|", $reg_name)) {
			return "В имени используются недопустимые символы";
			exit();
	}} 
	//check SURNAME
	if($reg_surname==TRUE) {
		if (preg_match("|[\\<>'\"-/]+|", $reg_surname)) {
			return "В фамилии используются недопустимые символы";
			exit();
	}} 
	//check NUMBER
	if($reg_phone_number==TRUE) {
		if (!preg_match("/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/", $reg_phone_number)) {
			return "В номере телефона используются недопустимые символы";
			exit();
	} else {
		$sql = "SELECT * FROM users WHERE u_phone_number = :u_phone_number";
		$stf = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$stf->execute(array(':u_phone_number' => $reg_phone_number));
		$result_phone_number = $stf->fetchAll();
		if($result_phone_number == TRUE){
			return "Такой номер телефона уже существует!";
			exit();
		}}}
	//check EMAIL 	
	if(!preg_match("|^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$|", $reg_email)){
		return "Указан несуществующий Email";
	}else{
		//extract from DB	
		$sql = "SELECT * FROM users WHERE u_email = :u_email";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_email' => $reg_email));
		$result_email = $sth->fetchAll();
		if ($result_email == TRUE) {
			return "Такой Email уже существует!";
			exit();
		} else {
			$reg_activation=md5($reg_email.time());
		}
	//check PASSWORD		
	if ($reg_password != $reg_password_r) {
		return "Пароли не совпадают!";
		exit();
	}else{
		//heshing password	
		$hash = password_hash($reg_password, PASSWORD_DEFAULT);
	}
	//add to DB
	$sql = "INSERT INTO users (u_name, u_surname, u_phone_number, u_email, u_password, u_date_registration, u_date_active, u_activation) VALUES (:u_name, :u_surname, :u_phone_number, :u_email, :u_password, :u_date_registration, :u_date_active, :u_activation)";
	$stm = $dbh->prepare($sql);
	$registration = $stm->execute(array(':u_name' => $reg_name, ':u_surname' => $reg_surname, ':u_phone_number' => $reg_phone_number, ':u_email' => $reg_email, ':u_password' => $hash, ':u_date_registration' => $u_date_registration, ':u_date_active' => $u_date_active, ':u_activation' => $reg_activation));
	global $mailer;
	$message = \Swift_Message::newInstance()
		->setSubject('Подтверждение адреса электронной почты')
		->setFrom(array('bramin90@gmail.com'))
		->setTo(array($reg_email))
		->setBody($app['twig']->render('parts/emails/email_check_post.twig', array('name' => $reg_name, 'site' => $_SERVER['HTTP_HOST'], 'activation' => $reg_activation)),'text/html');
	if($mailer->send($message) and $registration == TRUE){
		$_SESSION['registration']['email']=$reg_email;
		$redirect_url = "/registration/success";
		header('HTTP/1.1 200 OK');
		header('Location: http://'.$_SERVER['HTTP_HOST'].$redirect_url);
		exit();	
	} else {
		return "Проблемы на сервере, обратитесь в службу поддержки!";
		exit();
	}}}
	
	
	function activation ($activation) {
		if (preg_match("|[\\<>'\"-/]+|", $activation)) {
			return "Ошибка, неправильная ссылка активации";
			exit();
		} else {
			global $dbh;
			$sql = "SELECT * FROM users WHERE u_activation = :u_activation";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':u_activation' => $activation));
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			if ($result == FALSE) {
				return "Такой ссылки в системе нет, пожалуйста, зарегистрируйтесь снова!";
				exit();
			} else {
				if ($result['u_status'] == 0) {
					$sql = "UPDATE users SET u_status = :u_status WHERE u_activation = :u_activation";
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':u_status' => 1, ':u_activation' => $activation));
					return "Поздравляем! Ваш аккаунт успешно активирован.";
					exit();
				} else {
					return "Пользователь уже активизировался в системе! Повторная активация не требуется.";
					exit();
				}
			}
		}
	}
					
					
	function authorization ($request){
		$in_email = $request->get('in_email');
		$in_password = $request->get('in_password');
		$in_remember = $request->get('in_remember');
		if(!preg_match("|^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$|", $in_email)) {
			return "Введен некорректный emeil!";
			exit();
		}else{
			global $dbh;		
			$sql = "SELECT * FROM users WHERE u_email = :in_email";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':in_email' => $in_email));
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			if ($result['u_id'] == 0){
				return "Пользователя с таким email в системе не существует! Зарегистрируйтесь!";
				exit();
			} else {
				if (password_verify($in_password, $result['u_password'])) {
					if($in_remember==TRUE) {
						$cookie_auth= mt_rand() . $in_password;
						$auth_key = md5($cookie_auth);
						setcookie("auth_key", $auth_key, time() + 60 * 60 * 24 * 7, "/", $_SERVER['HTTP_HOST'], false, true);
						$sql = "UPDATE users SET u_auth_key = :auth_key WHERE u_id = :u_id";
						$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
						$sth->execute(array(':auth_key' => $auth_key, ':u_id' => $result['u_id']));
					}
					// Success!
					$_SESSION["user"] = $result;
					header('HTTP/1.1 200 OK');
					header('Location: http://'.$_SERVER['HTTP_HOST'].'/id'.$result['u_id']);
					exit();
					
				} else {
					// Invalid credentials
					return "Неверный пароль, повторите попытку!";
					exit();
				}}}}

	function online () {	
		$date_active = date("Y-m-d H:i:s");
		global $dbh;
		$sql = "UPDATE users SET u_date_active = :u_date_active WHERE u_id = :u_id";
		$stm = $dbh->prepare($sql);
		$stm->execute(array(':u_id' => $_SESSION['user']['u_id'], ':u_date_active' => $date_active));
	}

	function analyzer ($u_id){
		global $dbh;
		$sql = "SELECT * FROM users WHERE u_id = :u_id";
		$sto = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sto->execute(array(':u_id' => $u_id));
		$result = $sto->fetch(PDO::FETCH_ASSOC);
		if ($result == TRUE) {		
			if ($_SESSION['user'] == TRUE) {
				if ($_SESSION['user']['u_id'] == $u_id) {
					return "authorization";
					exit();
				} else {
					return "non_authorization";
					exit();
				}				
			} else {					
				return "guest";
				exit();
			}
		} else {
			return 404;
			exit();
		}
		if(isset($_COOKIE['auth_key']) AND $_COOKIE['auth_key']==$result['u_auth_key']) {
			return "authorization";
			exit();
		}	
	}
	
	function code_for_send_task ($app, $request) {
		$task = $request->get('task');
		$email_friend = $request->get('email_friend');
		$code = md5($email_friend.time());
		$code = substr($code, 1, 4);
		$_SESSION['code_for_send_task'] = $code;
		global $mailer;
		$message = \Swift_Message::newInstance()
		->setSubject('Код для создания задачи для'.$email_friend)
		->setFrom(array('bramin90@gmail.com'))
		->setTo(array($_SESSION['user']['u_email']))
		->setBody($app['twig']->render('parts/emails/email_code_for_send_task.twig', array('email_friend' => $email_friend, 'task' => $task, 'code' => $code)),'text/html');
		$mailer->send($message);
		return "success, код выслан вам на почту";
	}
	
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
					return "Задача создана!".$result_task['t_id'].$result_us['u_id'];
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
					return "Задача создана!";				
				}			
			}else {
				return "Вы уже отправили 5 задач за сегодня! Следующий раз, Вы сможите отправить задачу через сутки.".$count_result_today[0]['t_name'].$count_result_today[1]['t_name'].$count_result_today[2]['t_name'].$count_result_today[3]['t_name'];
			}
		} else {
			return "Код не совпадает!";
		}
	}

	function export_tasks ($request) {
		$lim = 5;	//count of product on one page
		$page = $request->get('page');
		$task_type = $request->get('task');
		if (!isset($task_type)) $task_type='own_all';
		if ($task_type == 'own_all') {
			$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id";
			$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and u_id=? ORDER BY t_date_create DESC LIMIT ?,?";			
		} elseif ($task_type == 'own') {
			$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id=:u_id and ut_role=1)";
			$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_id in (select t_id from users_tasks where u_id=? and ut_role=1) ORDER BY t_date_create DESC LIMIT ?,?";	
		} elseif ($task_type == 'other') {
			$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=1)";
			$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=2 and u_id=? and t_id in (select t_id from users_tasks where u_id!=? and ut_role=1) ORDER BY t_date_create DESC LIMIT ?,?";	
		} elseif ($task_type == 'from_others') {
			$sql_all = "select * from tasks left join users_tasks USING (t_id) where ut_role=1 and u_id=:u_id and t_id in (select t_id from users_tasks where u_id!=:u_id and ut_role=2)";
			$sql_special = "select * from tasks left join users_tasks USING (t_id) where ut_role=1 and u_id=? and t_id in (select t_id from users_tasks where u_id!=? and ut_role=2) ORDER BY t_date_create DESC LIMIT ?,?";	
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
		
		for ($stroka_v_massive = 0; $stroka_v_massive < $count_result; $stroka_v_massive++) {
					$result_new['number_' . $stroka_v_massive] = $result_special[$stroka_v_massive];
				}
		$result_new['count_all'] = $count_result;
		$result_new['lim'] = $lim;
		return json_encode ($result_new);		
	}
	
	function logout() {
		unset($_SESSION['user']);
		setcookie ("auth_key", "", time() - 3600, "/", $_SERVER['HTTP_HOST'], false, true);
	}

?>
