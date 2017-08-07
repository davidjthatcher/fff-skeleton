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
    function eventLoadCsvFile()
    {
		// Save selected filename, load date.
        $filename = $this->f3->get('POST.filename');

		$myfile = fopen('app/testdata/'."$filename", "r");

		// Read file into eventlist - TBD this block
		while (($buffer = fgetcsv($myfile, 4096,",","'")) !== false) {

			$event = new Event($this->db);
			// Ensure we don't overwrite end of buffer for event stings.
			$event->dayofweek = mb_substr($buffer[0], 0, $event->elementLength('dayofweek'));
			$event->timeofday = mb_substr($buffer[1], 0, $event->elementLength('timeofday'));
			$event->area      = mb_substr($buffer[2], 0, $event->elementLength('area'));
			$event->grp       = mb_substr($buffer[3], 0, $event->elementLength('grp'));
			$event->address   = mb_substr($buffer[4], 0, $event->elementLength('address'));
			$event->city      = mb_substr($buffer[5], 0, $event->elementLength('city'));
			$event->state     = mb_substr($buffer[6], 0, $event->elementLength('state'));
			$event->zip       = mb_substr($buffer[7], 0, $event->elementLength('zip'));
			$event->type      = mb_substr($buffer[8], 0, $event->elementLength('type'));
			$event->geo       = NULL;

			$event->save();
		}

		fclose($myfile);

        $this->eventList();
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

        $this->showString(print_r($event, true));
    }

}
