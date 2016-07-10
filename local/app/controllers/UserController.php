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
}
