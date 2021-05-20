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
      'host' => 'api.clockify.me',
      'scheme' => 'https',
      'headers' => ['X-Api-Key' => $this->getConfig('api_key')]
    ]));
  }
}
