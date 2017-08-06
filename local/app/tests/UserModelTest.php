<?php

class UserModelTest extends Controller {

	function get($f3) {
		$test=new \Test;

		$test->expect(
			is_null($f3->get('ERROR')),
			'Starting User Model Test'
		);

		$cntlr = New Controller();
		$test->expect(
			!empty($cntlr),
			'Controller Provides db handle for New User Object Instance'
		);

		$user = New User( $cntlr->db );
		$test->expect(
			!empty($user),
			'New User Not Empty'
		);

		$test->expect(
			true,
			'User Model Methods(FYI): '.json_encode(get_class_methods($user), JSON_PRETTY_PRINT)
		);

		$test->expect(
			true,
			'User Model Schema(FYI): ' . json_encode($user->schema(), JSON_PRETTY_PRINT)
		);

		$test->expect(
			'user' == $user->table(),
			'Table name is Model Name == user'
		);

		$test->expect(
			true,
			'User Model Fields(FYI): '.json_encode($user->fields(), JSON_PRETTY_PRINT)
		);

		$myFields = $user->fields();
		$test->expect(
			in_array('id', $myFields) &&
			in_array('username', $myFields) &&
			in_array('password', $myFields) &&
			in_array('access', $myFields),
			'These fields must exist for views: '.json_encode($myFields, JSON_PRETTY_PRINT)
		);

		$test->expect(
			0 < $user->count(),
			'Must be one or more Users: '.$user->count()
		);

		$users = $user->all();
		$myUserArray = array();
		foreach( $users as $myUser ) {
			$test->expect(
				true,
				'My User { '.$myUser->id.', '.$myUser->username.', '.$myUser->access.' }'
			);

			$myUserArray[] = [$myUser->id, $myUser->username, $myUser->access];
		};

		$test->expect(
			true,
			'All Users: '.json_encode($myUserArray, JSON_PRETTY_PRINT)
		);
		// Find each record by id. Validate equal
		foreach( $myUserArray as $myUser ) {
			$user->getById($myUser[0]);
			$test->expect(
				$user->dry() == false &&
				$myUser[0] == $user->id,
				'get By Id Confirmed { '.$myUser[0].' == '.$user->id.' }'
			);
		};
		$user->getById(0);
		$test->expect(
			$user->dry() == true,
			'get By Id not found'
		);
		// Find each record by id. Validate equal
		foreach( $myUserArray as $myUser ) {
			$user->getByName($myUser[1]);
			$test->expect(
				$user->dry() == false &&
				$myUser[1] == $user->username,
				'get By Name Confirmed { '.$myUser[1].' == '.$user->username.' }'
			);
		};
		$user->getByName('xxx');
		$test->expect(
			$user->dry() == true,
			'get By Name not found'
		);

		// User Model uses copy from POST for form input
		// Must add() for New User
		$user->all();
		$beforeCount = $user->count();
		$f3->set('POST.username', 'username');
		$f3->set('POST.password', 'new-password');
		$f3->set('POST.access', 'read');
		$newUser = New User($cntlr->db);
		$newUser->add();

		$test->expect(
			$user->count() == $beforeCount + 1,
			'add method - new user.id = ' . $newUser->id
		);

		$user->all();
		$beforeCount = $user->count();
		$newUser = New User($cntlr->db);
		$newUser->add();
		$test->expect(
			$user->count() == $beforeCount,
			'add method - username must be unique, user not added twice'
		);

		$f3->set('POST.username', 'new-username');
		$f3->set('POST.password', 'new-password');
		$f3->set('POST.access', 'read');

		$user->getByName('username');
		$user->edit($user->id);
		$test->expect(
			$user->username == 'new-username',
			'edit method - set new-username.id = ' . $user->id
		);

		$beforeCount = $user->count();
		$user->getByName('new-username');
		$user->delete($user->id);
		$test->expect(
			$user->count() == ($beforeCount - 1),
			'delete method - new user.id = ' . $user->id

		);

		$f3->set('results',$test->results());
	}

	function afterroute() {
		//echo \Preview::instance()->render('testresults.htm');
        $this->f3->set('view', 'testresults.htm');

        $template=new Template;
        echo $template->render('layout.htm');
	
	}
}
