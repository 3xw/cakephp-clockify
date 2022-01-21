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
    return $pr;
    /*
    foreach($pr as $p)
    {
      $users = $p->users
    }
    */
  }
}
