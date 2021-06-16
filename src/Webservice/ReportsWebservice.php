<?php

namespace Trois\Clockify\Webservice;

use Cake\Network\Http\Response;
use Cake\Utility\Hash;
use Cake\Utility\Text;

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

  protected function _transformResults(Endpoint $endpoint, array $results)
  {
    $resources = [];
    foreach ($results['timeentries'] as $key => $result)
    {
      // data object
      $r = (object) $result;

      // path
      $pName = Text::slug($r->projectName);
      $uName = text::slug($r->userEmail);
      $start = text::slug($r->timeInterval['start']);

      // create project
      if(!Hash::check($resources, $pName))
      {
        $resources[$pName] = [
          'client' => $r->clientName,
          'name' => $r->projectName,
          'users' => []
        ];
      }

      // create user entries
      if(!Hash::check($resources, "$pName.users.$uName"))
      {
        $resources[$pName]['users'][$uName] = (object)[
          'user' => $r->userName,
          'userEmail' => $r->userEmail,
          'entries' => []
        ];
      }

      $resources[$pName]['users'][$uName]->entries[$start] = $r;

      // sort
      ksort($resources[$pName]['users'][$uName]->entries);
    }

    $res = [];
    foreach ($resources as $key => $result) $res[] = $result;
    return $res;
  }
}
