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
    function render()
    {
        $template=new Template;
        echo $template->render('login.htm');
    }

    /**
     * We override the beforeroute function in the `Controller` class
     * Therefore the parent behaviour will not happen
     * i.e. we do not check if there is a logged in user, because
     * no user is logged in when the login view is loaded
     *
     * @return void
     */
    function beforeroute()
    {
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

            $this->f3->set('SESSION.user', $user->username);
            /* Set user preferences. djt 6/13/2016 */
            $this->f3->set('SESSION.order_status', $user->order_status);
            $this->f3->set('SESSION.order_start_date', $user->order_start_date);
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
        $qvars = array();
        parse_str($query, $qvars);

        $user = new User($this->db);
        $user->getById($qvars['id']);

        echo $user->username;
        echo json_encode($myUser);

        $this->f3->set( 'user', $user );
        $this->f3->set('view', 'userEdit.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }
    /**
     * List Users who have access to system.
     * If passsword has been changed, create new hash
     */
    function userUpdate()
    {
        $id = $this->f3->get('POST.id');

        $user = new User($this->db);
        $user->edit($id);

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

    function userLogout()
    {
        /* Log out of system by reseting SESSION user */
        $this->f3->set('SESSION.user', null);
        $this->f3->reroute('/login');
    }
}
