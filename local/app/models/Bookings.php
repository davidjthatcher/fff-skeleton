<?php
/*
 * Bookings Class parses order arrays for varios booking reports
 */
class Bookings extends \Prefab {
    function __construct( ) {

    }
    /*
     * Fitler to sort csv output
     * Sort by date, charter id, booking id
     */
    private function cmpOrderItems( $row1, $row2 ) {
        $date1 = new DateTime($row1['Date']);
        $date2 = new DateTime($row2['Date']);

        if( $date1 == $date2 ) {

            if( $row1['CharterId'] == $row2['CharterId'] ) {
                return( $row1['id'] >= $row2['id'] ? 1 : -1);
            } else {
                return( $row1['CharterId'] >= $row2['CharterId'] ? 1 : -1);
            }
        } else {
            return( $date1 >= $date2 ? 1 : -1);
        }
    }

    private function formatPhoneNumber( $num ) {
        //first strip all the non-digit characters from the input
        $num = preg_replace("/[^0-9]+/", "", $num);

        //and only then re-format the phone-number
        if(!empty($num)){
          $first=  substr($num, 0, 3)."-";
          $second= substr($num,3,3)."-";
          $third=substr($num,6,4);
          $num=$first.$second.$third;
        }
        return $num;
    }
    /*
     * Get the information we want from meta.
     */
    public function getMetaDetail($metas) {
        $myMeta = array();
        // Set Defaults
        $myMeta['Adults'] = 0;
        $myMeta['Children'] = 0;
        $myMeta['Date'] = "01/01/2016";

        // meta data is stored with key, value as "keys"
        foreach($metas as $meta) {
            //echo "<p>" . json_encode($meta) . "</p>";
            switch ($meta['key']) {
                case 'Booking Date':
                        $myMeta['Date'] = $meta['value'];
                    break;
                case 'Adults':
                case 'Persons':
                        $myMeta['Adults'] = intval($meta['value']);
                    break;
                case 'Children':
                        $myMeta['Children'] = intval($meta['value']);
                    break;
            }
        }
        return($myMeta);
    }

    /*
     * Get the information we want from line items.
     * Generally a single item but can be several.
     * Combine ROD rental w/charter booking
     */
    /*  Implement a State Machine
     *  States:         Init,       CH,             CH-w-RR,        RR
     *
     *  Events: RR      Set RR/     Set RR/         Add RR/         Add RR/
     *                  RR          CH-w-RR         CH-w-RR         RR
     *
     *          CH      Set CH +    Save CH +       Save CH +       Set CH
     *                  Init RR/    Set CH/         Set CH/
     *                  CH          INIT            INIT            CH-w-RR
     */
    const   ST_INIT  = 0;
    const   ST_CH    = 1;
    const   ST_RR    = 2;
    const   ST_CH_RR = 3;

    public function getLineItemsDetail($lineItems) {
        $myItems = array();

        $state = ST_INIT;
        for ($i=0, $j=0; $i < count($lineItems); $i++) {
            // Implement State Machine
            if('ROD' == $lineItems[$i]['sku']) {
                // Event Rod Rental (RR)
                switch ($state) {
                    case ST_INIT:
                        $state = ST_RR;
                        $myItems[$j]['Rods']  = intval($lineItems[$i]['quantity']);
                        $myItems[$j]['itemsTotal'] = floatval($lineItems[$i]['total']);
                        break;
                    case ST_CH:
                        $state = ST_CH_RR;
                        // Set RR
                        $myItems[$j]['Rods']  = intval($lineItems[$i]['quantity']);
                        $myItems[$j]['itemsTotal'] += floatval($lineItems[$i]['total']);
                        break;
                    case ST_CH_RR:
                    case ST_RR:
                        // Add RR
                        $myItems[$j]['Rods']  += intval($lineItems[$i]['quantity']);
                        $myItems[$j]['itemsTotal'] += floatval($lineItems[$i]['total']);
                        break;
                }
            } else {
                // Event Charter (CH)
                switch ($state) {
                    case ST_INIT:
                        $state = ST_CH;
                        // Init RR
                        $myItems[$j]['Rods'] = intval(0);
                        // Set CH
                        $myItems[$j]['CharterId']  = $lineItems[$i]['product_id'];
                        $myItems[$j]['itemsTotal']     += floatval($lineItems[$i]['total']);
                        /* Parse meta for information we want */
                        $myItems[$j] += $this -> getMetaDetail($lineItems[$i]['meta']);
                        break;
                    case ST_CH:
                        // Init RR
                        $myItems[$j]['Rods'] = intval(0);
                    case ST_CH_RR:
                        $state = ST_INIT;
                        // Save CH
                        $j++;
                        // Init RR
                        $myItems[$j]['Rods'] = intval(0);
                        // Set CH
                        $myItems[$j]['CharterId']  = $lineItems[$i]['product_id'];
                        $myItems[$j]['itemsTotal']     += floatval($lineItems[$i]['total']);
                        /* Parse meta for information we want */
                        $myItems[$j] += $this -> getMetaDetail($lineItems[$i]['meta']);
                        //echo '<p>' . var_dump($myItem[$j]) . '</p>';
                        break;
                    case ST_RR:
                        $state = ST_CH_RR;
                        // Set CH
                        $myItems[$j]['CharterId']  = $lineItems[$i]['product_id'];
                        $myItems[$j]['itemsTotal']     += floatval($lineItems[$i]['total']);
                        /* Parse meta for information we want */
                        $myItems[$j] += $this -> getMetaDetail($lineItems[$i]['meta']);
                        break;
                }
            }
        }
        return($myItems);
    }

    /*
     * Process array of orders. An order will conist of one or more
     *     line items. Note that this function is used to build list for
     *     getBookingSummary and getBookinsForDate.
     * Return array entry per booking entry
     * Add ROD Rental to associated booking
     * TBD total $ vs sub-total $ for order with multi-bookings.
     */
    public function getBookingList($ordersArray) {
        $bookings = array();
        $rodRentals = array();

        $orders = $ordersArray['orders'];
        $i = 0;
        foreach ($orders as $order){
            $booking = array();
            $items = array();
            //echo var_dump($order);
            $booking['status']     = $order['status'];
            $booking['id']         = $order['id'];
            $booking['Total']      = $order['total'];
            $booking['first_name'] = $order['customer']['first_name'];
            $booking['last_name']  = $order['customer']['last_name'];
            $booking['email']  = $order['customer']['email'];
            $booking['phone']      = $order['customer']['billing_address']['phone'];
            $booking['phone']      = $this->formatPhoneNumber($booking['phone']);
            // booking order may have more than one item
            $items = $this -> getLineItemsDetail($order['line_items']);

            foreach ($items as $item){
                $bookings[$i++] = $booking + $item;
            }
        }

        usort( $bookings, 'Bookings::cmpOrderItems' );

        //echo '<p>' . json_encode($details) . '</p>';
        return ($bookings);
    }
    /*
     *  Get trip summary for each date with orders from booking detail.
     *  For each booking date, calcuate total Adults, Children, $
     *  Assumption: $detail list is ordered by 'Booking Date'
     */
    public function getBookingSummary($orders){
        $summary = array();

        $bookings = $this -> getBookingList($orders);

        $summary[0]['Date']      = $bookings[0]['Date'];
        $summary[0]['CharterId'] = $bookings[0]['CharterId'];
        $summary[0]['Adults']    = $bookings[0]['Adults'];
        $summary[0]['Children']  = $bookings[0]['Children'];
        $summary[0]['Rods']      = $bookings[0]['Rods'];
        $summary[0]['Total']     = $bookings[0]['Total'];

        for ($i = 1, $j = 0; $i < count($bookings); $i++) {

            if(($summary[$j]['Date']      == $bookings[$i]['Date']) &&
               ($summary[$j]['CharterId'] == $bookings[$i]['CharterId']))
            {
                //echo '<p>' . '==' . $bookings[$i]['Date'] . '</p>';
                $summary[$j]['Adults']    += $bookings[$i]['Adults'];
                $summary[$j]['Children']  += $bookings[$i]['Children'];
                $summary[$j]['Rods']      += $bookings[$i]['Rods'];
                $summary[$j]['Total']     += $bookings[$i]['Total'];
            } else {
                ++$j;
                // Get next
                $summary[$j]['Date']      = $bookings[$i]['Date'];
                $summary[$j]['CharterId'] = $bookings[$i]['CharterId'];
                $summary[$j]['Adults']    = $bookings[$i]['Adults'];
                $summary[$j]['Children']  = $bookings[$i]['Children'];
                $summary[$j]['Rods']      = $bookings[$i]['Rods'];
                $summary[$j]['Total']     = $bookings[$i]['Total'];
            }
        }
        return($summary);
    }

    /*
     *  Get trip summary from booking detail.
     *  For each booking date, calcuate Total Adults, Children, $
     *  Assumption: $detail list is ordered by 'Booking Date'
     */
    public function getBookingSummaryTotals($summary){
        $totals = array();

        foreach($summary as $daily) {
            $totals['Adults']   += $daily['Adults'];
            $totals['Children'] += $daily['Children'];
            $totals['Rods']     += $daily['Rods'];
            $totals['Total']    += $daily['Total'];
            $totals['itemsTotal'] += $daily['itemsTotal'];
        }
        return $totals;
    }
    /*
     * Return each booking entry for a given date
     */
    public function getBookingsForDate($orders, $date, $charter) {
        $bookingsForDate = array();

        $bookings = $this -> getBookingList($orders);

        // Straight compare below did not work. Have to convert first
        $myDate = new DateTime($date);

        $i = 0;
        foreach ($bookings as $booking){
            if(($charter == $booking['CharterId']) and
               ($myDate == new DateTime($booking['Date']))) {
                //echo '<p>' . json_encode($booking) . '</p>';
                $bookingsForDate[$i++] = $booking;
            }
        }
        return ($bookingsForDate);
    }

    /*
     * Return for each booking entry for a given date
     */
    public function getEmailsForDate($orders, $date, $charter) {
        $emailsForDate = '';

        $bookings = $this -> getBookingList($orders);

        // Straight compare below did not work. Have to convert first
        $myDate = new DateTime($date);

        $i = 0;
        foreach ($bookings as $booking){
            if(($charter == $booking['CharterId']) and
               ($myDate == new DateTime($booking['Date']))) {
                $emailsForDate = $emailsForDate . ';' .$booking['email'];
            }
        }

        return ($emailsForDate);
    }
    /*
     * Return Chater Name for given Product Id
     * TBD on how to make this available at View Creation.
     */
    public function getCharterName($charterId) {
        $charterNames = array(
            '68316' => 'MSA 5',
            '67077' => 'MSA 10',
            '67952' => 'MSA 14',
            '68532' => 'MSA 17',
            '67538' => 'MSA 30',
            '67231' => 'MSA 4th',
            '66936' => 'Reg 6',
            '66636' => 'Reg 6',
            '66635' => 'Reg 8',
            '66638' => 'Reg 10',
            '66639' => 'Reg 12'
        );

        if(array_key_exists( $charterId, $charterNames )) {
            $charterName = $charterNames[$charterId];
        } else {
            $charterName = 'Other';
        }

        return($charterName);
    }
}
?>
