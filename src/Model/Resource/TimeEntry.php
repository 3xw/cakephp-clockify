<?php
declare(strict_types=1);

namespace Trois\Clockify\Model\Resource;

use Muffin\Webservice\Model\Resource;

class TimeEntry extends Resource
{
  protected $_virtual = ['duration'];

  public function __construct(array $properties = [], array $options = [])
  {
    parent::__construct(array_merge(
      [
        'projectName' => 'Unknown Project',
        'clientName' => 'Unknown Client',
        'start' => new \DateTime,
        'end' => new \DateTime,
      ],
      $properties
    ), $options);

    // extract
    $toExtract = ['start','end'];
    if(!empty($this->timeInterval)) foreach($toExtract as $key) if(empty($property[$key])) $this->set($key, new \DateTime($this->timeInterval['end']));

    // max
    if(empty($property['max'])) $this->set('max', clone $this->end);
  }

  protected function _getDuration()
  {
    return $this->end->getTimestamp() - $this->start->getTimestamp();
  }

  public function addTime(int $timestamp)
  {
    $this->end->setTimestamp($this->end->getTimestamp() + $timestamp);
    if($this->end > $this->max) $this->max->setTimestamp($this->end->getTimestamp());
  }

  public function shiftBy(int $timestamp)
  {
    $this->start->modify("$timestamp seconds");
    $this->end->modify("$timestamp seconds");
    $this->max->modify("$timestamp seconds");
  }

  public function sumWithTimeEntry(TimeEntry $timeEntry)
  {
    $this->addTime($timeEntry->duration);
    if($timeEntry->start < $this->start) $this->shiftBy($timeEntry->start->getTimestamp() - $this->start->getTimestamp());
    if($timeEntry->max > $this->end) $this->max->setTimestamp($timeEntry->max->getTimestamp());
    // debug("$this->userName: add ".($timeEntry->duration/60)."min ".$this->start->format('d.m'));
  }
}
