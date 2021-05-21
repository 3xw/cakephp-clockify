<?php

namespace Trois\Clockify\Webservice\Driver;

use Cake\Network\Http\Client;
use Muffin\Webservice\AbstractDriver;


class Clockify extends AbstractDriver
{

  /**
  * {@inheritDoc}
  */
  public function initialize()
  {
    $this->client(new Client([
      'host' =>  $this->getConfig('host'),
      'scheme' => 'https',
      'headers' => [
        'X-Api-Key' => $this->getConfig('token'),
        'Content-Type' => 'application/json'
      ]
    ]));
  }
}
