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
	$sql = "SELECT t_id, t_name, t_short_name, t_description, t_date_create, t_date_finish FROM tasks left join users_tasks USING (t_id) left join session_authorization USING (u_id) WHERE sa_auth_key = :key_auth AND ut_role = 2 and t_parent = 0 and t_id in (select t_id from users_tasks where sa_auth_key = :key_auth and ut_role=1) ORDER BY t_date_create DESC";
	$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->execute(array(':key_auth' => $key_auth));
	$result = $sth->fetchAll();
	return array('status' => 11, 'result' => $result);
}