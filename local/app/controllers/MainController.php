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
     * Load Event List
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
     * Load Event List to Model(database)
     *
     * @return void
     */
    function eventLoadList()
    {
		// Save selected filename, load date.
		$event = new Event($this->db);
		$event->load_date = date("Y/m/d");

		// Get keylist from first line of file
        $event->filename = $this->f3->get('POST.filename');

		$myfile = fopen('app/testdata/'."$event->filename", "r");
		$event->keylist = fgets($myfile);
		// Read file into eventlist - TBD this block
		$my_eventlist = $this->eventListRead($myfile);
		fclose($myfile);

		// TBD How to set eventlist?
		// $event->eventlist =  json_encode($my_eventlist, JSON_PRETTY_PRINT);
		$event->eventlist = 'TBD How to store eventlist?';

        $event->add();

        $this->eventList();
    }
    /**
     * Read event list from file.
     */
    function eventListRead($myfile)
    {
		$new_eventlist = array();

		while (($buffer = fgets($myfile, 4096)) !== false) {
			$new_eventlist[] = $buffer;
		}

		return $new_eventlist;
    }
    /**
     * Delete selected event.
     */
    function eventDelete()
    {
        $query = $this->f3->get('QUERY');
        parse_str($query, $qvars);
        $id = $qvars['id'];

        $event = new Event($this->db);
        $event->delete($id);

        $this->eventList();
    }
    /**
     * View event list data.
     */
    function eventView()
    {
        $query = $this->f3->get('QUERY');
        parse_str($query, $qvars);
        $id = $qvars['id'];

        $event = new Event($this->db);
        $event->getById($id);

		$myfile = fopen('app/testdata/'."$event->filename", "r");
		$event->keylist = fgets($myfile);
		// Read file into eventlist - TBD this block
		$my_eventlist = $this->eventListRead($myfile);
		fclose($myfile);

        $this->showArray($my_eventlist);
    }
    /**
     * Simple view to show text
     *
     * @return void
     */
    function showArrayJson($array)
    {
        $this->f3->set('header', 'Show Array JSON');
        $this->f3->set('json', json_encode($array, JSON_PRETTY_PRINT));
        $this->f3->set('view', 'jsonList.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }
    /**
     * Simple view to show text
     *
     * @return void
     */
    function showArray($array)
    {
        $this->f3->set('header', 'Show Array');
        $this->f3->set('my_array', $array);
        $this->f3->set('view', 'arrayList.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }

}
