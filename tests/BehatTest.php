<?php

namespace Phing\Behat\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class BehatTest.
 *
 * @package Phing\Behat\Tests
 */
class BehatTest extends TestCase {

  /**
   * Behat test.
   *
   * @dataProvider commandsProvider
   */
  public function testSmoke($command, $result) {
    \Phing::setOutputStream(new \OutputStream(fopen('php://output', 'w')));
    \Phing::setErrorStream(new \OutputStream(fopen('php://output', 'w')));

    $file = __DIR__ . '/xml/test.xml';
    $template = __DIR__ . '/xml/template.xml';
    // Read the entire string.
    $xml = file_get_contents($template);
    // Replace something in the file string - this is a VERY simple example.
    $str = str_replace('<behat/>', $command, $xml);
    $str = str_replace('<behat', '<behat pretend="yes"', $str);
    // Write the entire string.
    file_put_contents($file, $str);

    \Phing::startup();
    $m = new \Phing();
    $args = array('-f', realpath($file));
    ob_start();
    $m->execute($args);
    $m->runBuild();
    $content = ob_get_contents();
    ob_end_clean();
    \Phing::shutdown();

    $this->assertContains("Executing command: " . $result . " 2>&1\n", $content);
    unlink($file);
  }

  /**
   * Data provider.
   *
   * @return array
   *    Test arguments.
   */
  public function commandsProvider() {
    return array(
      array(
        'command' => '<behat/>',
        'result' => 'behat',
      ),
      array(
        'command' => '<property name="behat.bin" value="/boo/boo/behat"/><behat/>',
        'result' => '/boo/boo/behat',
      ),
    );
  }

}
