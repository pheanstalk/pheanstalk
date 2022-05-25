# This file is intended to roughly document design decisions

## Class design
By default classes are final, properties are private. 
From an API perspective, classes are internal by default.
## Commands

Command classes encapsulate a single command from the beanstalk protocol.
A command instance has 2 responsibilities:
- Formulate the protocol commandline
- Interpret the result

Note that we say interpret here, parsing the response is already done.

Commands interpret `CommandInterface`. Since many commands have a subject that is either a tube name or a job ID, there
are abstract base classes `TubeCommand` and `JobCommand` that simplify command generation.

## Roles: Manager, Publisher and Subscriber
Typically different parts of a PHP application will use different parts of the beanstalkd protocol.
In v5 we have split up the commands into three separate roles:
- Manager 
- Publisher
- Subscriber

We still offer the `Pheanstalk` class that implements all interfaces, if possible however, it is recommend to inject the 
most specific class for the situation. A `PheanstalkPublisher` class immediately makes it clear that all downstream code
just cares about pushing jobs to the queue.
### Manager
This role contains functions that are used to gather information about the server and its jobs. The assumption is that this
will be used mostly in management / reporting interfaces.

### Publisher
A job is created and pushed to the server, usually this happens within the request lifecycle. This role contains functions
that allow for publishing jobs.

### Subscriber
This role contains functions for listening to tubes and reserving / managing the jobs in them. 
