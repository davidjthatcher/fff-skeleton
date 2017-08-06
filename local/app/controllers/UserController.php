<?php
/**
 * User controller of sample applicaiton
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Fat-Free-PHP-Bootstrap-Site
 * @author   Mark Takacs <takacsmark@takacsmark.com>
 * @license  MIT
 * @link     takacsmark.com
 */

 /**
 *  User controller class
 *
 * @category PHP
 * @package  Fat-Free-PHP-Bootstrap-Site
 * @author   Mark Takacs <takacsmark@takacsmark.com>
 * @license  MIT
 * @link     takacsmark.com
 */

class UserController extends Controller
{
    /**
     * Renders the login screen
     *
     * @return void
     */
    function login()
    {
        $template=new Template;
        echo $template->render('login.htm');
    }

    /**
     * We override the beforeroute function in the `Controller` class
     *
     * If no logged in user only allow authenticate action.
     *
     * @return void
     */
    function beforeroute()
    {
        if(null === $this->f3->get('SESSION.user')) {

            /* Allow login or authenticate without valid user*/
            if( '/login' === $this->f3->get('PATH') ) {

            } elseif( '/authenticate' === $this->f3->get('PATH') ){

            } else {
                $this->f3->reroute('/login');
                exit;
            }
        }
    }

    /**
     * Authenticates the user based on the inputs
     * from the login form on login.htm
     * Redirects the user to home page if login is successful
     * Redirects to login.htm if login fails
     *
     * @return void
     */
    function authenticate()
    {

        $username = $this->f3->get('POST.username');
        $password = $this->f3->get('POST.password');

        $user = new User($this->db);
        $user->getByName($username);

        if($user->dry()) {
            $this->f3->reroute('/login');
        }

        // if(password_verify($password, $user->password)) {
        if(password_verify($password, $user->password)) {

            $this->f3->set('SESSION.id', $user->id);
            $this->f3->set('SESSION.user', $user->username);
            /* Set user read/write access */
            $this->f3->set('SESSION.access', $user->access);

            $this->f3->reroute('/');
        } else {
            $this->f3->reroute('/login');
        }
    }
    /**
     * List Users who have access to system.
     */
    function userList()
    {
        $user = new User($this->db);
        $users = $user->all();

        $this->f3->set('users', $users );
        $this->f3->set('view', 'userList.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }
    /**
     * Edit User information
     */
    function userEdit()
    {
        $query = $this->f3->get('QUERY');
        parse_str($query, $qvars);

        $user = new User($this->db);
        $user->getById($qvars['id']);

        $this->f3->set( 'user', $user );
        $this->f3->set('view', 'userEdit.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }
    /**
     * Update User data on completion of Edit.
	 * TBD No password change by Admin
     */
    function userUpdate()
    {
        $id = $this->f3->get('POST.id');

        $user = new User($this->db);
        $user->edit($id);

        $this->userList();
    }

    /**
     * Delete selected user.
     * Do not allow to delete yourself.
     */
    function userDelete()
    {
        $query = $this->f3->get('QUERY');
        parse_str($query, $qvars);
        $id = $qvars['id'];

        /* Do not delete current user */
        if( $this->f3->get('SESSION.id') != $id) {
            $user = new User($this->db);
            $user->delete($id);
        }

        $this->userList();
    }

    /**
     * Edit User Password
     */
    function userEditPassword()
    {
        $username = $this->f3->get('SESSION.user');
        $user = new User($this->db);
        $user->getByName($username);

        $this->f3->set('user', $user );
        $this->f3->set('view', 'userEditPassword.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }
    /**
     * Update Password
     * If passsword has been changed, create new hash
     */
    function userUpdatePassword()
    {
        $id = $this->f3->get('POST.id');
        $user = new User($this->db);
        $user->getById($id);

        /* Only update password for current user */
        if( $id == $user->id ){

            /* Password updates require new hash */
            $password1 = $this->f3->get('POST.password1');
            $password2 = $this->f3->get('POST.password2');

            /* Ensure both new passwords match */
            if($password1 == $password2){

                if( $password1 == $user->password ) {
                    /* Nothing to do if no password change */
                } else {
                    $secret = password_hash($password1, PASSWORD_BCRYPT);
                    $this->f3->set('POST.password', $secret);
                    $user->edit($id);
                }
            }
        }

        $this->f3->reroute('/bookingSummary');
    }

    /**
     * Add New User Form
     */
    function userAddNew()
    {
        $this->f3->set('view', 'userAddNew.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }

    /**
     * Save New User Password from Form Input
     */
    function userSaveNew()
    {
        $user = new User($this->db);
		/* TBD - Need to require two identical password entries. */
        /* Password updates require new hash */
        $password1 = $this->f3->get('POST.password1');
        $secret = password_hash($password1, PASSWORD_BCRYPT);
        $this->f3->set('POST.password', $secret);

        $user->add();

        $this->userList();
    }

    /*
     * Log User Out of System
     */
    function userLogout()
    {
        /* Log out of system by reseting SESSION user */
        $this->f3->set('SESSION.user', null);
        $this->f3->reroute('/login');
    }
}
