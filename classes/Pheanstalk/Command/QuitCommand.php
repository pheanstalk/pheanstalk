<?php

/**
 * The 'quit' command.
 * Closes the connection with the job server
 *
 * @author Julio Viera
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Command_QuitCommand  extends Pheanstalk_Command_AbstractCommand {
    public function getCommandLine() {
        return "quit";
    }


}
