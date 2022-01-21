<?php

namespace Trois\Clockify\Webservice;

use Cake\Network\Http\Response;
use Cake\Utility\Hash;
use Cake\Utility\Text;

use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Datasource\Query;
use Muffin\Webservice\Datasource\ResultSet;

use  Trois\Clockify\Model\Resource\ProjectReport;
use  Trois\Clockify\Model\Resource\UserReport;
use  Trois\Clockify\Model\Resource\TimeEntry;

class ReportsWebservice extends ClockifyWebservice
{
  public function getBaseUrl()
  {
    return '/v1/' . $this->getEndpoint();
  }

  public function initialize(): void
  {
    parent::initialize();

    $this->addNestedResource('/v1/workspaces/:workspaceId/reports/detailed', [
      'workspaceId',
    ]);
  }

  protected function _transformResults(Endpoint $endpoint, array $results): array
  {
    $resources = [];
    foreach ($results['timeentries'] as $key => $result)
    {
      // data object
      $r = new TimeEntry($result);

      // path
      $pName = Text::slug($r->projectName);
      $uName = text::slug($r->userEmail);
      $start = text::slug($r->timeInterval['start']);

      // create project
      if(!Hash::check($resources, $pName))
      {
        $resources[$pName] = new ProjectReport([
          'client' => $r->clientName,
          'name' => $r->projectName
        ]);
      }

      // create user entries
      if(!Hash::check($resources, "$pName.users.$uName"))
      {
        $resources[$pName]['users'][$uName] = new UserReport([
          'user' => $r->userName,
          'userEmail' => $r->userEmail
        ]);
      }

      $resources[$pName]['users'][$uName]->addTimeEntry($r);
    }

    return $resources;
  }
}
