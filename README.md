Pheanstalk
==========

[![Build Status](https://travis-ci.org/pda/pheanstalk.png?branch=master)](https://travis-ci.org/pda/pheanstalk)

Pheanstalk is a pure PHP 5.3+ client for the [beanstalkd workqueue][1].  It has
been actively developed, and used in production by many, since late 2008.

Created by [Paul Annesley][2], Pheanstalk is rigorously unit tested and written
using encapsulated, maintainable object oriented design.  Community feedback,
bug reports and patches has led to a stable 1.0 release in 2010, a 2.0 release
in 2013, and a 3.0 release in 2014.

Pheanstalk 3.0 introduces PHP namespaces, PSR-1 and PSR-2 coding standards,
and PSR-4 autoloader standard.

beanstalkd up to the latest version 1.4 is supported.  All commands and
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

Creating a worker
-----------------

A worker is a process which when runs takes the next job off a set of queues which are being watched and then executes
the job with the defined tasks.

The worker process runs the following steps:

1. Watch all registered tubes
2. Reserve the next job
3. Once job is reserved, invoke the registered handler based on the tube name
4. If no exceptions occur, delete the job (success)
5. If 'retry_on' exceptions occur, call 'release' (retry)
6. If other exception occurs, call 'bury' (error)
7. Repeat steps 2-6

To create a worker use the following example.

```
<?php

// Again hopefully you are using Composers autoloading

use Pheanstalk\Worker;

$worker = new Worker('127.0.0.1');

// ----------------------------------------
// register functions to be called for each queue

$worker->register('testtube', function ($job) {
    echo $job->getData();
});

// You can register multiple tubes to be watched by a single worker
$worker->register('testtube2', function ($job) {
    echo $job->getData();
});

// If you Exception class is specified the job will be released instead of buried.
$worker->register('testtube3', function ($job) {
    echo $job->getData();
}, 'SomeException');

// -----------------------------------------
// Start the worker.

$worker->process();
```

Running the tests
-----------------

There is a section of the test suite which depends on a running beanstalkd
at 127.0.0.1:11300, which was previously opt-in via `--with-server`.
Since porting to PHPUnit, all tests are run at once. Feel free to submit
a pull request to rectify this.

```
# ensure you have Composer set up
$ wget http://getcomposer.org/composer.phar
$ php composer.phar install

$ ./vendor/bin/phpunit
PHPUnit 4.0.19 by Sebastian Bergmann.

Configuration read from /Users/pda/code/pheanstalk/phpunit.xml.dist

................................................................. 65 / 83 ( 78%)
..................

Time: 239 ms, Memory: 6.00Mb

OK (83 tests, 378 assertions)
```


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
  * .. [more?](https://github.com/pda/pheanstalk/contributors) Let me know if you're missing.


License
-------

© Paul Annesley

Released under the [The MIT License](http://www.opensource.org/licenses/mit-license.php)
