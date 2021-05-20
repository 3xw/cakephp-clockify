<?php

namespace Trois\Clockify\Webservice;

class ClientsWebservice extends ClockifyWebservice
{
  public function initialize()
  {
    parent::initialize();

    $this->addNestedResource('/api/v1/workspaces/:workspaceId/clients', [
      'workspaceId',
    ]);

    $this->addNestedResource('/api/v1/workspaces/:workspaceId/clients/:clientId', [
      'workspaceId',
      'clientId',
    ]);
  }
}
