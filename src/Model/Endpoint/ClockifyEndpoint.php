<?php
namespace Trois\Clockify\Model\Endpoint;

use Muffin\Webservice\Model\Endpoint;

class ClockifyEndpoint extends Endpoint
{
  public static function defaultConnectionName()
  {
    return 'clockify';
  }

  public function create(EntityInterface $resource, $options = [])
  {
    //toDo
  }
}
