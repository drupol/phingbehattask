<?php

namespace Phing\Behat;

/**
 * Class Role. Represents a Behat CLI filter.
 *
 * @package Phing\Behat
 */
class Role extends \DataType implements FilterInterface {

  /**
   * The filter's role.
   *
   * @var string
   */
  protected $value;

  /**
   * @inheritdoc
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * @inheritdoc
   */
  public function addText($value) {
    $this->value = trim($value);

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getRegEx() {
    return '^[ ]*As a[n]* ' . $this->value;
  }

}
