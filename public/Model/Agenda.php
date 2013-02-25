<?php


class Model_Agenda extends Model
{

	/**
	 * Directive ALLOW_UNLESS_DENIED means all public methods can be called from
	 * the client, if defined as pages. Use with care.
	 */
	public $pageaccess = PageController::ALLOW_UNLESS_DENIED;


	/**
	 * public array pagecredentials()
	 *      Returns a map action => permission, for all actions (ajax calls or
	 * pages) that require credentials. Actions not in this list are assumed to
	 * be public, unless @@restrict is true;
	 *
	 * @return array
	 */
	public function pagecredentials()
	{
		return [
		];
	}

	public function main($firstDay=NULL, $filters=[])
	{
		$filters += ['type' => '', 'user' => '', 'resched' => 1];

		/* If $firstDay is not given, start on last Monday */
		empty($firstDay) && ($firstDay = 0);

		/* If it's given as a date, or the format is wrong */
		if (!is_numeric($firstDay))
		{
			$dayNum = strtotime($firstDay);
			$firstDay = $dayNum ? ceil(($dayNum - time()) / 86400) : 0;
		}

		while (date('N', strtotime("{$firstDay} days")) != 1)
		{
			$firstDay--;
		}

		$range = ['ini' => date('Y-m-d', strtotime("{$firstDay} days")),
				  'end' => date('Y-m-d', strtotime(($firstDay + AGENDA_DAYS_TO_SHOW - 1).' days'))];

		$sqlFilter = ['type' => $filters['type'], 'user' => $filters['user']];

		$events = oSQL()->getEventsInfo(NULL, $range, array_filter($sqlFilter));

		# Get data and pre-process it
		$data = [];

		for ($i=$firstDay; $i < ($firstDay + AGENDA_DAYS_TO_SHOW); $i++)
		{
			$date = date('Y-m-d', strtotime("{$i} days"));
			$data[$date] = ['date'    => $date,
							'isToday' => !$i,
							'events'  => []];
		}

		# Fill days with events
		foreach ($events as $event)
		{
			$event['event'] = nl2br($event['event']);
			$data[substr($event['ini'], 0, 10)]['events'][] = $event;
		}

		foreach ($data as $day)
		{
			$days[] = $day;
		}

		# Filters
		$filterCfg = [
			'type' => ['name'    => 'Tipo',
					   'options' => ['' => '(todos)'] + View::get('EventType')->getHashData()],
			'user' => ['name'    => 'Usuario',
					   'options' => [''=>'(todos)'] + View::get('User')->getHashData()]
		];

		Template::one()->assign('data', isset($days) ? $days : array());
		Template::one()->assign('currFilters', $filters);
		Template::one()->assign('prev', $firstDay - 7);
		Template::one()->assign('next', $firstDay + 7);
		Template::one()->assign('types', View::get('EventType')->getHashData());
		Template::one()->assign('filters', $filterCfg);
		Template::one()->assign('showRescheduled', !empty($filters['resched']));

		Response::hideMenu();
	}

}