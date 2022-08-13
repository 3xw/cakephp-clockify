<?php
declare(strict_types=1);

namespace Trois\Clockify\Model\Resource;

use Muffin\Webservice\Model\Resource;

class ProjectReport extends Resource
{
  public function __construct(array $properties = [], array $options = [])
  {
    $properties = array_merge(
      [
        'name' => 'Unknown Project',
        'client' => 'Unknown Client',
        'users' => [],
      ],
      $properties
    );

    parent::__construct($properties, $options);
  }

  public function getUserReports()
  {
    return $this->users ?? [];
  }

  public function getTimeEntries($MergedByDays = false, $roundToMinute = 0, $roundByDay = true)
  {
    $timeEntries = [];
    foreach($this->getUserReports() as $ur) foreach ($ur->getTimeEntries($MergedByDays, $roundToMinute, $roundByDay) as $te) $timeEntries[] = $te;
    return $timeEntries;
  }
}
