<?php

$f3 = require("/var/lib/fatfree-master/lib/base.php");

$f3 = Base::instance();

$f3->config('../local/config.ini');
$f3->config('../local/routes.ini');

$f3->set('ONERROR','MainController->handleError');

new Session();

$f3->run();