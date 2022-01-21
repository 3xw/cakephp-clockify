<?php
declare(strict_types=1);

namespace Trois\Clockify\Model\Resource;

use Muffin\Webservice\Model\Resource;

class UserReport extends Resource
{
  public function __construct(array $properties = [], array $options = [])
  {
    $properties = array_merge(
      [
        'user' => 'Unkown User',
        'userEmail' =>'unkown.user@unkown.com',
        'time_entries' => [],
        'cumulativeDuration' => 0
      ],
      $properties
    );

    parent::__construct($properties, $options);
  }

  public function addTimeEntry(TimeEntry $timeEntry)
  {
    $this->cumulativeDuration += $timeEntry->durtion;
    $this->_fields['time_entries'][] = $timeEntry;
  }

  public function getTimeEntriesMergedByDays($roundToMinute = 0): array
  {
    $entries = [];
    foreach($this->time_entries as $e)
    {
      // clown
      $e = clone $e;

      // round by entries
      // $e->addTime($this->roudItUp($e->duration, $roundToMinute) - $e->duration);

      $key = $e->start->format('Y-m-d');
      if(empty($entries[$key])) $entries[$key] = $e;
      else $entries[$key]->sumWithTimeEntry($e);
    }

    // sort
    ksort($entries);

    // round by days
    foreach($entries as $e) $e->addTime($this->roudItUp($e->duration, $roundToMinute) - $e->duration);

    return $entries;
  }

  public function roudItUp($sec, $roundToMinute): int
  {
    // round
    $roundDivider = $roundToMinute * 60;
    $sec = ceil($sec/$roundDivider) * $roundDivider;

    return (int) $sec;
  }
}
