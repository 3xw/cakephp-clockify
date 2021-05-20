<?php
namespace Trois\Clockify\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

class ClockifyCommand extends Command
{
  public function execute(Arguments $args, ConsoleIo $io)
  {
    $this->loadModel('Space', 'Endpoint');
  }
}
