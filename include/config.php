<?php

//system roles:
//1 - author
//2 - executor
//

$config['bd']['host'] = 'localhost';
$config['bd']['dbname'] = 'PINOK';
$config['bd']['charset'] = 'UTF8';
$config['bd']['user'] = 'root';
$config['bd']['passwoord'] = '';
$config['bd']['db_connect'] = 'mysql:host='.$config['bd']['host'].';dbname='.$config['bd']['dbname'].';charset='.$config['bd']['charset'];
$config['mail']['host'] = 'smtp.gmail.com';
$config['mail']['port'] = '465';
$config['mail']['encryption'] = 'ssl';
$config['mail']['user'] = 'bramin90@gmail.com';
$config['mail']['password'] = 'Nashlazyb';

$config['settings']['count_task_on_one_page'] = 5;
$config['settings']['template'] = 'default';
$config['settings']['count_task_for_another_people']['tarif_free'] = 5;
$config['settings']['count_task_for_another_people']['tarif_sms'] = 20;
$config['settings']['count_task_for_another_people']['tarif_call'] = 10000;

$config['user']['settings']['name'] = 'default';
$config['user']['settings']['default']['us_turn_notification_about_new_task'] = 1;

return $config;
?>
