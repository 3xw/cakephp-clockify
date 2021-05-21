<?php
namespace Trois\Clockify\Model\Endpoint;

class ReportsEndpoint extends ClockifyEndpoint
{
  public static function defaultConnectionName()
  {
    return 'reports_clockify';
  }

  public function initialize(array $config)
  {
    parent::initialize($config);
    $this->primaryKey('id');
    $this->displayField('name');
    //$this->setWebservice('Space', new \App\Webservice\ClickUp\SpaceWebservice);
    //debug($this->getWebservice());
  }
}
