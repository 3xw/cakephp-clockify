<?php

namespace Trois\Clockify\Webservice;

use Cake\Network\Http\Response;
use Cake\Utility\Hash;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;
use Muffin\Webservice\ResultSet;
use Muffin\Webservice\Webservice\Webservice;

/**
* Class GitHubWebservice
*
* @package CvoTechnologies\GitHub\Webservice
*/
class ClockifyWebservice extends Webservice
{

  protected $_queryFilters = [];

  /**
  * Returns the base URL for this endpoint
  *
  * @return string Base URL
  */
  public function getBaseUrl()
  {
    return '/api/v1/' . $this->endpoint();
  }

  /**
  * {@inheritDoc}
  */
  protected function _executeReadQuery(Query $query, array $options = [])
  {
    $url = $this->getBaseUrl();

    $queryParameters = [];
    // Page number has been set, add to query parameters
    if ($query->page()) {
      $queryParameters['page'] = $query->page();
    }
    // Result limit has been set, add to query parameters
    if ($query->limit()) {
      $queryParameters['page-size'] = $query->limit();
    }

    if ($query->clause('order')){
      foreach ($query->clause('order') as $field => $value)
      $queryParameters[strtoupper($field)] = strtoupper($value) == 'ASC'? 'ASCENDING': 'DESCENDING';
    }

    $search = false;
    $searchParameters = [];
    if ($query->clause('where')) {
      foreach ($query->clause('where') as $field => $value) {
        if(in_array($field, $this->_queryFilters)) $queryParameters[$field] = is_array($value)? implode(",", $value): $value;
      }
    }

    // Check if this query could be requested using a nested resource.
    if ($nestedResource = $this->nestedResource($query->clause('where'))) $url = $nestedResource;

    /* @var Response $response */
    if(empty($query->set())) $response = $this->driver()->client()->get($url, $queryParameters);
    else $response = $this->driver()->client()->post($url, json_encode($query->set()));
    $results = $response->getJson();
    if (!$response->isOk())
    {
      debug($response->getJson());
      throw new \Exception($response->getJson()['message']);
    }

    // Turn results into resources
    $resources = $this->_transformResults($query->endpoint(), $results);

    return new ResultSet($resources, count($resources));
  }

  protected function _transformResults(Endpoint $endpoint, array $results)
  {
    $resources = [];
    if(!empty($results[$endpoint->getName()])) $results = $results[$endpoint->getName()];
    foreach ($results as $key =>$result)
    {
      if(!is_numeric($key)) return [$this->_transformResource($endpoint, $results)];
      $resources[] = $this->_transformResource($endpoint, $result);
    }

    return $resources;
  }
}
