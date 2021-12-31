<?php

namespace Trois\Clockify\Webservice;

class ProjectsWebservice extends ClockifyWebservice
{
  protected $_queryFilters = ['clients'];

  public function initialize(): void
  {
    parent::initialize();

    $this->addNestedResource('/api/v1/workspaces/:workspaceId/projects', [
      'workspaceId',
    ]);

    $this->addNestedResource('/api/v1/workspaces/:workspaceId/projects/:projectId', [
      'workspaceId',
      'projectId',
    ]);
  }
}
