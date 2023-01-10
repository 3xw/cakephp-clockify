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

    // time entries
    if(!empty($properties['time_entries'])) foreach($properties['time_entries'] as &$timeEntry) if(is_array($timeEntry)) $timeEntry = new TimeEntry($timeEntry);

    // construct
    parent::__construct($properties, $options);
  }

  public function addTimeEntries(array $timeEntries)
  {
    foreach($timeEntries as $timeEntry) $this->addTimeEntry($timeEntry);
  }

  // public function addTimeEntry(TimeEntry|array $timeEntry) php 8 :/
  public function addTimeEntry($timeEntry)
  {
    // be tolerant
    if(is_array($timeEntry)) $timeEntry = new TimeEntry($timeEntry);

    $this->cumulativeDuration += $timeEntry->durtion;
    $this->_fields['time_entries'][] = $timeEntry;
  }

  public function getTimeEntriesMergedByDays($roundToMinute = 0, $roundByDay = true, $splitByTask = false): array
  {
    $entries = [];
    foreach($this->time_entries as $e)
    {
      // clown
      $e = new TimeEntry($e->toArray());

      // round by entries
      if(!$roundByDay) $e->addTime($this->roudItUp($e->duration, $roundToMinute) - $e->duration);

      $key = $splitByTask? $e->start->format('Y-m-d')."__$e->taskName" :$e->start->format('Y-m-d');
      if(empty($entries[$key])) $entries[$key] = $e;
      else $entries[$key]->sumWithTimeEntry($e);
    }

    // sort
    ksort($entries);

    // round by days
    if($roundByDay) foreach($entries as $e) $e->addTime($this->roudItUp($e->duration, $roundToMinute) - $e->duration);

    return $entries;
  }

  public function roudItUp($sec, $roundToMinute): int
  {
    // round
    $roundDivider = $roundToMinute * 60;
    $sec = ceil($sec/$roundDivider) * $roundDivider;

    return (int) $sec;
  }

  public function getTimeEntries($MergedByDays = false, $roundToMinute = 0, $roundByDay = true, $splitByTask = false)
  {
    if(!$MergedByDays) return $this->time_entries ?? [];
    else return $this->getTimeEntriesMergedByDays($roundToMinute, $roundByDay, $splitByTask);
  }
}
