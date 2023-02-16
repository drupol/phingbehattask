<?php

namespace Phing\Behat;

/**
 * Class Filter. Represents a Behat CLI filter.
 *
 * @package Phing\Behat
 */
class Filters extends \DataType {

  /**
   * The profile name.
   *
   * @var string
   *   The profile name.
   */
  protected $profile;

  /**
   * The filter's tags.
   *
   * @var Tags[]
   */
  protected $tags = [];

  /**
   * The filter roles.
   *
   * @var Role[]
   */
  protected $roles = [];

  /**
   * Create the filter profile.
   *
   * @return Tags
   *   The created filter profile.
   */
  public function createTags() {
    $num = array_push($this->tags, new Tags());

    return $this->tags[$num - 1];
  }

  /**
   * Add a given tag to filters.
   *
   * @param Tags $tag
   *   The tag to add.
   *
   * @return self
   *   Return itself
   */
  public function addTag(Tags $tag) {
    $this->tags[] = $tag;

    return $this;
  }

  /**
   * Add a given role to filters.
   *
   * @param Role $role
   *   The role to add.
   *
   * @return self
   *   Return itself
   */
  public function addRole(Role $role) {
    $this->roles[] = $role;

    return $this;
  }

  /**
   * Get an array of the tag filters.
   *
   * @return array
   *   An array of regular expressions for the tags.
   */
  private function getTags() {
    $tags = [];

    foreach ($this->tags as $tag) {
      $tags[] = $tag->getRegEx();
    }

    return $tags;
  }

  /**
   * Get an array of the role filters.
   *
   * @return array
   *   An array of regular expressions for the roles.
   */
  private function getRoles() {
    $roles = [];

    foreach ($this->roles as $role) {
      $roles[] = $role->getRegEx();
    }

    return $roles;
  }

  /**
   * Create the filter profile.
   *
   * @return Role
   *   The created filter profile.
   */
  public function createRole() {
    $num = array_push($this->roles, new Role());

    return $this->roles[$num - 1];
  }

  /**
   * Set the profile name.
   *
   * @param string $str
   *   The profiles's name.
   *
   * @return self
   *   Return itself.
   */
  public function setProfile($str) {
    $this->profile = (string) $str;

    return $this;
  }

  /**
   * Get the profile name.
   *
   * @return string
   *   The profile name.
   */
  public function getProfile() {
    return $this->profile;
  }

  /**
   * Get all regular expressions for the current filter.
   *
   * @return array
   *   An array of regular expressions.
   */
  public function getFilters() {
    return array_merge($this->getTags(), $this->getRoles());
  }
}
