<?php
/**
 * Controller of the main booking view sample applicaiton (bookingSummary.htm)
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
 * Controller class
 *
 * @category PHP
 * @package  Fat-Free-PHP-Bootstrap-Site
 * @author   Mark Takacs <takacsmark@takacsmark.com>
 * @license  MIT
 * @link     takacsmark.com
 */
class MainController extends Controller
{
    /**
     * Renders the dashboard view template
     *
     * @return void
     */
    function render()
    {
        $this->displayOrderSettings();
    }

    /**
     * Handle HTTP Error Conditions
     *
     * @return void
     */
    function handleError()
    {
        $error = $this->f3->get('ERROR');

        if( '403' == $error['code'] ) {
            $view = 'login.htm';
        } else {
            $view = 'error.htm';
        }

        $template=new Template;
        echo $template->render($view);
    }

    /**
     * Display Order Configuration Settings
     * TBD Change until updated using this Form
     *
     * @return void
     */
    function displayOrderSettings()
    {
        // Show/Update Order (order_) Configuration Settings

        $this->f3->set('header', 'Order Settings');
        $this->f3->set('view', 'orderSettings.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }
    /**
     * Simple Form to get text message to send via WC REST API
     *
     * @return void
     */
    function getRestMessage()
    {
        // Show/Update Order (order_) Configuration Settings

        $this->f3->set('header', 'Rest Message');
        $this->f3->set('view', 'restMessage.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }

    /**
     * Simple Form to get text message to send via WC REST API
     *
     * @return void
     */
    function getRestUpdate()
    {
        // Show/Update Order (order_) Configuration Settings

        $this->f3->set('header', 'Rest Update');
        $this->f3->set('view', 'restUpdate.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }

    /**
     * Get Order Configuration Settings from orderSettings.htm
     * Save in SESSION vars
     * Update the calendar
     * @return void
     */
    function saveOrderSettings()
    {
        /** Get form input. **/
        $order_status = $this->f3->get('POST.order_status');
        $order_start_date = $this->f3->get('POST.order_start_date');

        /* Set user preferences. djt 6/13/2016 */
        $this->f3->set('SESSION.order_status', $order_status);
        $this->f3->set('SESSION.order_start_date', $order_start_date);

		/* Update DB for persistant change. djt 02/04/2017 */
		$id = $this->f3->get('SESSION.id');
        $this->f3->set('POST.id', $id);
        $user = new User($this->db);
        $user->edit($id);

        $this->render();
    }

}
