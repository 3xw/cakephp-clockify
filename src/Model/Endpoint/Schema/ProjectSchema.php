<?php

namespace Trois\Clockify\Model\Endpoint\Schema;

use Muffin\Webservice\Model\Schema;

class ProjectSchema extends Schema
{
  /**
  * {@inheritDoc}
  */
  public function initialize()
  {
    parent::initialize();

    $this->addColumn('id', [
      'type' => 'integer',
      'primaryKey' => true
    ]);
    $this->addColumn('name', [
      'type' => 'string',
    ]);
    $this->addColumn('clientId', [
      'type' => 'string',
    ]);
    $this->addColumn('archived',[
      'type' => 'boolean',
    ]);
    $this->addColumn('isPublic',[
      'type' => 'boolean',
    ]);
  }
}
