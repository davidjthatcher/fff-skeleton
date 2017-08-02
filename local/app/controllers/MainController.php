<?php
/**
 * Main Controller of the Responsivie Application Skeleton
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
        $this->eventList();
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
     * Display Event List
     *
     * @return void
     */
    function eventList()
    {
        $event = new Event($this->db);
        $events = $event->all();

        $this->f3->set('events', $events );

        $this->f3->set('header', 'Event Listing');
        $this->f3->set('view', 'eventList.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }
    /**
     * Load Event List Name TBD name
     *
     * @return void
     */
    function eventLoad()
    {
		// Load new event list from CSV File
        $this->f3->set('header', 'Select Event File');
        $this->f3->set('view', 'eventLoad.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }
    /**
     * Load Event List Name TBD name
     *
     * @return void
     */
    function eventLoadList()
    {
		// Store filename, load date.
		$event = new Event($this->db);

		// Get keylist from first line of file
		// Read file into eventlist
        $event->add();

        $this->eventList();
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
        $this->f3->set('header', 'Rest Update');
        $this->f3->set('view', 'restUpdate.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }

}
