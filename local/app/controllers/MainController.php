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
     * Renders the messages view template with f3 <repeat>
     *
     * @return void
     */
    function displayBookingList()
    {
        // Get orders from WC API for Booking Process
        $orders = $this->getOrderArray($this->f3);

        $bobj = Bookings::instance();
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

        $bobj = Bookings::instance();
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

        $query = $this->f3->get('QUERY');
        $qvars = array();
        parse_str($query, $qvars);

        $bobj = Bookings::instance();
        $bookings = $bobj -> getBookingsForDate($orders, $qvars['when'], $qvars['charter']);
        $totals   = $bobj -> getBookingSummaryTotals($bookings);

        $this->f3->set('bookingDate', $qvars['when']);
        $this->f3->set('charterId', $qvars['charter']);
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
        $orderStatus = $this->f3->get('order_status');
        $orders = $this->getOrdersJson($this->f3, $orderStatus);

        $this->f3->set('json', $orders);
        $this->f3->set('header', 'Orders Json');
        $this->f3->set('view', 'jsonList.htm');

        $template=new Template;
        echo $template->render('layout.htm');
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
     * Get Order Configuration Settings from orderSettings.htm
     * TBD on run time variables only read once from config.ini
     * @return void
     */
    function saveOrderSettings()
    {
        /** Get form input. **/
        $orderStatus = $this->f3->get('POST.orderStatus');
        $orderStartDate = $this->f3->get('POST.orderStartDate');

        /** Update config variables. **/
        $this->f3->set('order_status', $orderStatus);
        $this->f3->set('order_start_date', $orderStartDate);

        $this->f3->set('header', 'Order Settings - Updated');
        $this->f3->set('view', 'orderSettings.htm');

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

        $bobj = Bookings::instance();
        $bookings = $bobj -> getBookingList($orders);

        $this->f3->set('json', json_encode($bookings));
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
     * @status for processing, completed, cancelled, ... orders
     * @return response json
     */
    function getOrdersJson($f3, $status) {

        $oauth = new OAuth($f3->get('api_consumer_key'),
                           $f3->get('api_consumer_secret'),
                           OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);

        // Send request to fsa server per current query
        $oauth->fetch($f3->get('api_url').'/orders',
                      array ('filter[created_at_min]' => $f3->get('order_start_date'),
                             'filter[limit]' => '500',
                             'fields' => 'id,status,total,customer,line_items',
                             'status' => $status));

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

        $response = $this->getOrdersJson($f3, $this->f3->get('order_status'));

        return json_decode($response, true);
    }
}
