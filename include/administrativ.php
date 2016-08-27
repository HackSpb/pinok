<?php

function registration ($request, $app){
	$reg_email = $request->get('reg_email');
	$reg_password = $request->get('reg_password');
	$reg_password_r = $request->get('reg_password_r');
	$reg_name = $request->get('reg_name');
	$reg_surname = $request->get('reg_surname');
	$reg_phone_number = $request->get('reg_phone_number');
	$reg_city = $request->get('reg_city');
	$reg_true_with_regulations = $request->get('reg_true_with_regulations');
	$u_date_registration = $u_date_active = date("Y-m-d H:i:s");

	global $dbh;
	global $mailer;
	global $config;

	if (email_valid($reg_email) !== TRUE) $error[] = email_valid($reg_email);
	if (iconv_strlen($reg_password)>3 && iconv_strlen($reg_password)<51) {if (password_valid($reg_password) !== TRUE) $error[] = password_valid($reg_password);} else {$error[] = 'В пароле должно быть от 4 до 50 символов!';
}
	if (name_valid($reg_name) !== TRUE) $error[] = name_valid($reg_name);
	if (name_valid($reg_surname) !== TRUE) $error[] = name_valid($reg_surname);
	if ($reg_phone_number == TRUE) {if (number_phone_valid($reg_phone_number) !== TRUE) $error[] = number_phone_valid($reg_phone_number);}
	if (city_valid($reg_city) !== TRUE) $error[] = city_valid($reg_city);
	if (regulations_valid($reg_true_with_regulations) !== TRUE) $error[] = regulations_valid($reg_true_with_regulations);
	if ($reg_password != $reg_password_r) $error[] = 'Пароли не совпадают, попробуйте снова!';

	if ($reg_phone_number==TRUE) {
		$sql = "SELECT u_name FROM users WHERE u_phone_number = :u_phone_number";
		$stf = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$stf->execute(array(':u_phone_number' => $reg_phone_number));
		$result_phone_number = $stf->fetchAll();
		if(count($result_phone_number) > 0) {
			$error[] = 'Такой номер телефона уже существует!';
		}
	}

	if (count($error) > 0) {
		return $error;
		exit();
	}

	$reg_activation=md5($reg_email.time());
	$hash = password_hash($reg_password, PASSWORD_DEFAULT);

	$sql = "SELECT * FROM users WHERE u_email = :u_email";
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->execute(array(':u_email' => $reg_email));
	$result_email = $sth->fetchAll();
	if(count($result_email) == 1) {
		if ($result_email[0]['u_status'] == 1) {
			$error[] = 'Такой email уже существует!';
			return $error;
			exit();
		} else {
			$sql = "UPDATE `users` SET `u_name`=:u_name,`u_surname`=:u_surname,`u_city`=:u_city,`u_phone_number`=:u_phone_number,`u_password`=:u_password,`u_activation`=:u_activation,`u_date_registration`=:u_date_registration,`u_date_active`=:u_date_active WHERE 'u_email' = :u_email";
			$stm = $dbh->prepare($sql);
			$registration = $stm->execute(array(':u_name' => $reg_name, ':u_surname' => $reg_surname, ':u_city' => $reg_city, ':u_phone_number' => $reg_phone_number, ':u_email' => $reg_email, ':u_activation' => $reg_activation, ':u_password' => $hash, ':u_date_registration' => $u_date_registration, ':u_date_active' => $u_date_active));
		}
	} else {
		$sql = "INSERT INTO users (u_name, u_surname, u_city, u_phone_number, u_email, u_activation, u_password, u_date_registration, u_date_active) VALUES (:u_name, :u_surname, :u_city, :u_phone_number, :u_email, :u_activation, :u_password, :u_date_registration, :u_date_active)";
		$stm = $dbh->prepare($sql);
		$registration = $stm->execute(array(':u_name' => $reg_name, ':u_surname' => $reg_surname, ':u_city' => $reg_city, ':u_phone_number' => $reg_phone_number, ':u_email' => $reg_email, ':u_activation' => $reg_activation, ':u_password' => $hash, ':u_date_registration' => $u_date_registration, ':u_date_active' => $u_date_active));	
	}

	$message = \Swift_Message::newInstance()
		->setSubject('Подтверждение адреса электронной почты')
		->setFrom(array($config['mail']['user']))
		->setTo(array($reg_email))
		->setBody($app['twig']->render('parts/emails/email_check_post.twig', array('name' => $reg_name, 'site' => $_SERVER['HTTP_HOST'], 'activation' => $reg_activation)),'text/html');
	$mailer->send($message);

	$message = \Swift_Message::newInstance()
		->setSubject('На проекте появился новый пользователь')
		->setFrom(array($config['mail']['user']))
		->setTo(array('pav1887@yandex.ru'))
		->setBody($app['twig']->render('parts/emails/warning_new_user.twig', array('name' => $reg_name, 'surname' => $reg_surname, 'number' => $reg_phone_number, 'email' => $reg_email, 'date' => $u_date_registration)),'text/html');
	$mailer->send($message);

	$redirect_url = "/";
	
	//$_POST["message"] = 'Регистрация прошла успешно! На Ваш Email ('.$reg_email.') выслано письмо с ссылкой для подтверждения электронного адреса.';

	header('HTTP/1.1 200 OK');
	header('Location: http://'.$_SERVER['HTTP_HOST'].$redirect_url);
	exit();
}


function activation ($activation) {
	global $dbh;

	if (code_activation_valid($activation) !== TRUE) {
		return code_activation_valid($activation);
		exit();
	} else {
			
		$sql = "SELECT u_status FROM users WHERE u_activation = :u_activation";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':u_activation' => $activation));
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		if ($result == FALSE) {
			return "Ошибка, неправильная ссылка активации";
			exit();
		} else {
			if ($result['u_status'] == 0) {
				$sql = "UPDATE users SET u_status = :u_status WHERE u_activation = :u_activation";
				$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':u_status' => 1, ':u_activation' => $activation));
				return "Поздравляем! Ваш аккаунт успешно активирован.";				
			} else {
				return "Пользователь уже активизировался в системе! Повторная активация не требуется.";
			}
		}
	}
}

function authorization ($request){
	$in_email = $request->get('in_email');
	$in_password = $request->get('in_password');
	$in_remember = $request->get('in_remember');

	if (email_valid($in_email) !== TRUE) $error[] = email_valid($in_email);
	if (password_valid($in_password) !== TRUE) $error[] = password_valid($in_password);
	if ($in_remember == 1) { 
		$in_remember = FALSE;
	} else {
		 $in_remember = TRUE;
	}

	if (count($error) > 0) {
		return $error;
		exit();
	}

	global $dbh;

	$sql = "SELECT * FROM users WHERE u_email = :in_email";
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->execute(array(':in_email' => $in_email));
	$result = $sth->fetch(PDO::FETCH_ASSOC);
	if ($result['u_id'] == 0){
		$error[] = "Пользователя с таким email в системе не существует! Зарегистрируйтесь!";
		return $error;
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
			$error[] =  "Неверный пароль, повторите попытку!";
			return $error;
			exit();
		}
	}
}


function online () {	
	$date_active = date("Y-m-d H:i:s");
	global $dbh;

	$sql = "UPDATE users SET u_date_active = :u_date_active WHERE u_id = :u_id";
	$stm = $dbh->prepare($sql);
	$stm->execute(array(':u_id' => $_SESSION['user']['u_id'], ':u_date_active' => $date_active));
}
	

function analyzer ($u_id){
	global $dbh;
	if (id_valid($u_id) !== TRUE) $error = id_valid($u_id);

	if (isset($error)) {
		return $error;
		exit();
	}

	$sql = "SELECT * FROM users WHERE u_id = :u_id";
	$sto = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sto->execute(array(':u_id' => $u_id));
	$result = $sto->fetch(PDO::FETCH_ASSOC);
	if ($result == TRUE) {		
		if ($_SESSION['user'] == TRUE) {
			if ($_SESSION['user']['u_id'] == $u_id) {
				$user['template'] = "authorization";
				return $user;
				exit();
			} else {
				$user = $result;
				$user['template'] = "guest";
				return $user;
				exit();
			}				
		} else {			
			$user['template'] = "non_authorization";
			return $user;		
			exit();
		}
	} else {
		return 404;
		exit();
	}
}
	

function logout() {
	unset($_SESSION['user']);
	setcookie ("auth_key", "", time() - 3600, "/", $_SERVER['HTTP_HOST'], false, true);
}

function turn_on_session() {
	global $dbh;
	$sql = "SELECT * FROM users WHERE u_auth_key = :u_auth_key";
	$sto = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sto->execute(array(':u_auth_key' =>$_COOKIE['auth_key']));
	$result = $sto->fetch(PDO::FETCH_ASSOC);
	if($result==TRUE) {
		$_SESSION["user"] = $result;
	}
}