<?php

namespace Trois\Clockify\Webservice;

class UserGroupsWebservice extends ClockifyWebservice
{

  protected $_queryFilters = ['projectId','name'];

  public function initialize(): void
  {
    parent::initialize();

    $this->addNestedResource('/api/v1/workspaces/:workspaceId/user-groups', [
      'workspaceId',
    ]);

    $this->addNestedResource('/api/v1//workspaces/:workspaceId/user-groups/:groupId', [
      'workspaceId',
      'groupId',
    ]);
  }
}
