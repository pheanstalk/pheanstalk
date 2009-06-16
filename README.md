Pheanstalk
==========

Pheanstalk, created by [Paul Annesley][1], is a pure PHP 5.2+ client for the [beanstalkd workqueue][2].

Pheanstalk is rigorously unit tested and written using encapsulated, maintainable object oriented design.

All commands specified in the beanstalkd 1.3 protocol documentation (latest as at June 2009) are supported:

  * bury
  * delete
  * ignore
  * kick
  * list-tubes
  * list-tubes-watched
  * list-tube-used
  * peek
  * peek-ready
  * peek-delayed
  * peek-buried
  * put
  * release
  * reserve
  * reserve-with-timeout
  * stats
  * stats-job
  * stats-tube
  * touch
  * use
  * watch

  [1]: http://paul.annesley.cc/
  [2]: http://xph.us/software/beanstalkd/
