<?php

namespace Phing\Behat;

/**
 * Interface FilterInterface
 *
 * The required methods for a filter item.
 *
 * @package Phing\Behat
 */
interface FilterInterface {

  /**
   * Get the filter's value.
   *
   * @return string
   *   The filter's value.
   */
  public function getValue();

  /**
   * Set the value from a text element.
   *
   * @param string $value
   *   The value of the element.
   *
   * @return self
   *   Return itself.
   */
  public function addText($value);


  /**
   * Get the Regular Expression string for a given filter.
   *
   * @return string
   *   The regular expression match for the the filter.
   */
  public function getRegEx();

}