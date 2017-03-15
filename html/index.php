<?php

require_once( '../vendor/autoload.php' );

$f3 = \Base::instance();

$f3->config('../local/config.ini');
$f3->config('../local/routes.ini');

$f3->set('ONERROR','MainController->handleError');

// for TDD User Model
$f3->map('/test1','UserModelTest');
$f3->map('/test2','UserCntlrTest');
$f3->map('/test3','BookingsModelTest');

//new Session();

$f3->run();
