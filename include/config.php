<?php

//system roles:
//1 - author
//2 - executor
//

$config['bd']['host'] = 'localhost';
$config['bd']['dbname'] = 'MOTIVATOR';
$config['bd']['charset'] = 'UTF8';
$config['bd']['user'] = 'motiv';
$config['bd']['passwoord'] = 'mypony';
$config['bd']['db_connect'] = 'mysql:host='.$config['bd']['host'].';dbname='.$config['bd']['dbname'].';charset='.$config['bd']['charset'];
$config['mail']['host'] = 'smtp.gmail.com';
$config['mail']['port'] = '465';
$config['mail']['encryption'] = 'ssl';
$config['mail']['user'] = 'bramin90@gmail.com';
$config['mail']['password'] = 'Nashlazyb';

$config['settings']['count_task_on_one_page'] = 5;

return $config;
?>
