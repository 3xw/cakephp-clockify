<?php

namespace Trois\Clockify\Webservice;

class UsersWebservice extends ClockifyWebservice
{

  protected $_queryFilters = ['memberships','email','projectId','name','status','sort-column','sort-order','includeRoles'];

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
