<?php

//system roles:
//1 - author
//2 - executor
//

/*define ("HOST", "localhost");
define ("DBNAME", "MOTIVATOR");
define ("CHARSET", "UTF8");
define ("USER", "motiv");
define ("PASSWORD", "mypony");
define ("DB", "mysql:host=localhost;dbname=MOTIVATOR;charset=UTF8");
define ("POST_HOST", "smtp.gmail.com");
define ("POST_PORT", "465");
define ("POST_ENCRYPTION", "ssl");
define ("POST_USERNAME", "bramin90@gmail.com");
define ("POST_PASSWORD", "Nashlazyb");*/

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

return $config;
?>
