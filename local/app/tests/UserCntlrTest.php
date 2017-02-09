<?php

class UserCntlrTest extends Controller {

	function get($f3) {
		$test=new \Test;

		$test->expect(
			is_null($f3->get('ERROR')),
			'Starting User Controller Test'
		);
		$cntlr = New UserController();
		$test->expect(
			!empty($cntlr),
			'New User Controller'
		);

		$test->expect(
			true,
			'User Controller Methods(FYI): '.json_encode(get_class_methods($cntlr), JSON_PRETTY_PRINT)
		);

		$f3->set('QUIET',true);  	// Can't test output. What can we test?
		// Test each GET in router
		$cntlrRoute = 'GET  /userAddNew';
		$f3->mock($cntlrRoute);
		$view = $f3->get('UI') . $f3->get('view');
		$test->expect(
			file_exists($view),
			$cntlrRoute . ': view = ' .  $view
		);
		//
		$cntlrRoute = 'GET  /userDelete';
		$name='id'; $value = '2';
		$f3->mock($cntlrRoute.'?'.$name.'='.$value);
		$test->expect(
			$_GET==array($name=>$value),
			$cntlrRoute . ': Query name/value pair = ' .  json_encode($_REQUEST, JSON_PRETTY_PRINT)
		);
		//
		$cntlrRoute = 'GET  /userEdit';
		$f3->mock($cntlrRoute.'?'.$name.'='.$value);
		$view = $f3->get('UI') . $f3->get('view');
		$test->expect(
			file_exists($view),
			$cntlrRoute . ': view = ' .  $view
		);
		$test->expect(
			$_GET==array($name=>$value),
			$cntlrRoute . ': Query name/value pair = ' .  json_encode($_REQUEST, JSON_PRETTY_PRINT)
		);
		//
		$cntlrRoute = 'GET  /userEditPassword';
		$f3->mock($cntlrRoute);
		$view = $f3->get('UI') . $f3->get('view');
		$test->expect(
			file_exists($view),
			$cntlrRoute . ': view = ' .  $view
		);
		$user = $f3->get('user');
		$test->expect(
			!$user->dry(),
			$cntlrRoute . ' user'
		);
		//
		$cntlrRoute = 'GET  /userList';
		$f3->mock($cntlrRoute);
		$view = $f3->get('UI') . $f3->get('view');
		$test->expect(
			file_exists($view),
			$cntlrRoute . ': view = ' .  $view
		);

		$test->expect(
			!empty($f3->get('users')),
			$cntlrRoute . ' -> users [' . count($f3->get('users')) . ']'
		);
/*
		//
		$cntlrRoute = 'POST /userSaveNew';
		$f3->mock($cntlrRoute);
		$test->expect(
			true,
			$cntlrRoute . ' - TBD How can I test?'
		);
		//
		$cntlrRoute = 'POST /userUpdate';
		$f3->mock($cntlrRoute);
		$test->expect(
			true,
			$cntlrRoute . ' - TBD How can I test?'
		);
		//
		$cntlrRoute = 'POST /userUpdatePassword';
		$test->expect(
			true,
			$cntlrRoute . ' - TBD How can I test?'
		);
		//
		$cntlrRoute = 'GET /login';
		$f3->mock($cntlrRoute);
		$test->expect(
			true,
			$cntlrRoute . ' - TBD How can I test?'
		);
		//
		$cntlrRoute = 'GET /logout';
		//$f3->mock($cntlrRoute);
		$test->expect(
			true,
			$cntlrRoute . ' - TBD How can I test?'
		);
		//
		$cntlrRoute = 'POST /authenticate';
		//$f3->mock($cntlrRoute);
		$test->expect(
			true,
			$cntlrRoute . ' - TBD How can I test?' . json_encode($_POST, JSON_PRETTY_PRINT)
		);
*/
		$f3->set('QUIET',FALSE);  // show output of the active route

		// Return Test Results for each Test Controller
		$f3->set('results',$test->results());
	}

	function afterroute() {
        $this->f3->set('view', 'testresults.htm');

        $template=new Template;
        echo $template->render('layout.htm');
	
	}
}
