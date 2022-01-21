<?php
namespace Trois\Clockify\Model\Endpoint;

class UserGroupsEndpoint extends ClockifyEndpoint
{
  public function initialize(array $config): void
  {
    parent::initialize($config);
    $this->setPrimaryKey('id');
    $this->setDisplayField('name');
  }
}
