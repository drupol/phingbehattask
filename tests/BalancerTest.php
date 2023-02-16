<?php

namespace Phing\Behat\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use function bovigo\assert\assert;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\hasKey;

/**
 * Class BalancerTest.
 *
 * @package Phing\Behat\Tests
 */
class BalancerTest extends TestCase {

  /**
   * Containers generation test.
   *
   * @dataProvider featureFilesProvider
   */
  public function testContainersGeneration($containers, $files, $expected, $testFilters = ['' => []]) {
    $balancer = \Mockery::mock('\Phing\Behat\Balancer');
    $balancer->makePartial();

    $filters = $this->getFilters($testFilters);

    /** @var \Phing\Behat\Balancer $balancer */
    $scanFiles = $balancer->scanDirectory(__DIR__ . '/files', '/.feature/');
    assert(sort($scanFiles), equals(sort($files)));

    $balancer->setContainers($containers);
    $actual = [];
    foreach ($balancer->getFilteredFiles($filters, $files) as $profile => $filteredFiles) {
      $actual[$profile] = [];
      foreach ($balancer->getContainers($filteredFiles) as $key => $container) {
        $actual[$profile][$key] = $container;
      }
    }
    if ($profile === '') {
      $actual = $actual[$profile];
    }

    assert($actual, equals($expected));
  }

  /**
   * YAML generation test.
   *
   * @dataProvider featureFilesProvider
   */
  public function testYamlGeneration($containers, $files, $expected, $testFilters = ['' => []]) {
    $balancer = \Mockery::mock('\Phing\Behat\Balancer');
    $balancer->makePartial();

    $filters = $this->getFilters($testFilters);

    /** @var \Phing\Behat\Balancer $balancer */
    $scanFiles = $balancer->scanDirectory(__DIR__ . '/files', '/.feature/');

    $balancer->setContainers($containers);
    $balancer->setImport('behat.import.yml');

    foreach ($balancer->getFilteredFiles($filters, $scanFiles) as $profile => $filteredFiles) {
      foreach ($balancer->getContainers($filteredFiles) as $container) {
        $content = $balancer->generateBehatYaml($container, $profile);
        if ($profile === '') {
          $profile = 'default';
        }

        $parsed = Yaml::parse($content);

        assert($parsed, hasKey($profile)->and(hasKey('imports')));
        assert($parsed['imports'], equals(['behat.import.yml']));
        assert($parsed[$profile]['suites']['default']['paths'], equals($container));
      }
    }
  }

  /**
   * Test filter regular expression return.
   *
   * @dataProvider featureRegExProvider
   */
  public function testFiltersRegEx($testFilters, $expected) {
    $filters = $this->getFilters($testFilters);

    return assert($filters, equals($expected));
  }

  /**
   * Get all profiles regular expressions for a given configuration.
   *
   * The testFilters array should mimic the filters xml:
   *   profile
   *     tags
   *       'tag-filters'
   *     role
   *       'role-filter
   *   second-profile
   *     tags
   *       'tag-filters'
   *     role
   *       'role-filter
   *
   * In turn this will return:
   *   profile
   *     'tags-regular-expression'
   *     'role-regular-expression'
   *   second-profile
   *     'tags-regular-expression'
   *     'role-regular-expression'
   *
   * @param array $testFilters
   *   An array that mimics the filters xml.
   *
   * @return array
   *   An array of regex filters, keyed by the filter profile name.
   */
  private function getFilters($testFilters) {
    $filters = [];

    foreach ($testFilters as $profile => $testFilter) {
      $filters[$profile] = $this->getFiltersForProfile($testFilter);
    }

    return $filters;
  }

  /**
   * Get the filter regular expressions for a given filter.
   *
   * The testFilters array should mimic one single filters xml entry:
   *   tags
   *     'tag-filters'
   *   role
   *     'role-filter
   *
   * In turn this will return:
   *   'tags-regular-expression'
   *   'role-regular-expression'
   *
   * @param $testFilters
   *   An array that mimics the single filter.
   *
   * @return array
   *   An array of regex filters for the given filter.
   */
  private function getFiltersForProfile($testFilters) {
    /** @var \Phing\Behat\Filters $filters */
    $filters = \Mockery::mock('\Phing\Behat\Filters');
    $filters->makePartial();

    if (array_key_exists('tags', $testFilters)) {
      foreach ($testFilters['tags'] as $testTag) {
        $tags = \Mockery::mock('\Phing\Behat\Tags');
        $tags->makePartial();
        /** @var \Phing\Behat\Tags $tags */
        $tags->addText($testTag);

        $filters->addTag($tags);
      }
    }

    if (array_key_exists('role', $testFilters)) {
      foreach ($testFilters['role'] as $testTag) {
        $role = \Mockery::mock('\Phing\Behat\Role');
        $role->makePartial();
        /** @var \Phing\Behat\Role $role */
        $role->addText($testTag);

        $filters->addRole($role);
      }
    }

    return $filters->getFilters();
  }

  /**
   * Data provider for regular expression tests.
   *
   * @return array
   *   Test arguments.
   */
  public function featureRegExProvider() {
    return [
      // Case 1 - One include tag, one exclude tag and a role.
      [
        'testFilters' => [
          'default' => [
            'tags' => ['@one&&~@two'],
            'role' => ['person'],
          ],
        ],
        'expected' => [
          'default' => [
            '(?=^@)(?!.*(@two\b)).*(@one\b).*',
            '^[ ]*As a[n]* person',
          ],
        ],
      ],
      // Case 2 - One include tag, one exclude tag.
      [
        'testFilters' => [
          'default' => [
            'tags' => ['@three&&~@four'],
          ],
        ],
        'expected' => [
          'default' => [
            '(?=^@)(?!.*(@four\b)).*(@three\b).*',
          ],
        ],
      ],
      // Case 3 - Two include tags and a role.
      [
        'testFilters' => [
          'default' => [
            'tags' => ['@five&&@six'],
            'role' => ['administrator'],
          ],
        ],
        'expected' => [
          'default' => [
            '(?=^@)(@five\b|@six\b).*',
            '^[ ]*As a[n]* administrator',
          ],
        ],
      ],
      // Case 4 - Two include tags.
      [
        'testFilters' => [
          'default' => [
            'tags' => ['@seven&&@eight'],
          ],
        ],
        'expected' => [
          'default' => [
            '(?=^@)(@seven\b|@eight\b).*',
          ],
        ],
      ],
    ];
  }

  /**
   * Data provider for balancer tests.
   *
   * @return array
   *    Test arguments.
   */
  public function featureFilesProvider() {
    return [
      // Case 1.
      [
        'containers' => 3,
        'files' => [
          __DIR__ . '/files/feature-1.feature',
          __DIR__ . '/files/feature-2.feature',
          __DIR__ . '/files/feature-3.feature',
          __DIR__ . '/files/feature-4.feature',
          __DIR__ . '/files/feature-5.feature',
          __DIR__ . '/files/feature-6.feature',
          __DIR__ . '/files/feature-7.feature',
        ],
        'expected' => [
          [
            __DIR__ . '/files/feature-1.feature',
            __DIR__ . '/files/feature-2.feature',
            __DIR__ . '/files/feature-3.feature',
          ],
          [
            __DIR__ . '/files/feature-4.feature',
            __DIR__ . '/files/feature-5.feature',
            __DIR__ . '/files/feature-6.feature',
          ],
          [
            __DIR__ . '/files/feature-7.feature',
          ],
        ],
      ],
      // Case 2.
      [
        'containers' => 1,
        'files' => [
          __DIR__ . '/files/feature-1.feature',
          __DIR__ . '/files/feature-2.feature',
          __DIR__ . '/files/feature-3.feature',
          __DIR__ . '/files/feature-4.feature',
          __DIR__ . '/files/feature-5.feature',
          __DIR__ . '/files/feature-6.feature',
          __DIR__ . '/files/feature-7.feature',
        ],
        'expected' => [
          [
            __DIR__ . '/files/feature-1.feature',
            __DIR__ . '/files/feature-2.feature',
            __DIR__ . '/files/feature-3.feature',
            __DIR__ . '/files/feature-4.feature',
            __DIR__ . '/files/feature-5.feature',
            __DIR__ . '/files/feature-6.feature',
            __DIR__ . '/files/feature-7.feature',
          ],
        ],
      ],
      // Case 3.
      [
        'containers' => 1,
        'files' => [
          __DIR__ . '/files/feature-1.feature',
          __DIR__ . '/files/feature-2.feature',
          __DIR__ . '/files/feature-3.feature',
          __DIR__ . '/files/feature-4.feature',
          __DIR__ . '/files/feature-5.feature',
          __DIR__ . '/files/feature-6.feature',
          __DIR__ . '/files/feature-7.feature',
        ],
        'expected' => [
          'default' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-5.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'one' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-5.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'onetwo' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-5.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'three' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'onenottwo' => [
            [
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-5.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'onenottwoWithAdmin' => [
            [
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-5.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
        ],
        'testFilters' => [
          'default' => [
            'tags' => ['@one&&~@three'],
          ],
          'one' => [
            'tags' => ['@one'],
          ],
          'onetwo' => [
            'tags' => ['@one&&@two'],
          ],
          'three' => [
            'tags' => ['@three'],
          ],
          'onenottwo' => [
            'tags' => ['@one&&~@two'],
          ],
          'onenottwoWithAdmin' => [
            'tags' => ['@one&&~@two'],
            'role' => ['administrator'],
          ],
        ],
      ],
      // Case 4.
      [
        'containers' => 2,
        'files' => [
          __DIR__ . '/files/feature-1.feature',
          __DIR__ . '/files/feature-2.feature',
          __DIR__ . '/files/feature-3.feature',
          __DIR__ . '/files/feature-4.feature',
          __DIR__ . '/files/feature-5.feature',
          __DIR__ . '/files/feature-6.feature',
          __DIR__ . '/files/feature-7.feature',
        ],
        'expected' => [
          'default' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-4.feature',
            ],
            [
              __DIR__ . '/files/feature-5.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'one' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-4.feature',
            ],
            [
              __DIR__ . '/files/feature-5.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'onetwo' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-4.feature',
            ],
            [
              __DIR__ . '/files/feature-5.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'three' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-4.feature',
            ],
            [
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'onenottwo' => [
            [
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-5.feature',
            ],
            [
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'onenottwoWithAdmin' => [
            [
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-4.feature',
            ],
            [
              __DIR__ . '/files/feature-5.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
        ],
        'testFilters' => [
          'default' => [
            'tags' => ['@one&&~@three'],
          ],
          'one' => [
            'tags' => ['@one'],
          ],
          'onetwo' => [
            'tags' => ['@one&&@two'],
          ],
          'three' => [
            'tags' => ['@three'],
          ],
          'onenottwo' => [
            'tags' => ['@one&&~@two'],
          ],
          'onenottwoWithAdmin' => [
            'tags' => ['@one&&~@two'],
            'role' => ['administrator'],
          ],
        ],
      ],
    ];
  }

}
