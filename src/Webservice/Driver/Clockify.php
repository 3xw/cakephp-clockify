<?php

namespace Trois\Clockify\Webservice\Driver;

use Cake\Http\Client;
use Muffin\Webservice\Webservice\Driver\AbstractDriver;


class Clockify extends AbstractDriver
{

  /**
  * {@inheritDoc}
  */
  public function initialize()
  {
    $this->setClient(new Client([
      'host' =>  $this->getConfig('host'),
      'scheme' => 'https',
      'headers' => [
        'X-Api-Key' => $this->getConfig('token'),
        'Content-Type' => 'application/json'
      ]
    ]));
  }
}
