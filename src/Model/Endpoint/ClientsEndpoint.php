<?php
namespace Trois\Clockify\Model\Endpoint;

class ClientsEndpoint extends ClockifyEndpoint
{
  public function initialize(array $config)
  {
    parent::initialize($config);
    $this->primaryKey('id');
    $this->displayField('name');
    //$this->setWebservice('Space', new \App\Webservice\ClickUp\SpaceWebservice);
    //debug($this->getWebservice());
  }
}
