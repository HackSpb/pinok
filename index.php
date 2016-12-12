<?php
session_start();

require 'include/config.php';
if(file_exists('include/local.config.php')) include 'include/local.config.php';
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
	'twig.path' => __DIR__.'/templates/'.$config['settings']['template'].'/views',
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

$app->register(new Silex\Provider\SwiftmailerServiceProvider());

$transport = Swift_SmtpTransport::newInstance($config['mail']['host'], $config['mail']['port'], $config['mail']['encryption'])
	->setUsername($config['mail']['user'])
	->setPassword($config['mail']['password']);
$mailer = Swift_Mailer::newInstance($transport);

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->before(function ($request) use ($app) {
    $app['twig']->addGlobal('active', $request->get("_route"));
    $app['twig']->addGlobal('session_user', @$_SESSION['user']);
});


$app->error(
        function (Exception $e) use ($app) {
            if ($e instanceof NotFoundHttpException) {
                return $app['twig']->render('error.twig', array('code' => 404, 'session_user' =>  @$_SESSION['user']));
            }
            $code = ($e instanceof HttpException) ? $e->getStatusCode() : 500;
            return $app['twig']->render('error.twig', array('code' => $code, 'session_user' =>  @$_SESSION['user']));
        }
);
//for app
$app->match('/app/exam/connect/site', function(Request $request) use ($app) {
	$response = array('status' => 'ok');
	if(isset($_GET['callback'])) {
		return $_GET['callback'] . '(' . json_encode($response) . ')';
	}
});

$app->match('/app/authorization/site', function(Request $request) use ($app) {
	if(isset($_GET['user_email']) && isset($_GET['user_password'])) {
		$response = array('status' => 'ok');
		if(isset($_GET['callback'])) {
			return $_GET['callback'] . '(' . json_encode($response) . ')';
		}
	}
});



//for script
$app->match('/logout', function() use ($app) {
	logout();
	$redirect_url = "/";
	header('HTTP/1.1 200 OK');
	header('Location: http://'.$_SERVER['HTTP_HOST'].$redirect_url);
	exit;
});

$app->match('/task/do/create', function(Request $request) use ($app) {
	return create_task($app, $request);
});

$app->match('/task/list/right', function(Request $request) use ($app) {
	return task_list_right($request);
});

$app->match('/task/list/content', function(Request $request) use ($app) {
	return task_list_content($request);
});

$app->match('/task/information/content', function(Request $request) use ($app) {
	return task_information_render($request);
});

$app->match('/task/update/status', function(Request $request) use ($app) {
	return update_task_status($request);
});

$app->match('/task/update/accept', function(Request $request) use ($app) {
	return update_task_accept($request);
});

$app->match('/task/update/raiting', function(Request $request) use ($app) {
	return update_task_raiting($request);
});

$app->match('/task/update/archive', function(Request $request) use ($app) {
	return update_task_archive($request);
});

$app->match('/task/update/cancel', function(Request $request) use ($app) {
	return update_task_cancel($request);
});

$app->match('/task/update/delete', function(Request $request) use ($app) {
	return update_task_delete($request);
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
	return $app['twig']->render('home.twig', array('code' => @$code));
})->bind('home');

$app->match('/registration', function(Request $request) use ($app) {
	$reg_name = $request->get('reg_name');
	$reg_password = $request->get('reg_password');
	if (isset($reg_name) or isset($reg_password)) $code = registration($request, $app);
	return $app['twig']->render('registration.twig', array('code' => @$code));
})->bind('registration');

$app->match('/community', function() use ($app) {
	return $app['twig']->render('community.twig');
});

$app->match('/news', function() use ($app) {
	return $app['twig']->render('news.twig');
});

$app->match('/application', function() use ($app) {
	return $app['twig']->render('application.twig');
});

$app->match('/activation/{activation}', function($activation) use ($app) {
	$activation_user = activation($activation);
	return $app['twig']->render('activation.twig', array('activation' => $activation_user));
});

$app->match('/admin', function() use ($app) {
	return $app['twig']->render('admin.twig');
});

$app->match('/id{u_id}', function(Request $request, $u_id) use ($app) {
	if (exam_user($u_id)) return $app->abort(404, $app['twig']->render('error.twig', array('code' => 404)));
	$role = analyzer($u_id);
	if ($role == 404) {
		return $app['twig']->render('error.twig', array('code' => $role));
		exit();
	}
	return $app['twig']->render('user.twig', array('template' => $role['template'], 'user' => $role));
})->assert('u_id', '\d+');

$app->run();
