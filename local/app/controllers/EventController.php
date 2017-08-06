<?php
 /**
 *  Event controller class
 *
 * @category PHP
 * @package  Fat-Free-PHP-Bootstrap-Site
 * @author   Mark Takacs <takacsmark@takacsmark.com>
 * @license  MIT
 * @link     takacsmark.com
 */

class EventController extends Controller
{
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
        $filename = $this->f3->get('POST.filename');

		$myfile = fopen('app/testdata/'."$filename", "r");
		$myData = array();
		// Read file into eventlist - TBD this block
		while (($buffer = fgetcsv($myfile, 4096,",","'")) !== false) {
			$myData[] = json_encode($buffer);
			$event = new Event($this->db);

			$event->dayofweek = $buffer[0];
			$event->timeofday = $buffer[1];
			$event->area      = $buffer[2];
			$event->grp       = $buffer[3];
			$event->address   = $buffer[4];
			$event->city      = $buffer[5];
			$event->state     = $buffer[6];
			$event->zip       = $buffer[7];
			$event->type      = $buffer[8];
			$event->geo       = NULL;

			$event->save();
		}

		fclose($myfile);

        $this->showArray($myData);
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

        $this->showArray($event->fields());
    }

}
