<?php

$f3 = require('/var/lib/fatfree-master/lib/base.php');
require_once( '../vendor/WooCommerce-REST-API-Client-Library/lib/woocommerce-api.php' );

$f3->config('../local/config.ini');
$f3->config('../local/routes.ini');

$f3->set('ONERROR','MainController->handleError');

// for TDD User Model
$f3->map('/test1','UserCntlrTest');
$f3->map('/test2','UserModelTest');

//new Session();

$f3->run();
