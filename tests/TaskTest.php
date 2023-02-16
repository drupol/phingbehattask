<?php

namespace Phing\Behat\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class TaskTest.
 *
 * @package Phing\Behat\Tests
 */
class TaskTest extends TestCase {

  /**
   * Smoke test.
   */
  public function testTestSmoke() {
    $command_line = $this->prophesize(\Commandline::class);
    $task = new TestableTask();
    $task->setCommandLine($command_line->reveal());
    $this->assertTrue(TRUE);
  }

}
