Pheanstalk
==========

[![Build Status](https://travis-ci.org/pda/pheanstalk.png?branch=master)](https://travis-ci.org/pda/pheanstalk)

Pheanstalk is a pure PHP 7.2+ client for the [beanstalkd workqueue][1].  It has
been actively developed, and used in production by many, since late 2008.

Created by [Paul Annesley][2], Pheanstalk is rigorously unit tested and written
using encapsulated, maintainable object oriented design.  Community feedback,
bug reports and patches has led to a stable 1.0 release in 2010, a 2.0 release
in 2013, and a 3.0 release in 2014.

Pheanstalk 3.0 introduces PHP namespaces, PSR-1 and PSR-2 coding standards,
and PSR-4 autoloader standard.

Pheanstalk 4.0 drops support for older PHP versions. It contains the following changes (among other things):
- Strict PHP type hinting
- Value objects for Job IDs
- Functions without side effects
- Dropped support for persistent connections


beanstalkd up to the latest version 1.10 is supported.  All commands and
responses specified in the [protocol documentation][3] for beanstalkd 1.3 are
implemented.

  [1]: http://xph.us/software/beanstalkd/
  [2]: http://paul.annesley.cc/
  [3]: http://github.com/kr/beanstalkd/tree/v1.3/doc/protocol.txt?raw=true

Installation with Composer
-------------

Install pheanstalk as a dependency with composer:

```bash
composer require pda/pheanstalk
```


Usage Example
-------------

```php
<?php

// Hopefully you're using Composer autoloading.

use Pheanstalk\Pheanstalk;

$pheanstalk = new Pheanstalk('127.0.0.1');

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

If you have docker-compose installed running tests is as simple as:
```sh
> composer test
```

If you don't then you manually need to set up a beanstalk server and run:
```sh
> vendor/bin/phpunit
```composer


Contributors
------------

  * [Paul Annesley](https://github.com/pda)
  * [Lachlan Donald](https://github.com/lox)
  * [Joakim Bick](https://github.com/minimoe)
  * [Vyacheslav](https://github.com/SlNPacifist)
  * [leprechaun](https://github.com/leprechaun)
  * [Peter McArthur](https://github.com/ptrmcrthr)
  * [robbiehudson](https://github.com/robbiehudson)
  * [Geoff Catlin](https://github.com/gcatlin)
  * [Steven Lewis](https://github.com/srjlewis)
  * [Lars Yencken](https://github.com/larsyencken)
  * [Josh Butts](https://github.com/jimbojsb)
  * [Henry Smith](https://github.com/h2s)
  * [Javier Spagnoletti](https://github.com/phansys)
  * [Graham Campbell](https://github.com/GrahamCampbell)
  * [Thomas Tourlourat](https://github.com/armetiz)
  * [Matthieu Napoli](https://github.com/mnapoli)
  * [Christoph](https://github.com/xrstf)
  * [James Hamilton](https://github.com/mrjameshamilton)
  * [Hannes Van De Vreken](https://github.com/hannesvdvreken)
  * [Yaniv Davidovitch](https://github.com/YanivD)
  * [Sam Mousa](https://github.com/sammousa)
  * .. [more?](https://github.com/pda/pheanstalk/contributors) Let me know if you're missing.


License
-------

© Paul Annesley

Released under the [The MIT License](http://www.opensource.org/licenses/mit-license.php)
