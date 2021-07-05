<?php

namespace Trois\Clockify\Webservice;

use Cake\Utility\Text;
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

  public function nestedResource(array $conditions)
  {
    if(empty($conditions)) return false;
    if(empty($this->_nestedResources)) return false;

    // keys to replace
    $keys = array_keys($conditions);

    // remove query filters
    foreach($keys as $k => $key) if(in_array($key, $this->_queryFilters)) unset($keys[$k]);

    foreach ($this->_nestedResources as $url => $options)
    {
      $diff = array_diff($options['requiredFields'], $keys);
      if(count($diff) != 0 || count($options['requiredFields']) != count($keys)) continue;

      return Text::insert($url, $conditions);
    }

    return false;
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
      debug($url);
      debug($response->getJson());
      throw new \Exception($response->getJson()['message']);
    }

    // check limit
    if(
      !empty($query->set()) &&
      Hash::check($query->set(),'detailedFilter.pageSize') &&
      Hash::check($results,'totals.0.entriesCount')
    ) if(Hash::get($results,'totals.0.entriesCount') >= Hash::get($query->set(),'detailedFilter.pageSize')) throw new \Exception("Limite export gartuit atteinte (".Hash::get($results,'totals.0.entriesCount').")");

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

  protected function _executeCreateQuery(Query $query, array $options = [])
  {
    return $this->_write($query, $options);
  }

  protected function _executeUpdateQuery(Query $query, array $options = [])
  {
    return $this->_write($query, $options);
  }

  protected function _write(Query $query, array $options = [])
  {
    $url = $this->getBaseUrl();
    if (
      $query->getOptions() &&
      !empty($query->getOptions()['nested']) &&
      $nestedResource = $this->nestedResource($query->getOptions()['nested'])
      ) $url = $nestedResource;

      switch ($query->action())
      {
        case Query::ACTION_CREATE:
        $response = $this->driver()->client()->post($url, json_encode($query->set()));
        break;

        case Query::ACTION_UPDATE:
        $response = $this->driver()->client()->put($url, json_encode($query->set()));
        break;

        case Query::ACTION_DELETE:
        $response = $this->driver()->client()->delete($url);
        break;
      }

      if (!$response->isOk())
      {
        debug($url);
        debug($response);
        debug($response->getStringBody());
        throw new \Exception($response->getJson()['err']);
      }

      return $this->_transformResource($query->endpoint(), $response->getJson());
    }
  }
