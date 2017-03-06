<?php

class BookingsModelTest extends Controller {

	function bookingTestSuite($test, $description, $input, $expectedBookingsList)
	{
		$orders = json_decode(file_get_contents($input), true);
		$expected = json_decode(file_get_contents($expectedBookingsList), true);
		$expectedBookings = $expected['bookings'];

		//$test->expect(true, $description.', DEBUG Expected: '.json_encode($expected, JSON_PRETTY_PRINT)); // Use this for debugging test input

		$bobj = New Bookings();
		$bookingsList = $bobj->getBookingList($orders);

		$test->expect(
			(count($expectedBookings) == count($bookingsList)),
			$description.': Bookings count = '.count($expectedBookings).', '.count($bookingsList)
		);

		$bookingsSummary = $bobj->getBookingSummary($orders);
		$test->expect(
			true,
			$description.': Trip Summary '.count($bookingsSummary).' - '.json_encode($bookingsSummary[0], JSON_PRETTY_PRINT)
		);
		// Verify totals for Adults, Child, Rods, $Revenue totals
		$bookingsTotals = $bobj->getBookingSummaryTotals($bookingsList);
		$expectedTotals = $bobj->getBookingSummaryTotals($expectedBookings);

		$test->expect(
			($expectedTotals['Adults']   == $bookingsTotals['Adults']) &&
            ($expectedTotals['Children'] == $bookingsTotals['Children']) &&
            ($expectedTotals['Rods']     == $bookingsTotals['Rods']),
			$description.': Adults: '.$expectedTotals['Adults'].', '.$bookingsTotals['Adults'].
						 ': Children: '.$expectedTotals['Children'].', '.$bookingsTotals['Children'].
						 ': Rods: '.$expectedTotals['Rods'].', '.$bookingsTotals['Rods']
		);
		$test->expect(
            (abs($expectedTotals['total'] - $bookingsTotals['total']) < 0.01),
			$description.': total: '.$expectedTotals['total'].', '.$bookingsTotals['total']
		);
		$test->expect(
            (abs($expectedTotals['fsa_take'] - $bookingsTotals['fsa_take']) < 0.01),
			$description.': fsa_take: '.$expectedTotals['fsa_take'].', '.$bookingsTotals['fsa_take']
		);
		$test->expect(
            (abs($expectedTotals['fsa_total'] - $bookingsTotals['fsa_total']) < 0.01),
			$description.': fsa_total: '.$expectedTotals['fsa_total'].', '.$bookingsTotals['fsa_total']
		);
		$test->expect(
            (abs($expectedTotals['itemsTotal'] - $bookingsTotals['itemsTotal']) < 0.01),
			$description.': itemsTotal: '.$expectedTotals['itemsTotal'].', '.$bookingsTotals['itemsTotal']
		);
	}

	function get($f3) {
		$test=new \Test;
    	$testing = "Bookings Model: ";

		$test->expect(
			is_null($f3->get('ERROR')),
			$testing . ' Starting '
		);

		$test->expect(
			true,
			$testing. ' Methods(FYI): '.json_encode(get_class_methods($bobj), JSON_PRETTY_PRINT)
		);
		// Get stored test input data for Bookings Model Input/Output Tests
		// Bookings are created from Order input files. Test Suites takes description,
		// input file name, expected output file name.
		//
		// Basic Suites convers
		//		getBookingList
		//		getBookingSummary
		//		getBookingSummaryTotals
		// Booking Coverage (Order Types)
		// 		Charters = Full Day 67077, Half Day 68316
		// 		Adults [Children]
		// 		Coupons null, groupon
		// 		Rod Rentals
		//      Multiple booking dates (73162)
		//      Multiple line items, one date (72677)
		$this->bookingTestSuite($test,
			$testing.'Order Set 1',
			'app/testdata/orders1-input.json',
			'app/testdata/orders1-output.json');

		// Order 72677 should result in 1 Booking item not 2
		//$test_input = file_get_contents('app/testdata/orders-72677-input.json');
		$this->bookingTestSuite($test,
			$testing.'Order 72677',
			'app/testdata/orders-72677-input.json',
			'app/testdata/orders-72677-output.json');

		// Order 73162 should result in 3 Booking items at $140 + tax each ($161).
		//             should not be closed until 3rd trip complete.
		$this->bookingTestSuite($test,
			$testing.'Order 73162',
			'app/testdata/orders-73162-input.json',
			'app/testdata/orders-73162-output.json');

		// Test Important Bookings Keys
		$bobj = New Bookings();
		$orders = json_decode(file_get_contents('app/testdata/orders-72677-input.json'), true);
		$bookingsList = $bobj->getBookingList($orders);

		$requiredKeys = array("status", "id", "created_at", "total", "first_name", "last_name",
			"email", "phone", "coupon", "fsa_take", "Rods", "CharterId", "itemsTotal",
			"Adults", "Children", "Date", "fsa_total", "number_of_bookings");
		$currentKeys = array(); $k=0;
		foreach($bookingsList[0] as $key => $value) {
			$currentKeys[$k++] = $key;
		}
		$test->expect(
			$requiredKeys == $currentKeys,
			$testing.' Required Booking Keys '.json_encode($currentKeys, JSON_PRETTY_PRINT)
		);
		//
		$test->expect(
			false,
			$testing.' TBD: How to test live data? Here or Maint Controller?'
		);

		$f3->set('results',$test->results());
	}

	function afterroute() {
		//echo \Preview::instance()->render('testresults.htm');
        $this->f3->set('view', 'testresults.htm');

        $template=new Template;
        echo $template->render('layout.htm');
	}
}
