<?php
namespace Trois\Clockify\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Trois\Clockify\Utility\ModelLoader;

class SyncWithClockifyBehavior extends Behavior
{
  protected $_defaultConfig = [
    'endpoint' => 'Projects',
    'joinTable' => [
      'modelName' => 'ClockifyMatches',
      'foreignKey' => 'foreign_id',
      'clockifyKey' => 'clockify_id',
      'conditions' => ['ClockifyMatches.model' => 'Accounts'],
    ],
    'staticMatching' => [],
    'mapping' => [],
    'delete' => false
  ];

  protected function loadModel($modelClass = null, $modelType = null)
  {
    return (new ModelLoader)->loadModel($modelClass, $modelType);
  }

  protected function patchResource($resource, EntityInterface $entity, ArrayObject $options)
  {
    $data = [];
    foreach($this->getConfig('staticMatching') as $field => $value) $data[$filed] = $value;
    foreach($this->getConfig('mapping') as $field => $mapping) $data[$field] = $this->getValueOrCallable($mapping, $entity);

    return $this->getEndpoint()->patchEntity($resource, $data, $options->getArrayCopy());
  }

  public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
  {
    if(empty($options['EnableClockifySync'])) return;
    if(empty($options['EnableClockifySync']['nested'])) throw new \Exception('Need nested options to create records on Clockify');
    if($this->getConfig('endpoint') == 'Projects' && empty($options['EnableClockifySync']['clientId'])) throw new \Exception('Need clientId options to create records on Clockify');

    if(!empty($options['EnableClockifySync']['clientId'])) $this->setConfig('clientId', $options['EnableClockifySync']['clientId']);
    else $this->setConfig('clientId', false);

    // create empty
    $clockifyId = false;
    $resource =  $this->getEndpoint()->newEntity();

    // check if one exists
    if(
      // !$entity->isNew() &&
      $clockifyId = $this->getClockifyId($entity, $options['EnableClockifySync']['nested'])
    ){
      if($this->getConfig('endpoint') != 'Clients')
      {
        $nested = array_merge($options['EnableClockifySync']['nested'], [$this->getNestedVarName($this->getConfig('endpoint')) => $clockifyId]);
        if(
          $resourceExists = $this->getEndpoint()->find()
          ->where($nested)
          ->first()
        ) $resource = $resourceExists;
      }else
      {
        $resource->isNew(false);
        $resource->id = $clockifyId;
      }
    }
    else
    {
      if($this->getConfig('endpoint') == 'Projects')
      {
        $resource->clientId = $options['EnableClockifySync']['clientId'];
        $resource->isPublic = 1;
        $resource->archived = 0;
      }
    }

    // warm
    $resource = $this->patchResource($resource, $entity, $options);
    $resource->isPublic = 1;
    $nested = $this->getNestedOptionsForResource($resource, $options['EnableClockifySync']['nested']);

    // save
    if(!$resource = $this->getEndpoint()->save($resource, ['nested' => $nested])) return;

    // set entity
    $entity->set('resource', $resource);

    // save relation
    if(!$clockifyId)
    {
      $this->getJoinTable()->save($this->getJoinTable()->newEntity([
         'model' => $this->getTable()->getAlias(),
         'foreign_id' =>$entity->get($this->getTable()->getPrimaryKey()),
         'clockify_id' => $resource->id
       ]));
    }
  }

  public function afterDelete(Event $event, EntityInterface $entity, ArrayObject $options)
  {
    // check
    if(!$this->getConfig('delete')) return;
    if(empty($options['EnableClockifySync'])) return;
    if(empty($options['EnableClockifySync']['nested'])) throw new \Exception('Need nested options to create records on Clockify');

    // related
    if(!$clockifyId = $this->getClockifyId($entity)) return;
    $nested = array_merge($options['EnableClockifySync']['nested'], [$this->getNestedVarName($this->getConfig('endpoint')) => $clockifyId]);

    // delete
    $this->getEndpoint()->delete($entity, ['nested' => $nested]);
  }

  /* UTILS */
  public function getClockifyId(EntityInterface $entity, $nested = null)
  {
    // get
    if(!$joinEntity = $this->getJoinEntity($entity))
    {
      if(!$nested) return false;
      if(!$joinEntity = $this->lookupOnClockifyAndCreate($entity, $nested)) return false;
    }

    // return ID
    $join = (object) $this->getConfig('joinTable');
    return $joinEntity->get($join->clockifyKey);
  }

  public function lookupOnClockifyAndCreate(EntityInterface $entity, $nested)
  {
    $items = $this->getEndpoint()->find()
    ->limit(1000)
    ->where($nested)
    ->toArray();
    $entityId4Digi = sprintf('%04d', $entity->number);

    foreach ($items as $itm)
    {
      $parts = explode(" ", trim($itm->name));
      $Id4Digi = sprintf('%04d',end($parts));
      if(
        ($entityId4Digi == $Id4Digi && $this->getConfig('endpoint') != 'Projects') ||
        ($entityId4Digi == $Id4Digi && $this->getConfig('clientId') &&  $itm->clientId == $this->getConfig('clientId'))
      )
      {
        $joinEntity = $this->getJoinTable()->newEntity([
           'model' => $this->getTable()->alias(),
           'foreign_id' => $entity->id,
           'clockify_id' => $itm->id
         ]);

        if(!$joinEntity = $this->getJoinTable()->save($joinEntity) ) throw new \Exception("Error Processing Request", 1);

        return $joinEntity;
      }
    }

    return false;
  }

  protected function getEndpoint($endpoint = null)
  {
    if(!$endpoint) $endpoint = $this->getConfig('endpoint');
    if(property_exists($this, $endpoint)) return $this->{$endpoint};
    return $this->loadModel("Trois/Clockify.$endpoint", 'Endpoint');
  }

  protected static function getValueOrCallable($value, ...$args)
  {
    if(is_callable($value)) return call_user_func_array($value, $args);
    else if(!empty($args) && is_subclass_of($args[0], 'Cake\Datasource\EntityInterface')) return $args[0]->{$value};
    else return $value;
  }

  protected function getJoinTable()
  {
    $mn = $this->getConfig('joinTable.modelName');
    if(property_exists($this, $mn)) return $this->{$mn};
    else return $this->loadModel($mn);
  }

  protected function getJoinEntity(EntityInterface $entity)
  {
    // set
    $join = (object) $this->getConfig('joinTable');
    $key = $entity->get($this->getTable()->getPrimaryKey());
    $join->conditions["$join->modelName.$join->foreignKey"] = $key;

    // get
    return $this->getJoinTable()->find()->where($join->conditions)->first();
  }

  protected function getNestedVarName($endpointName)
  {
    return Inflector::singularize(strtolower($endpointName)).'Id';
  }

  protected function getNestedOptions(array $nested)
  {
    $opt = [];
    foreach($nested as $endpointName => $id) $opt[$this->getNestedVarName($endpointName)] = $id;
    return $opt;
  }

  protected function getNestedOptionsForResource($resource, $nested = [])
  {
    if($resource->isNew()) return $nested;
    return array_merge($nested, [
      $this->getNestedVarName($this->getConfig('endpoint')) => $resource->id
    ]);
  }
}
