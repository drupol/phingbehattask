## Phing Behat Task
[![Build Status](https://travis-ci.org/drupol/phingbehattask.svg?branch=master)](https://travis-ci.org/drupol/phingbehattask)

A Behat task for [Phing](http://www.phing.info/). This task enable usage of Behat commands in Phing build scripts.

Phing provides tools for usual tasks for PHP projects (phplint, jslint, VCS checkouts, files copy or merge, packaging,
upload, etc.). Integration of Behat in Phing is particularly useful when building and testing Drupal projects in a
continuous integration server such as [Jenkins](http://jenkins-ci.org/), [Travis](https://travis-ci.org/)
or [Continuous PHP](https://continuousphp.com/).
 
## Installation

Installation must be done through [Composer](https://getcomposer.org/):

```
  composer require drupol/phingbehattask
```

or by editing your composer.json file and add in the right section:

```json
{
  "require": {
    "drupol/phingbehattask": "dev-master"
  }
}
```

## Usage

### Behat task

To use the Behat task in your build file, it must be made available to Phing so that the buildfile parser is aware a
correlating XML element and it's parameters.

This is done by adding a `<taskdef>` task to your build file:

```xml
  <taskdef name="behat" classname="\Phing\Behat\Task" />
```

or by importing the ```import.xml``` file: 

```xml
  <import file="vendor/drupol/phingbehattask/import.xml"/>
```

Once imported, you are able to use it:

```xml
  <behat bin="${project.basedir}/vendor/behat/behat/bin/behat" haltonerror="yes" colors="yes" verbose="${behat.options.verbosity}">
    <option name="config=">${project.basedir}/tests/behat.yml</option> 
  </behat>
```

See the [Phing documentation](http://www.phing.info/docs/guide/stable/chapters/appendixes/AppendixB-CoreTasks.html#TaskdefTask) for more information on the `<taskdef>` task.

As the Phing Behat Task extends the Phing ExecTask, you are able to use all the options of that command.

Have a look at [the ExecTask documentation](https://www.phing.info/docs/guide/trunk/ExecTask.html) for the complete list of options.

### Behat load balancer task

The Behat load balancer task splits your feature files into an arbitrary number of Behat configuration files.
 
To use the Behat load balancer task in your build file, it must be made available to Phing so that the buildfile 
parser is aware a correlating XML element and it's parameters.

This is done by adding a `<taskdef>` task to your build file:

```xml
  <taskdef name="behat:balancer" classname="\Phing\Behat\Balancer" />
```

or by importing the ```import.xml``` file: 

```xml
  <import file="vendor/drupol/phingbehattask/import.xml"/>
```

Once imported, you are able to use it as follow:

```xml
  <behat:balancer
    containers="5"
    root="/path/to/your/behat/features/directory"
    destination="/path/to/destination/directory"
    import="/path/to/base/behat/configuration/behat.yml"
  />
```

The attributes have the following meaning:

- `containers`: Number of Behat configuration files the feature files will be split into.
- `root`: Path to your Behat `/features` directory.
- `destination`: Destination directory in which the new behat files will be generated.
- `import`: Main Behat configuration file to be imported in each of the generated file.

You can then configure your continuous integration to run one behat file per environment.

## Filtering

The behat balancer also allows for filtering on tags when using multiple profiles.

Filtering matches behat filtering [http://behat.org/en/latest/user_guide/configuration/suites.html#suite-filters]

The following example will first filter the features into profile specific features, by using the tags as a filter:
  - default: NOT @wip AND NOT @profile AND NOT @login AND must contain @theme will only be included and then balanced.
  - secondary_profile: NOT @secondary_profile AND NOT @api AND must contain @secondary_theme or the the text 'As a(n) administrator' will only be included and then balanced.
The filters are based on behat's filters and as such will also match any features that contain scenarios that are tagged.
```xml
  <behat:balancer
    containers="5"
    root="/path/to/your/behat/features/directory"
    destination="/path/to/destination/directory"
    import="/path/to/base/behat/configuration/behat.yml"
  >
    <filters profile="default">
        <tags><![CDATA[ ~@wip&&~@profile&&~@login&&@theme ]]></tags>
    </filters>
    <filters profile="secondary_profile">
        <tags><![CDATA[ ~@secondary_profile&&~@api&&@secondary_theme ]]></tags>
        <role>administrator</role>
    </filters>
  </behat:balancer>
```

The balanced files will generate files in the following format `behat.{profile}.{part}.yml` and will produce the output:
```xml
imports:
  - {behat_configuration_file}
{profile}:
  suites:
    default:
      paths:
        - {feature}
        - {feature}
```

If there are no filters, for backward compatibility the file name is `behat.{part}.yml`.
