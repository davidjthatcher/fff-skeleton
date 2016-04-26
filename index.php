<?php

$f3 = require("/var/lib/fatfree-master/lib/base.php");

$f3->set('AUTOLOAD','app/models; app/controllers');

$f3 = Base::instance();

$f3->config('config.ini');
$f3->config('routes.ini');

new Session();

$f3->run();
