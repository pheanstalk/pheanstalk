Pheanstalk
==========

[![Build Status](https://travis-ci.org/pda/pheanstalk.png?branch=master)](https://travis-ci.org/pda/pheanstalk)

Pheanstalk is a pure PHP 5.2+ client for the [beanstalkd workqueue][1].  It has
been actively developed, and used in production by many, since late 2008.

Created by [Paul Annesley][2], Pheanstalk is rigorously unit tested and written
using encapsulated, maintainable object oriented design.  Community feedback,
bug reports and patches has led to a stable 1.0.0 release in 2010, and a 2.0.0
release in 2013.

beanstalkd up to the latest version 1.4 is supported.  All commands and
responses specified in the [protocol documentation][3] for beanstalkd 1.3 are
implemented.

  [1]: http://xph.us/software/beanstalkd/
  [2]: http://paul.annesley.cc/
  [3]: http://github.com/kr/beanstalkd/tree/v1.3/doc/protocol.txt?raw=true
  [4]: http://semver.org/

Installation with Composer
-------------

Declare pheanstalk as a dependency in your projects `composer.json` file:

``` json
{
  "require": {
    "pda/pheanstalk": "dev-master"
  }
}
```

Usage Example
-------------

```php
<?php

// If you aren't using composer, register Pheanstalk class loader
require_once('pheanstalk_init.php');

$pheanstalk = new Pheanstalk_Pheanstalk('127.0.0.1');

// ----------------------------------------
// producer (queues jobs)

$pheanstalk
  ->useTube('testtube')
  ->put("job payload goes here\n");

// ----------------------------------------
// worker (performs jobs)

$job = $pheanstalk
  ->watch('testtube')
  ->ignore('default')
  ->reserve();

echo $job->getData();

$pheanstalk->delete($job);

// ----------------------------------------
// check server availability

$pheanstalk->getConnection()->isServiceListening(); // true or false

```

Running the tests
-----------------

We use [Composer](http://getcomposer.org/) to install [PHPUnit](http://www.phpunit.de/manual/3.7/en/index.html), and [Vagrant](http://www.vagrantup.com/) to create, and provision, an isolated test enviroment to run the tests in.

```
$ composer install --dev
$ cd tests/env
$ vagrant up && vagrant ssh
$ php /vagrant/vendor/phpunit/phpunit/phpunit.php -c /vagrant/phpunit.xml.dist

Configuration read from /vagrant/phpunit.xml.dist

................................................................. 65 / 83 ( 78%)
..................

Time: 1 second, Memory: 9.25Mb

OK (83 tests, 395 assertions)
```

Contributors
------------

  * [Paul Annesley](https://github.com/pda)
  * [Lachlan Donald](https://github.com/lox)
  * [Joakim Bick](https://github.com/minimoe)
  * [SlNPacifist](https://github.com/SlNPacifist)
  * [leprechaun](https://github.com/leprechaun)
  * [Peter McArthur](https://github.com/ptrmcrthr)
  * [robbiehudson](https://github.com/robbiehudson)
  * [Geoff Catlin](https://github.com/gcatlin)
  * [srjlewis](https://github.com/srjlewis)
  * [Lars Yencken](https://github.com/larsyencken)
  * [Josh Butts](https://github.com/jimbojsb)
  * [Henry Smith](https://github.com/h2s)
  * [Javier Spagnoletti](https://github.com/phansys)
  * [Anthony Sterling](https://github.com/anthonysterling)
  * .. [more?](https://github.com/pda/pheanstalk/contributors) Let me know if you're missing.


Licence
-------

© Paul Annesley

Released under the [The MIT License](http://www.opensource.org/licenses/mit-license.php)
