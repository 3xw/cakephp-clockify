<?php

namespace Trois\Clockify\Webservice;

use Cake\Network\Http\Response;
use Cake\Utility\Hash;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;
use Muffin\Webservice\ResultSet;

class ReportsWebservice extends ClockifyWebservice
{
  public function getBaseUrl()
  {
    return '/v1/' . $this->endpoint();
  }

  public function initialize()
  {
    parent::initialize();

    $this->addNestedResource('/v1/workspaces/:workspaceId/reports/detailed', [
      'workspaceId',
    ]);
  }
}
