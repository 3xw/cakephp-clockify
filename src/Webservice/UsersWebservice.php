<?php

namespace Trois\Clockify\Webservice;

class UsersWebservice extends ClockifyWebservice
{

  protected $_queryFilters = ['projectId','memberships'];

  public function initialize(): void
  {
    parent::initialize();

    $this->addNestedResource('/api/v1/workspaces/:workspaceId/users', [
      'workspaceId',
    ]);

    $this->addNestedResource('/api/v1//workspaces/:workspaceId/users/:userId', [
      'workspaceId',
      'userId',
    ]);
  }
}
