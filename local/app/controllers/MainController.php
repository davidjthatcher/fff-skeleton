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
    protected $api_options = array(
        'debug'           => false,
        'return_as_array' => false,
        'validate_url'    => false,
        'timeout'         => 30,
        'ssl_verify'      => false );

    /**
     * Renders the dashboard view template
     *
     * @return void
     */
    function render()
    {
        $this->updateLocalOrderArray();
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
        // Get remote orders from SESSION variable
        $orders = $this->f3->get("SESSION.orders");

        $bobj = Bookings::instance();
        $bookings = $bobj -> getBookingList($orders);

        $this->f3->set('bobj', $bobj);
        $this->f3->set('bookings', $bookings);
        $this->f3->set('view', 'bookingList.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }

    function displayBookingSummary()
    {
        // Get remote orders from SESSION variable
        $orders = $this->f3->get("SESSION.orders");

        $bobj = Bookings::instance();
        $bookings = $bobj -> getBookingSummary($orders);
        // echo json_encode( $bookings );
        $totals   = $bobj -> getBookingSummaryTotals($bookings);

        $this->f3->set('bobj', $bobj);
        $this->f3->set('bookings', $bookings);
        $this->f3->set('totals', $totals);
        $this->f3->set('view', 'bookingSummary.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }

    function displayBookingTotals()
    {
        // Get remote orders from SESSION variable
        $orders = $this->f3->get("SESSION.orders");

        $bobj = Bookings::instance();
        $bookings = $bobj -> getBookingSummary($orders);
        // echo json_encode( $bookings );
        $totals   = $bobj -> getBookingSummaryTotals($bookings);

        $this->f3->set('bobj', $bobj);
        $this->f3->set('bookings', $bookings);
        $this->f3->set('totals', $totals);
        $this->f3->set('view', 'bookingTotals.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }

    function displayBookingDate()
    {
        // Get remote orders from SESSION variable
        $orders = $this->f3->get("SESSION.orders");

        $query = $this->f3->get('QUERY');
        $qvars = array();
        parse_str($query, $qvars);

        $bobj = Bookings::instance();
        $bookings = $bobj -> getBookingsForDate($orders, $qvars['when'], $qvars['charter']);

        $totals   = $bobj -> getBookingSummaryTotals($bookings);

        $this->f3->set('bookingDate', $qvars['when']);
        $charterId = $qvars['charter'];
        $charterName = $bobj->getCharterName( $charterId );
        $this->f3->set('charterName', $charterName );

        $this->f3->set('bobj', $bobj);
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
        // Get remote orders from SESSION variable
        $orders = $this->f3->get("SESSION.orders");

        $this->f3->set('json', json_encode($orders));
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
     * Send REST Message Text
     * Get args from message text (json)
     * Show Response in json
     *
     * @return void
     */
    function sendRestMessage()
    {
        /** Get form input. **/
        $restMessage = $this->f3->get('POST.restMessage');

        $args = array ('fields' => 'id,status,total,coupon_lines');

        try {
            $client = new WC_API_Client( $this->f3->get('api_client_url'),
                $this->f3->get('api_consumer_key'),
                $this->f3->get('api_consumer_secret'),
                $this->api_options );

            $response = $client->orders->get( $restMessage, $args );
            $response = json_encode($response);

        } catch ( WC_API_Client_Exception $e ) {

            echo $e->getMessage() . PHP_EOL;
            echo $e->getCode() . PHP_EOL;

            if ( $e instanceof WC_API_Client_HTTP_Exception ) {

                print_r( $e->get_request() );
                print_r( $e->get_response() );
            }
        }

        $this->f3->set('json', $response);
        $this->f3->set('header', 'Rest Response');
        $this->f3->set('view', 'jsonList.htm');

        $template=new Template;
        echo $template->render('layout.htm');
    }    /**
     * Send REST Update Text and Data.
     * Show Response in json
     *
     * @return void
     */
    function sendRestUpdate() /* TBD */
    {
        /** Get form input. **/
        $restMessage = $this->f3->get('POST.restMessage');
        $restData = $this->f3->get('POST.restData');
        $restData = array ('status' => 'completed');

        $args = array ('fields' => 'id,status,total,coupon_lines');

        try {

            $client = new WC_API_Client( $this->f3->get('api_client_url'),
                $this->f3->get('api_consumer_key'), $this->f3->get('api_consumer_secret'), $this->api_options );

            // orders
            //print_r( $client->orders->get() );
            $order_id = '69227';
            $response = $client->orders->get( $order_id, $args );
            //$response = $client->orders->update_status( $order_id, 'completed' );
            $response = json_encode($response);
        } catch ( WC_API_Client_Exception $e ) {

            echo $e->getMessage() . PHP_EOL;
            echo $e->getCode() . PHP_EOL;

            if ( $e instanceof WC_API_Client_HTTP_Exception ) {

                print_r( $e->get_request() );
                print_r( $e->get_response() );
            }
        }

        $this->f3->set('json', $response);
        $this->f3->set('header', 'Rest Response');
        $this->f3->set('view', 'jsonList.htm');

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
    /**
     * Provide list of emails for booking advisory messages
     *
     * @return void
     */
    function displayBookingEmails()
    {
        $emails = $this->f3->get("POST.emailListForDay");

        $this->f3->set('json', $emails);
        $this->f3->set('header', 'Booking Emails');
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
        // Get remote orders from SESSION variable
        $orders = $this->f3->get("SESSION.orders");

        $bobj = Bookings::instance();
        $bookings = $bobj -> getBookingList($orders);

        $this->f3->set('json', json_encode($bookings));
        $this->f3->set('header', 'Bookings Json');
        $this->f3->set('view', 'jsonList.htm');

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
    function sendOrderComplete( $order_id )
    {
        $args = array ('fields' => 'id,status,total,coupon_lines');

        try {
            $client = new WC_API_Client( $this->f3->get('api_client_url'),
                $this->f3->get('api_consumer_key'),
                $this->f3->get('api_consumer_secret'),
                $this->api_options );

            $response = $client->orders->update_status( $order_id, 'completed' );
            $response = json_encode($response);

        } catch ( WC_API_Client_Exception $e ) {

            echo $e->getMessage() . PHP_EOL;
            echo $e->getCode() . PHP_EOL;

            if ( $e instanceof WC_API_Client_HTTP_Exception ) {

                print_r( $e->get_request() );
                print_r( $e->get_response() );
            }
        }

        return $response;
    }

    /**
     * Send complete order completed request to WooCommerce API
     * @id from http query vars
     * @return none
     */
    function bookingComplete()
    {
        $qvars = array();

        $query = $this->f3->get('QUERY');
        parse_str($query, $qvars);

        $orderId = $qvars['id'];

        $response = $this->sendOrderComplete($orderId);

        $this->displayBookingSummary();
    }
    /**
     * Get array of order ids from POST form
     * Complete each, update local order and refresh summary view
     * @ids[] from http POST data
     * @return none
     */
    function bookingDayComplete()
    {
        $orderIdJson = $this->f3->get('POST.bookingsForDay');

        $orderIds = json_decode($orderIdJson);

        foreach( $orderIds as $orderId ) {
            $response = $this->sendOrderComplete($orderId);
            sleep(1);       // Don't over run the system
        }

        $this->render();
    }

    /**
     * Send request to WooCommerce API to update local orders array
     *
     * SESSION varilabe holds array from remote site for fast processing of
     * Order/Booking Views
     *
     * @return none
     */
    function updateLocalOrderArray()
    {
        // Get orders from WC API for Booking Process
        $orders = $this->getRemoteOrderArray();

        // Save remote orders in SESSION variable for other views
        $this->f3->set("SESSION.orders", $orders);
    }
    /**
     * Get Orders Array from Remote System
     *
     * @return remote Json response in array format
     */
    function getRemoteOrderArray()
    {
        $response = $this->getRemoteOrdersJson($this->f3->get('SESSION.order_status'));

        return json_decode($response, true);
    }
    /**
     * Send request to WooCommerce API for active orders (status == processing)
     * @status for processing, completed, cancelled, ... orders
     * @return response json
     */
    function getRemoteOrdersJson($status)
    {
        $args = array ('filter[created_at_min]' => $this->f3->get('SESSION.order_start_date'),
                       'filter[limit]' => '500',
                       'fields' => 'id,status,total,customer,line_items,coupon_lines,created_at',
                       'status' => $status);

        try {
            $client = new WC_API_Client( $this->f3->get('api_client_url'),
                $this->f3->get('api_consumer_key'),
                $this->f3->get('api_consumer_secret'),
                $this->api_options );

            $response = $client->orders->get( '', $args );
            $response = json_encode($response);

        } catch ( WC_API_Client_Exception $e ) {

            echo $e->getMessage() . PHP_EOL;
            echo $e->getCode() . PHP_EOL;

            if ( $e instanceof WC_API_Client_HTTP_Exception ) {

                print_r( $e->get_request() );
                print_r( $e->get_response() );
            }
        }
        // Write file to tmp for review
        if($this->f3->get('DEBUG') > 0) {
            $file = fopen( "tmp/orders.json", "w+");
            fwrite( $file, $response);
            fclose( $file);
        }
        return $response;
    }
}
