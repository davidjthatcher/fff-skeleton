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
			'User Controller Methods(FYI): '.json_encode(get_class_methods($cntlr))
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
		$f3->mock($cntlrRoute);
		$view = $f3->get('UI') . $f3->get('view');
		$test->expect(
			file_exists($view),
			$cntlrRoute . ': view = ' .  $view
		);
		$test->expect(
			false,
			'User Controller GET  /userDelete'
		);
		//
		$cntlrRoute = 'GET  /userEdit';
		$f3->mock($cntlrRoute);
		$view = $f3->get('UI') . $f3->get('view');
		$test->expect(
			file_exists($view),
			$cntlrRoute . ': view = ' .  $view
		);
		$test->expect(
			false,
			'User Controller GET  /userEdit'
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
		//
		$cntlrRoute = 'POST /userSaveNew';
		//$f3->mock($cntlrRoute);
		$view = $f3->get('UI') . $f3->get('view');
		$test->expect(
			file_exists($view),
			$cntlrRoute . ': view = ' .  $view
		);
		$test->expect(
			false,
			$cntlrRoute
		);
		//
		//
		$cntlrRoute = 'POST /userUpdate';
		//$f3->mock($cntlrRoute);
		$view = $f3->get('UI') . $f3->get('view');
		$test->expect(
			file_exists($view),
			$cntlrRoute . ': view = ' .  $view
		);
		$test->expect(
			false,
			$cntlrRoute
		);
		//
		$cntlrRoute = 'POST /userUpdatePassword';
		//$f3->mock($cntlrRoute);
		$view = $f3->get('UI') . $f3->get('view');
		$test->expect(
			file_exists($view),
			$cntlrRoute . ': view = ' .  $view
		);
		$test->expect(
			false,
			$cntlrRoute
		);
		//
		$cntlrRoute = 'POST /login';
		//$f3->mock($cntlrRoute);
		$test->expect(
			false,
			$cntlrRoute
		);
		//
		$cntlrRoute = 'POST /logout';
		//$f3->mock($cntlrRoute);
		$test->expect(
			false,
			$cntlrRoute
		);
		//
		$cntlrRoute = 'POST /authenticate';
		//$f3->mock($cntlrRoute);
		$test->expect(
			false,
			$cntlrRoute
		);

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
