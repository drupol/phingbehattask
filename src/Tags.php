<?php

namespace Phing\Behat;

/**
 * Class Filter. Represents a Behat CLI filter.
 *
 * @package Phing\Behat
 */
class Tags extends \DataType implements FilterInterface {

  /**
   * The filter's tags.
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
   * Get filter groups.
   *
   * @param string $value
   *   The current tag value.
   *
   * @return array
   *   An array of tags to filter.
   */
  private function getFilterGroups($value) {
    if (!$value) {
      return [];
    }

    if (strpos($value, '&&') === false) {
      return [$value];
    }

    return explode('&&', $value);
  }

  /**
   * @inheritdoc
   */
  public function getRegEx() {
    $filterGroups = $this->getFilterGroups($this->value);
    $tags = $this->process($filterGroups);
    if (!$tags) {
      return '';
    }

    $regex = '(?=^@)';
    if (!empty($tags['exclude'])) {
      $regex .= '(?!.*(' . implode('\b|', $tags['exclude']) . '\b)).*';
    }
    if (!empty($tags['include'])) {
      $regex .= '(' . implode('\b|', $tags['include']) . '\b).*';
    }

    return $regex;
  }

  /**
   * Process the filter tags in to groups of
   *   - include: Tags to include.
   *   - exclude: Tags to exclude.
   *
   * @param array $filterGroups
   *   The array of tags for the current filter.
   *
   * @return array
   *   An array of grouped tags.
   */
  private function process(array $filterGroups) {
    $filters = [];
    foreach ($filterGroups as $tag) {
      $first = substr($tag, 0, 1);
      if (!in_array($first, ['@', '~'])) {
        continue;
      }

      $type = 'include';
      if ($first === '~') {
        $type = 'exclude';
        $tag = substr($tag, 1);
      }

      if (!array_key_exists($type, $filters)) {
        $filters[$type] = [];
      }

      $filters[$type][] = $tag;
    }

    return $filters;
  }

}
