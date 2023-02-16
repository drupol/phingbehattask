<?php

namespace Phing\Behat;

/**
 * A Behat task for Phing.
 *
 * @package Phing\Behat
 */
class Task extends \ExecTask {

  /**
   * The source file from XML attribute.
   *
   * @var \PhingFile
   */
  protected $file;

  /**
   * All fileset objects assigned to this task.
   *
   * @var array
   */
  protected $filesets = array();

  /**
   * Path the the Behat executable.
   *
   * @var PhingFile
   */
  protected $bin = 'behat';

  /**
   * All Behat options to be used to create the command.
   *
   * @var Option[]
   */
  protected $options = array();

  /**
   * Task constructor.
   */
  public function __construct() {
    parent::__construct();
    $this->setExecutable($this->bin);
  }

  /**
   * Set the path to the Behat executable.
   *
   * @param string $bin
   *   The behat executable file.
   */
  public function setBin($bin) {
    $this->bin = new \PhingFile($bin);
    $this->setExecutable($this->bin);
  }

  /**
   * Set the path to features to test.
   *
   * @param string $path
   *   The path to features.
   */
  public function setPath($path) {
    $this->createOption()
      ->setName('path')
      ->addText($path);
  }

  /**
   * Sets the Behat config file to use.
   *
   * @param string $config
   *   The config file.
   */
  public function setConfig($config) {
    $this->createOption()
      ->setName('config')
      ->addText($config);
  }

  /**
   * Sets the name of tests to run.
   *
   * @param string $name
   *   The feature name to match.
   */
  public function setName($name) {
    $this->createOption()
      ->setName('profile')
      ->addText($name);
  }

  /**
   * Sets the test tags to use.
   *
   * @param string $tags
   *   The tag(s) to match.
   */
  public function setTags($tags) {
    $this->createOption()
      ->setName('profile')
      ->addText($tags);
  }

  /**
   * Sets the role able to run tests.
   *
   * @param string $role
   *   The actor role to match.
   */
  public function setRole($role) {
    $this->createOption()
      ->setName('profile')
      ->addText($role);
  }

  /**
   * This is not a real drush option. It's just used for tests.
   *
   * Display the command that would be runned only.
   *
   * @param bool $yesNo
   *   The pretend option.
   */
  public function setPretend($yesNo) {
    if ($yesNo) {
      $this->createOption()
        ->setName('pretend');
    }
  }

  /**
   * Set the profile to use for tests.
   *
   * @param string $profile
   *   The profile to use.
   */
  public function setProfile($profile) {
    $this->createOption()
      ->setName('profile')
      ->addText($profile);
  }

  /**
   * Set the test suite to use.
   *
   * @param string $suite
   *   The suite to use.
   */
  public function setSuite($suite) {
    if ($suite) {
      $this->createOption()
        ->setName('suite')
        ->addText($suite);
    }
  }

  /**
   * Sets the flag if strict testing should be enabled.
   *
   * @param bool $yesNo
   *   Behat strict mode.
   */
  public function setStrict($yesNo) {
    if (\StringHelper::booleanValue($yesNo)) {
      $this->createOption()
        ->setName('strict');
    }
  }

  /**
   * Sets the flag if a verbose output should be used.
   *
   * @param bool $yesNo
   *   Use verbose output.
   */
  public function setVerbose($yesNo) {
    if (\StringHelper::booleanValue($yesNo)) {
      $this->createOption()
        ->setName('verbose')
        ->addText('yes');
    }
  }

  /**
   * Either force ANSI colors on or off.
   *
   * @param bool $yesNo
   *   Use ANSI colors.
   */
  public function setColors($yesNo) {
    if (\StringHelper::booleanValue($yesNo)) {
      $this->createOption()
        ->setName('colors');
    }
  }

  /**
   * Force no ANSI color in the output.
   *
   * @param bool $yesNo
   *   Use ANSI colors.
   */
  public function setNoColors($yesNo) {
    if (\StringHelper::booleanValue($yesNo)) {
      $this->createOption()
        ->setName('no-colors');
    }
  }

  /**
   * Invokes test formatters without running tests against a site.
   *
   * @param bool $yesNo
   *   Run without testing.
   */
  public function setDryRun($yesNo) {
    if (\StringHelper::booleanValue($yesNo)) {
      $this->createOption()
        ->setName('dry-run');
    }
  }

  /**
   * How to format tests output. pretty is default.
   *
   * Available formats are:
   * - junit: Outputs the failures in JUnit compatible files.
   * - progress: Prints one character per step.
   * - pretty: Prints the feature as is.
   * You can use multiple formats at the same time. (multiple values allowed)
   *
   * @param string $format
   *   The format.
   */
  public function setFormat($format) {
    $this->createOption()
      ->setName('format')
      ->addText($format);
  }

  /**
   * Sets the flag if test execution should stop in the event of a failure.
   *
   * @param bool $yesNo
   *   If all tests should stop on first failure.
   */
  public function setHaltonerror($yesNo) {
    if (\StringHelper::booleanValue($yesNo)) {
      $this->createOption()
        ->setName('stop-on-failure');
    }
  }

  /**
   * Options of the Behat command.
   *
   * @return Option
   *   The created option.
   */
  public function createOption() {
    $num = array_push($this->options, new Option());
    return $this->options[$num - 1];
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    // Get default properties from project.
    $properties_mapping = array(
      'setBin' => 'behat.bin',
      'setColors' => 'behat.colors',
      'setDryrun' => 'behat.dry-run',
      'setName' => 'behat.name',
      'setProfile' => 'behat.profile',
      'setSuite' => 'behat.suite',
      'setVerbose' => 'behat.verbose',
    );

    foreach ($properties_mapping as $class_method => $behat_property) {
      if ($property = $this->getProject()->getProperty($behat_property)) {
        call_user_func(array($this, $class_method), $property);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function main() {
    /*
     * The Behat binary command.
     */
    if ($this->bin instanceof \PhingFile) {
      $this->setBin($this->bin);
    }
    $this->commandline->setExecutable($this->bin);

    /*
     * The options.
     */
    $options = array();

    // This has been specifically made for tests.
    // If the pretend option has been found, just display the drush command
    // but never execute it.
    $pretend = NULL;
    if ($pretend = $this->optionExists('pretend')) {
      $this->setLogoutput(FALSE);
      $this->setPassthru(FALSE);
      $this->setCheckreturn(FALSE);
      $this->optionRemove('pretend');
    }

    foreach ($this->options as $option) {
      // Trick to ensure no option duplicates.
      $options[$option->getName()] = $option->toString();
    }
    // Sort options alphabetically.
    asort($options);
    $this->commandline->addArguments(array_values($options));

    $this->buildCommand();
    $this->log('Executing command: ' . $this->realCommand);

    if (!$pretend) {
      parent::main();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function buildCommand() {
    $this->realCommand = implode(' ', $this->commandline->getCommandline());

    if ($this->error !== NULL) {
      $this->realCommand .= ' 2> ' . escapeshellarg($this->error->getPath());
      $this->log(
        "Writing error output to: " . $this->error->getPath(),
        $this->logLevel
      );
    }

    if ($this->output !== NULL) {
      $this->realCommand .= ' 1> ' . escapeshellarg($this->output->getPath());
      $this->log(
        "Writing standard output to: " . $this->output->getPath(),
        $this->logLevel
      );
    }
    elseif ($this->spawn) {
      $this->realCommand .= ' 1>/dev/null';
      $this->log("Sending output to /dev/null", $this->logLevel);
    }

    // If neither output nor error are being written to file
    // then we'll redirect error to stdout so that we can dump
    // it to screen below.
    if ($this->output === NULL && $this->error === NULL && $this->passthru === FALSE) {
      $this->realCommand .= ' 2>&1';
    }

    // We ignore the spawn boolean for windows.
    if ($this->spawn) {
      $this->realCommand .= ' &';
    }
  }

  /**
   * Check if an option exists.
   *
   * @param string $optionName
   *   The option name.
   *
   * @return array|\Phing\Behat\Option[]
   *   The option if exists, an empty array otherwise.
   */
  private function optionExists($optionName) {
    return array_filter($this->options, function ($option) use ($optionName) {
      return $option->getName() == $optionName;
    });
  }

  /**
   * Remove an option.
   *
   * @param string $optionName
   *   The option name.
   *
   * @return \Phing\Behat\Option[]
   *   The option array without the option to remove.
   */
  private function optionRemove($optionName) {
    $this->options = array_filter($this->options, function ($option) use ($optionName) {
      return $option->getName() != $optionName;
    });

    return $this->options;
  }

}
