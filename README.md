Pheanstalk
==========

Pheanstalk is a pure PHP 5.2+ client for the [beanstalkd workqueue][1].  It has been actively developed, and used in production by many, since late 2008.

Created by [Paul Annesley][2], Pheanstalk is rigorously unit tested and written using encapsulated, maintainable object oriented design.  Community feedback, bug reports and patches has led to a stable 1.0.0 release in 2010.

beanstalkd up to the latest version 1.4 is supported.  All commands and responses specified in the [protocol documentation][3] for beanstalkd 1.3 are implemented.

  [1]: http://xph.us/software/beanstalkd/
  [2]: http://paul.annesley.cc/
  [3]: http://github.com/kr/beanstalkd/tree/v1.3/doc/protocol.txt?raw=true
  [4]: http://semver.org/


Usage Example
-------------

```php
<?php

// register Pheanstalk class loader
require_once('pheanstalk_init.php');

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

?>
```


Running the tests
-----------------

```
# ensure you have simpletest
$ git submodule init
$ git submodule update


$ ./tests/runtests.php
All Tests
OK
Test cases run: 4/4, Passes: 103, Failures: 0, Exceptions: 0


# extra tests relying on a beanstalkd on 127.0.0.1:11300
$ ./tests/runtests.php --with-server
All Tests
OK
Test cases run: 7/7, Passes: 198, Failures: 0, Exceptions: 0


$ ./tests/runtests.php --help

CLI test runner.

Available options:

  --with-server      Includes tests which connect to a beanstalkd server
  --testfile <path>  Only run the specified test file.
  --help             This documentation.
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
  * .. [more?](https://github.com/pda/pheanstalk/contributors) Let me know if you're missing.


Licence
-------

© Paul Annesley

Released under the [The MIT License](http://www.opensource.org/licenses/mit-license.php)
