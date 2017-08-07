<?php

class EventModelTest extends Controller {

	function get($f3) {
		$test=new \Test;

		$test->expect(
			is_null($f3->get('ERROR')),
			'Starting Event Model Test'
		);

		$cntlr = New Controller();
		$test->expect(
			!empty($cntlr),
			'Controller Provides db handle for New Event Object Instance'
		);

		$event = New Event( $cntlr->db );
		$test->expect(
			!empty($event),
			'New Event Not Empty'
		);

		$test->expect(
			true,
			'Event Model Methods(FYI): '.json_encode(get_class_methods($event), JSON_PRETTY_PRINT)
		);

		$test->expect(
			true,
			'Event Model Fields(FYI): '.json_encode($event->fields(), JSON_PRETTY_PRINT)
		);

		$schema = $event->schema();
		foreach($schema as $key => $value) {
			$test->expect(
				true,
				'Schema ' . $key . ' => '. json_encode($value, JSON_PRETTY_PRINT)
			);
		}

		$test->expect(
			$event->elementLength('grp') == 50,
			'Event Model Element grp: '. $event->elementLength('grp')
		);
			
		$test->expect(
			$event->elementLength('dayofweek') == 3,
			'Event Model Element dayofweek: '. $event->elementLength('dayofweek')
		);
		$test->expect(
			'event' == $event->table(),
			'Table name is Model Name == event'
		);

		$test->expect(
			0 < $event->count(),
			'Must be one or more Events: '.$event->count()
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
