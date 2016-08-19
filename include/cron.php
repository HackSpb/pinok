<?php
include 'config.php';

require_once __DIR__.'/../vendor/autoload.php';
$app['debug'] = true;

$dbh = new PDO($config['bd']['db_connect'], $config['bd']['user'], $config['bd']['passwoord']);

use Silex\Provider\SwiftmailerServiceProvider;
$app->register(new Silex\Provider\SwiftmailerServiceProvider());

$transport = Swift_SmtpTransport::newInstance($config['mail']['host'], $config['mail']['port'], $config['mail']['encryption'])
	->setUsername($config['mail']['user'])
	->setPassword($config['mail']['password']);
$mailer = Swift_Mailer::newInstance($transport);

$time_now = date("H:i:s");

$sql = "SELECT * FROM tasks left join users_tasks USING (t_id) WHERE t_hour_reminder = :t_hour_reminder AND t_status = 1 AND ut_role = 2";
$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':t_hour_reminder' => $time_now));
$result_task = $sth->fetch(PDO::FETCH_ASSOC);

$count_result_task = count($result_task);

for ($i=0; $i<$count_result_task; $i++){
	if ($result_task[$i]['t_date_last_remainder']){
		if (/*прошло ли время t_frequency_reminder с момента t_date_last_reminder*/) {
			if (/*активен ли пользователь в системе*/) {
				//отправить напоминалку
				//записать дату последнего напоминания
			}
		}
	} else {
		//отправить напоминалку
		//записать дату последнего напоминания
	}
}

?>