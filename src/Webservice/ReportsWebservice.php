<?php

namespace Trois\Clockify\Webservice;

use Cake\Network\Http\Response;
use Cake\Utility\Hash;
use Cake\Utility\Text;

use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Datasource\Query;
use Muffin\Webservice\Datasource\ResultSet;

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

      $this->addTimeRecord($resources[$pName]['users'][$uName]->entries, $r);

      // sort
      ksort($resources[$pName]['users'][$uName]->entries);
    }

    $res = [];
    foreach ($resources as $key => $result) $res[] = $result;
    return $res;
  }

  protected function addTimeRecord(&$array, $record)
  {
    $key = (new \DateTime($record->timeInterval['start']))->format('Y-m-d');
    if(empty($array[$key])) $array[$key] = $record;
    else $array[$key]->timeInterval['duration'] += $record->timeInterval['duration'];
  }
}
