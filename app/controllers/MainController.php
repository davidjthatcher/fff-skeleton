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
        $this->displayBookingSummary();
    }

    /**
     * Renders the messages view template with f3 <repeat>
     *
     * @return void
     */
    function displayBookingList()
    {
        // Get orders from WC API for Booking Process
        $orders = $this->getOrderArray($this->f3);

        $bobj = new Bookings();
        $bookings = $bobj -> getBookingList($orders);    

        $this->f3->set('bookings', $bookings);
        $this->f3->set('view', 'bookingList.htm');

        $template=new Template;
        echo $template->render('layout.htm');        
    }

    function displayBookingSummary()
    {
        // Get orders from WC API for Booking Process
        $orders = $this->getOrderArray($this->f3);

        $bobj = new Bookings();
        $bookings = $bobj -> getBookingSummary($orders);    
        $totals   = $bobj -> getBookingSummaryTotals($bookings);    

        $this->f3->set('bookings', $bookings);
        $this->f3->set('totals', $totals);
        $this->f3->set('view', 'bookingSummary.htm');

        $template=new Template;
        echo $template->render('layout.htm');        
    }

    function displayBookingDate()
    {
        // Get orders from WC API for Booking Process
        $orders = $this->getOrderArray($this->f3);

        $params = $this->f3->get('PARAMS');

		$date = substr(strchr($params[0], "="), 1);
        echo $date;
        $bobj = new Bookings();
        $bookings = $bobj -> getBookingsForDate($orders, $date);    
        $totals   = $bobj -> getBookingSummaryTotals($bookings);    

        $this->f3->set('bookingDate', $date);
        $this->f3->set('bookings', $bookings);
        $this->f3->set('totals', $totals);
        $this->f3->set('view', 'bookingDate.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }

    /**
     * Provide json api representation of order data
     * Order data is the raw information we get from WC API
     *
     * @return void
     */
    function displayOrderJson() 
    {
        // Get orders from WC API for Booking Process
        $orders = $this->getOrderJson($this->f3);

        $this->f3->set('json', $orders);
        $this->f3->set('header', 'Order Json');
        $this->f3->set('view', 'jsonList.htm');

        $template=new Template;
        echo $template->render('layout.htm');
	}

    /**
     * Provide json api representation of booking data
     * The Booking information I need is processed and condensed for our fishing trips.
     *
     * @return void
     */
    function displayBookingJson() 
    {
        // Get orders from WC API for Booking Process
        $orders = $this->getOrderArray($this->f3);

        $bobj = new Bookings();
        $bookings = $bobj -> getBookingList($orders);    

        $this->f3->set('json', json_encode($orders));
        $this->f3->set('header', 'Bookings Json');
        $this->f3->set('view', 'jsonList.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }
    
    /**
     * Renders the messages view template with AJAX
     *
     * @return void
     */    
    function displayMessagesAjaxView() 
    {
        $this->f3->set('view', 'messagesajax.htm');
        $template=new Template;
        echo $template->render('layout.htm');          
    }
    /**
     * All Following Imported from David's Bookings API Baseline
     */
    /*
     *  Send API request.
     *  Return: API Report Filtered
     */

    /**
     * Send request to WooCommerce API for active orders (status == processing)
     * @status TBD for completed, cancelled, ... orders
     * @return response json
     */
    function getOrderJson($f3) {

        $oauth = new OAuth($f3->get('api_consumer_key'),
                           $f3->get('api_consumer_secret'),
                           OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
        // Send request to fsa server per current query
        $oauth->fetch($f3->get('api_url').'/orders?filter[limit]=500',
                      array ('fields' => 'id,status,total,customer,line_items',
                             'status' => 'processing'));

        $response = $oauth->getLastResponse();

        // Write file to tmp for review
        if($f3->get('DEBUG') > 0) {
            $file = fopen( "tmp/orders.json", "w+");
            fwrite( $file, $response);
            fclose( $file);
        }
        return $response;
    }

    /**
     * Send request to WooCommerce API for active orders (status == processing)
     *
     * @return response in array format
     */
    function getOrderArray($f3) {
        $response = $this->getOrderJson($f3);

        return json_decode($response, true);
    }
}
