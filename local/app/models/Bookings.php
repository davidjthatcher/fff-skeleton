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
            // Remove 1 in first position (i.e. long distance)
            $num = preg_replace("/^1/", "", $num);

            $first  = substr($num, 0, 3)."-";
            $second = substr($num,3,3)."-";
            $third  = substr($num,6,4);
            $num    = $first.$second.$third;
        }
        return $num;
    }
    /*
     * Estimate Groupon Revenue based on charter type, persons, rod rental.
     *  MSA 5 Hour (68316) w/out Rod Rental = 32 %
     *  MSA 5 Hour (68316) with Rod Rental  = 38 %
     *  MSA 10 Hour (67077) with Rod Rental = 47 %
     *  All other = 100 %
     */
    private function estimateGrouponRevenue( $value, $charterId, $rods )
    {
        switch ( $charterId ) {

            case '68316':
                if( 0 == $rods ) {
                    $ourPercent = 0.32;
                } else {
                    $ourPercent = 0.38;
                }
                break;

            case '67077':
                $ourPercent = 0.47;
                break;

            default:
                $ourPercent = 1.0;
                break;
        }

        $ourTake = $value * $ourPercent;

        return( $ourTake );
    }
    /*
     * Get the information we want from meta.
     */
    private function getMetaDetail($metas) {
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
     * Combine ROD rental (RR) w/charter booking (CH)
     */
    /*  Implement a State Machine
     *  States:         ST_INIT,    ST_CH,          ST_CH_RR,       ST_RR
     *
     *  Events: RR      Set RR/     Set RR/         Add RR/         Add RR/
     *                  ST_RR       ST_CH_RR        ST_CH_RR        ST_RR
     *
     *          CH      Set CH +    Save CH +       Save CH +       Set CH/
     *                  Init RR/    Set CH/         Set CH/
     *                  ST_CH       ST_CH           ST_CH           ST_CH_RR
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
                /* Parse meta for information we want */
                $meta = $this -> getMetaDetail($lineItems[$i]['meta']);
                switch ($state) {
                    case ST_INIT:
                        $state = ST_CH;
                        // Init RR
                        $myItems[$j]['Rods'] = intval(0);
                        // Set CH
                        $myItems[$j]['CharterId']  = $lineItems[$i]['product_id'];
                        $myItems[$j]['itemsTotal'] = floatval($lineItems[$i]['total']);
                        $myItems[$j] += $meta;
                        break;

                    case ST_CH:
                    case ST_CH_RR:
                        if(($myItems[$j]['Date'] == $meta['Date']) &&
                           ($myItems[$j]['CharterId'] == $lineItems[$i]['product_id']))
                        {
                            // Additional people for booking
                            $myItems[$j]['Adults']   += $meta['Adults'];
                            $myItems[$j]['Children'] += $meta['Children'];
                            $myItems[$j]['itemsTotal'] += floatval($lineItems[$i]['total']);
                        } else {
                            // Save CH if new date.
                            $state = ST_CH;
                            $j++;
                            // Init RR
                            $myItems[$j]['Rods'] = intval(0);
                            $myItems[$j]['CharterId']  = $lineItems[$i]['product_id'];
                            $myItems[$j]['itemsTotal'] = floatval($lineItems[$i]['total']);
                            $myItems[$j] += $meta;
                        }
                        break;

                    case ST_RR:
                        $state = ST_CH_RR;
                        // Set CH
                        $myItems[$j]['CharterId']  = $lineItems[$i]['product_id'];
                        $myItems[$j]['itemsTotal'] += floatval($lineItems[$i]['total']);
                        $myItems[$j] += $meta;
                        break;
                } /* End switch state */
            
            } /* End if event = CH */

            //echo '<p>Line items('.$i.', '.$j.'): '.$lineItems[$i]['id'].' '.json_encode($myItems[$j], JSON_PRETTY_PRINT).'</p>';
        } /* End for each line item */

        return($myItems);
    }

    /*
     * Process array of orders. An order will conist of one or more
     *     line items. Note that this function is used to build list for
     *     getBookingSummary and getBookinsForDate.
     * Return array entry per booking entry
     * Add ROD Rental to associated booking
     * DJT 03/06/2010 Use Line items total $ with multiple-bookings.
     *      Save the number_of_bookings for use with close order issue.
     */
    public function getBookingList($ordersArray) {
        $bookings = array();
        $rodRentals = array();
        $gratuity = 1.15;

        $orders = $ordersArray['orders'];
        $i = 0;
        foreach ($orders as $order){
            $booking = array();
            $items = array();
            //echo var_dump($order['coupon_lines'][0]);
            $booking['status']     = $order['status'];
            $booking['id']         = $order['id'];
            $booking['created_at'] = $order['created_at'];
            $booking['total']      = $order['total'];
            $booking['first_name'] = $order['customer']['first_name'];
            $booking['last_name']  = $order['customer']['last_name'];
            $booking['email']      = $order['customer']['email'];
            $booking['phone']      = $order['customer']['billing_address']['phone'];
            $booking['phone']      = $this->formatPhoneNumber($booking['phone']);
            // add coupon, FSA take will be % of 'amount' and combined revenue field
            $booking['coupon']     = $order['coupon_lines'][0]['code'];
            $booking['fsa_take']   = $order['coupon_lines'][0]['amount'];

            // order may occasionally have more than one booking line_item
            $items = $this -> getLineItemsDetail($order['line_items']);
            $numberOfItems = sizeof($items);
            foreach ($items as $item){
                $bookings[$i] = $booking + $item;

                $bookings[$i]['fsa_take']  = $this->estimateGrouponRevenue(
                    $bookings[$i]['fsa_take'], $bookings[$i]['CharterId'], $bookings[$i]['Rods']);

                if($numberOfItems > 1) {
                    $bookings[$i]['total'] = $gratuity * $bookings[$i]['itemsTotal'];
                }
                $bookings[$i]['fsa_total']  = $bookings[$i]['fsa_take'] + $bookings[$i]['total'] ;
                $bookings[$i]['number_of_bookings'] = $numberOfItems;

                $i++;
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
        $summary[0]['total']     = $bookings[0]['total'];
        $summary[0]['fsa_take']  = $bookings[0]['fsa_take'];
        $summary[0]['fsa_total'] = $bookings[0]['fsa_total'];

        for ($i = 1, $j = 0; $i < count($bookings); $i++) {

            if(($summary[$j]['Date']      == $bookings[$i]['Date']) &&
               ($summary[$j]['CharterId'] == $bookings[$i]['CharterId']))
            {
                //echo '<p>' . '==' . $bookings[$i]['Date'] . '</p>';
                $summary[$j]['Adults']    += $bookings[$i]['Adults'];
                $summary[$j]['Children']  += $bookings[$i]['Children'];
                $summary[$j]['Rods']      += $bookings[$i]['Rods'];
                $summary[$j]['total']     += $bookings[$i]['total'];
                $summary[$j]['fsa_take']  += $bookings[$i]['fsa_take'];
                $summary[$j]['fsa_total'] += $bookings[$i]['fsa_total'];
            } else {
                ++$j;
                // Get next
                $summary[$j]['Date']      = $bookings[$i]['Date'];
                $summary[$j]['CharterId'] = $bookings[$i]['CharterId'];
                $summary[$j]['Adults']    = $bookings[$i]['Adults'];
                $summary[$j]['Children']  = $bookings[$i]['Children'];
                $summary[$j]['Rods']      = $bookings[$i]['Rods'];
                $summary[$j]['total']     = $bookings[$i]['total'];
                $summary[$j]['fsa_take']  = $bookings[$i]['fsa_take'];
                $summary[$j]['fsa_total'] = $bookings[$i]['fsa_total'];
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
            $totals['total']    += $daily['total'];
            $totals['fsa_take']   += $daily['fsa_take'];
            $totals['fsa_total']  += $daily['fsa_total'];
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
     * Return order Id from array of bookings
     */
    public function getIdsJson($bookings) {
        $ids = array();

        $i = 0;
        foreach ($bookings as $booking){
            $ids[$i++] = $booking['id'];
        }

        return(json_encode($ids));
    }
    /*
     * Return email from array of bookings
     */
    public function getEmailList($bookings) {
        $emailList = '';

        foreach ($bookings as $booking){
            $emailList .= $booking['email'] . '; ';
        }

        //return($emailList);
        return(json_encode($emailList));
    }
    /*
     * Return Chater Name for given Product Id
     * TBD on how to make this available at View Creation.
     */
    public function getCharterName($charterId) {
        $charterNames = array(
            '68316' => 'MSA 5',
            '67077' => 'MSA 12',
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
