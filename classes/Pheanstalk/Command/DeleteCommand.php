<?php

/**
 * The 'delete' command.
 * Permanently deletes an already-reserved job.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Command_DeleteCommand
    extends Pheanstalk_Command_AbstractCommand
    implements Pheanstalk_ResponseParser
{
    private $_jobId;

    /**
     * @param object $job Pheanstalk_Job or int $job
     */
    public function __construct($job)
    {
        $this->_jobId = is_object($job) ? $job->getId() : $job;
    }

    /* (non-phpdoc)
     * @see Pheanstalk_Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return sprintf('delete %u', $this->_jobId);
    }

    /* (non-phpdoc)
     * @see Pheanstalk_ResponseParser::parseRespose()
     */
    public function parseResponse($responseLine, $responseData)
    {
        if ($responseLine == Pheanstalk_Response::RESPONSE_NOT_FOUND) {
            throw new Pheanstalk_Exception_ServerException(sprintf(
                'Cannot delete job %u: %s',
                $this->_jobId,
                $responseLine
            ));
        }

        return $this->_createResponse($responseLine);
    }
}
