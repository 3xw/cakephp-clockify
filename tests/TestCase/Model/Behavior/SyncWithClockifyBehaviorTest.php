<?php
namespace Trois\Clockify\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use Trois\Clockify\Model\Behavior\SyncWithClockifyBehavior;

/**
 * Trois\Clockify\Model\Behavior\SyncWithClockifyBehavior Test Case
 */
class SyncWithClockifyBehaviorTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Trois\Clockify\Model\Behavior\SyncWithClockifyBehavior
     */
    public $SyncWithClockify;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->SyncWithClockify = new SyncWithClockifyBehavior();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->SyncWithClockify);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
