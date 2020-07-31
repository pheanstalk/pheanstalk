Pheanstalk
==========

[![Latest Stable Version](https://img.shields.io/packagist/v/pda/pheanstalk.svg)](https://packagist.org/packages/pda/pheanstalk)
[![Total Downloads](https://img.shields.io/packagist/dt/pda/pheanstalk.svg)](https://packagist.org/pda/pheanstalk)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pheanstalk/pheanstalk/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pheanstalk/pheanstalk/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/pheanstalk/pheanstalk/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/pheanstalk/pheanstalk/?branch=master)
[![Build Status](https://travis-ci.org/pheanstalk/pheanstalk.svg?branch=master)](https://travis-ci.org/pheanstalk/pheanstalk)

Pheanstalk is a pure PHP 7.1+ client for the [beanstalkd workqueue][1].  It has
been actively developed, and used in production by many, since late 2008.

Created by [Paul Annesley][2], Pheanstalk is rigorously unit tested and written
using encapsulated, maintainable object oriented design.  Community feedback,
bug reports and patches has led to a stable 1.0 release in 2010, a 2.0 release
in 2013, and a 3.0 release in 2014.

Pheanstalk 3.0 introduces PHP namespaces, PSR-1 and PSR-2 coding standards,
and PSR-4 autoloader standard.

beanstalkd up to the latest version 1.10 is supported.  All commands and
responses specified in the [protocol documentation][4] for beanstalkd 1.3 are
implemented.

  [1]: https://beanstalkd.github.io/
  [2]: https://paul.annesley.cc/
  [3]: https://github.com/sammousa
  [4]: https://github.com/kr/beanstalkd/tree/v1.3/doc/protocol.txt?raw=true
  
Pheanstalk 4
-------------
In 2018 [Sam Mousa][3] took on the responsibility of maintaining Pheanstalk.

Pheanstalk 4.0 drops support for older PHP versions. It contains the following changes (among other things):
- Strict PHP type hinting
- Value objects for Job IDs
- Functions without side effects
- Dropped support for persistent connections
- Add support for multiple socket implementations (streams extension, socket extension, fsockopen)

### Dropping support persistent connections
Persistent connections are a feature where a TCP connection is kept alive between different requests to reduce overhead
from TCP connection set up. When reusing TCP connections we must always guarantee that the application protocol, in this
case beanstalks' protocol is in a proper state. This is hard, and in some cases impossible; at the very least this means
we must do some tests which cause roundtrips.
Consider for example a connection that has just sent the command `PUT 0 4000`. The beanstalk server is now going to read
4000 bytes, but if the PHP script crashes during this write the next request get assigned this TCP socket.
Now to reset the connection to a known state it used to subscribe to the default tube: `use default`.
Since the beanstalk server is expecting 4000 bytes, it will just write this command to the job and wait for more bytes..

To prevent these kinds of issues the simplest solution is to not use persistent connections.

### Dropped connection handling
Depending on the socket implementation used we might not be able to enable TCP keepalive. If we do not have TCP keepalive
there is no way for us to detect dropped connections, the underlying OS may wait up to 15 minutes to decide that a TCP
connection where no packets are being sent is disconnected. 
When using a socket implementation that supports read timeouts, like `SocketSocket` which uses the socket extension we 
use read and write timeouts to detect broken connections; the issue with the beanstalk protocol is that it allows for
no packets to be sent for extended periods of time. Solutions are to either catch these connection exceptions and reconnect
or use `reserveWithTimeout()` with a timeout that is less than the read / write timeouts.  

Example code for a job runner could look like this (this is real production code):
```php
while(true) {
    $job = $beanstalk->reserveWithTimeout(50);
    $this->stdout('.', Console::FG_CYAN);
    if (isset($job)) {
        $this->ensureDatabase($db);
        try {
            /** @var HookTask $task */
            $task = $taskFactory->createFromJson($job->getData());

            $commandBus->handle($task);
            $this->stdout("Deleting job: {$job->getId()}\n", Console::FG_GREEN);
            $beanstalk->delete($job);
        } catch (\Throwable $t) {
            \Yii::error($t);
            $this->stderr("\n{$t->getMessage()}\n", Console::FG_RED);
            $this->stderr("{$t->getTraceAsString()}\n", Console::FG_RED);

            $this->stdout("Burying job: {$job->getId()}\n", Console::FG_YELLOW);
            $beanstalk->bury($job);
        }
    }
}
```
Here connection errors will cause the process to exit (and be restarted by a task manager).   

### Functions with side effects
In version 4 functions with side effects have been removed, functions like `putInTube` internally did several things:
1. Switch to the tube
2. Put the job in the new tube

In this example, the tube changes meaning that the connection is now in a different state. This is not intuitive and forces
any user of the connection to always switch / check the current tube.
Another issue with this approach is that it is harder to deal with errors. If an exception occurs it is unclear whether 
we did or did not switch tube.


Migration to v4
-------------
A migration should in most cases be relatively simple:
- Change the constructor, either use the static constructor, use a DI container to construct the dependencies, or manually 
instantiate them.
- Change instances of `reserve()` with a timeout to `reserveWithTimeout(int $timeout)` since `reserve()` no longer accepts a `timeout` parameter.
- Run your tests, or use a static analyzer to test for calls to functions that no longer exist.
- Make sure that you handle connection exceptions (this is not new to V4, only in V4 you will get more of them due to the
default usage of a socket implementation that has read / write timeouts).





Installation with Composer
-------------

Install pheanstalk as a dependency with composer:

```bash
composer require pda/pheanstalk
```


Usage Example
-------------

#### Producer 

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Pheanstalk\Pheanstalk;

$pheanstalk = Pheanstalk::create('127.0.0.1');

// Queue a Job
$pheanstalk
  ->useTube('testtube')
  ->put("job payload goes here\n");

$pheanstalk
    ->useTube('testtube')
    ->put(
        json_encode(['test' => 'data']),  // encode data in payload
        Pheanstalk::DEFAULT_PRIORITY,     // default priority
        30, // delay by 30s
        60  // beanstalk will retry job after 60s
     );

```


#### Consumer / Worker
```php
<?php
require __DIR__ . '/vendor/autoload.php';
use Pheanstalk\Pheanstalk;

$pheanstalk = Pheanstalk::create('127.0.0.1');

// we want jobs from 'testtube' only.
$pheanstalk->watch('testtube');

// this hangs until a Job is produced.
$job = $pheanstalk->reserve();

try {
    $jobPayload = $job->getData();
    // do work.

    sleep(2);
    // If it's going to take a long time, periodically
    // tell beanstalk we're alive to stop it rescheduling the job.
    $pheanstalk->touch($job);
    sleep(2);

    // eventually we're done, delete job.
    $pheanstalk->delete($job);
}
catch(\Exception $e) {
    // handle exception.
    // and let some other worker retry.
    $pheanstalk->release($job); 
}
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
  * [Sam Mousa](https://github.com/sammousa)
  * .. [more?](https://github.com/pda/pheanstalk/contributors) Let me know if you're missing.


License
-------

© Paul Annesley

Released under the [The MIT License](http://www.opensource.org/licenses/mit-license.php)
