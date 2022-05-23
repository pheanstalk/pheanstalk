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
