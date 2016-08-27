<?php
session_start();

require 'include/config.php';
require 'include/main_func.php';

$dbh = new PDO($config['bd']['db_connect'], $config['bd']['user'], $config['bd']['passwoord']);


if(isset($_COOKIE['auth_key']) AND !isset($_SESSION['user'])) {
	turn_on_session();
}	

if (isset($_SESSION['user'])) {
	online();
}

require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/views',
));

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
        return sprintf('%s/%s', trim($app['request']->getBasePath()), ltrim($asset, '/'));
    }));
    return $twig;
}));

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\SwiftmailerServiceProvider;


$app->error(
        function (Exception $e) use ($app) {
            if ($e instanceof NotFoundHttpException) {
                return $app['twig']->render('error.twig', array('code' => 404, 'session' => @$_SESSION['user']));
            }
            $code = ($e instanceof HttpException) ? $e->getStatusCode() : 500;
            return $app['twig']->render('error.twig', array('code' => $code, 'session' => @$_SESSION['user']));
        }
);


$app->register(new Silex\Provider\SwiftmailerServiceProvider());

$transport = Swift_SmtpTransport::newInstance($config['mail']['host'], $config['mail']['port'], $config['mail']['encryption'])
	->setUsername($config['mail']['user'])
	->setPassword($config['mail']['password']);
$mailer = Swift_Mailer::newInstance($transport);

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->before(function ($request) use ($app) {
    $app['twig']->addGlobal('active', $request->get("_route"));
});

//for script
$app->match('/logout', function() use ($app) {
	logout();
	$redirect_url = "/";
	header('HTTP/1.1 200 OK');
	header('Location: http://'.$_SERVER['HTTP_HOST'].$redirect_url);
	exit;
});

$app->match('/task/select/special', function(Request $request) use ($app) {
	return task_select($request);
});

$app->match('/task/add/new', function(Request $request) use ($app) {
	return task_add_new($request);
});

$app->match('/task/send/for_other', function(Request $request) use ($app) {
	return send_task($app, $request);
});

$app->match('/task/update/status', function(Request $request) use ($app) {
	return update_task_status($request);
});

$app->match('/task/statistics/select', function(Request $request) use ($app) {
	return statistics_tasks($request);
});


//for web
$app->match('/', function(Request $request) use ($app) {
	$registration = $request->get('message');	
	$in_email = $request->get('in_email');	
	$in_password = $request->get('in_password');
	if (isset($in_email) or isset($in_password)) $code = authorization($request);
	if (isset($registration)) $code = $registration;
	return $app['twig']->render('home.twig', array('code' => @$code, 'session_user' => @$_SESSION['user']));
})->bind('home');

$app->match('/registration', function(Request $request) use ($app) {
	$reg_name = $request->get('reg_name');
	$reg_password = $request->get('reg_password');
	if (isset($reg_name) or isset($reg_password)) $code = registration($request, $app);
	return $app['twig']->render('registration.twig', array('code' => @$code, 'session_user' => @$_SESSION['user']));
})->bind('registration');

$app->match('/community', function() use ($app) {
	return $app['twig']->render('community.twig', array('session_user' => @$_SESSION['user']));
});

$app->match('/news', function() use ($app) {
	return $app['twig']->render('news.twig', array('session_user' => @$_SESSION['user']));
});

$app->match('/application', function() use ($app) {
	return $app['twig']->render('application.twig', array('session_user' => @$_SESSION['user']));
});

$app->match('/activation/{activation}', function($activation) use ($app) {
	$activation_user = activation($activation);
	return $app['twig']->render('activation.twig', array('activation' => $activation_user, 'session_user' => @$_SESSION['user']));
});

$app->match('/id{u_id}', function(Request $request, $u_id) use ($app) {
	$role = analyzer($u_id);
	if ($role == 404) {
		return $app['twig']->render('error.twig', array('code' => $role, 'session_user' => @$_SESSION['user']));
		exit();
	}
	return $app['twig']->render('user.twig', array('template' => $role['template'], 'user' => $role, 'session_user' => @$_SESSION['user']));
});

$app->run();
