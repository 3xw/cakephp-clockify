<?php
namespace Trois\Clockify\Model\Endpoint;

class ReportsEndpoint extends ClockifyEndpoint
{
  public static function defaultConnectionName(): string
  {
    return 'reports_clockify';
  }

  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setPrimaryKey('id');
    $this->setDisplayField('name');
  }

  public function projectReportsToUserReports(array $pr = [])
  {
    $users = [];
    foreach($pr as $p) foreach($p->users as $ur)
    {
      // if not entry
      if(empty($ur->time_entries)) continue;

      if(empty($users[$ur->user])) $users[$ur->user] = clone $ur;
      else $users[$ur->user]->addTimeEntries($ur->time_entries);
    }

    return $users;
  }
}
