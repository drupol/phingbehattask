<?php

namespace Phing\Behat\Tests;

use Phing\Behat\Task;

/**
 * Class TestableTask.
 *
 * Make task class testable since base ExecTask does not implement a proper
 * dependency injection.
 *
 * @package Phing\Behat\Tests
 */
class TestableTask extends Task {

  /**
   * Set command line object.
   *
   * @param \Commandline $command_line
   *    Command line object.
   */
  public function setCommandLine(\Commandline $command_line) {
    $this->commandline = $command_line;
  }

}
