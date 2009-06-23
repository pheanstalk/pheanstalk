Pheanstalk
==========

Pheanstalk, created by [Paul Annesley][1], is a pure PHP 5.2+ client for the [beanstalkd workqueue][2].  The code is rigorously unit tested and written using encapsulated, maintainable object oriented design.

All commands and responses specified in the [protocol documentation][3] for beanstalkd 1.3 are fully supported.

  [1]: http://paul.annesley.cc/
  [2]: http://xph.us/software/beanstalkd/
  [3]: http://github.com/kr/beanstalkd/tree/v1.3/doc/protocol.txt?raw=true

Usage Example
-------------

<pre><code class="php">
&lt;?php

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

?&gt;
</code></pre>
