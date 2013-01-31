<?php

/**
 * Tests exceptions thrown by Pheanstalk_ResponseParser implementations.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_ResponseParserExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testDeleteNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new Pheanstalk_Command_DeleteCommand($this->_mockJob(5)),
            'NOT_FOUND'
        );
    }

    public function testReleaseBuried()
    {
        $this->_expectServerExceptionForResponse(
            new Pheanstalk_Command_ReleaseCommand($this->_mockJob(5), 1, 0),
            'BURIED'
        );
    }

    public function testReleaseNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new Pheanstalk_Command_ReleaseCommand($this->_mockJob(5), 1, 0),
            'NOT_FOUND'
        );
    }

    public function testBuryNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new Pheanstalk_Command_BuryCommand($this->_mockJob(5), 1),
            'NOT_FOUND'
        );
    }

    public function testIgnoreNotIgnored()
    {
        $this->_expectServerExceptionForResponse(
            new Pheanstalk_Command_IgnoreCommand('test'),
            'NOT_IGNORED'
        );
    }

    public function testTouchNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new Pheanstalk_Command_TouchCommand($this->_mockJob(5)),
            'NOT_FOUND'
        );
    }

    public function testPeekNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new Pheanstalk_Command_PeekCommand(5),
            'NOT_FOUND'
        );
    }

    /**
     * @expectedException Pheanstalk_Exception_CommandException
     */
    public function testPeekInvalidSubject()
    {
        new Pheanstalk_Command_PeekCommand('invalid');
    }

    public function testYamlResponseParserNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new Pheanstalk_YamlResponseParser(Pheanstalk_YamlResponseParser::MODE_DICT),
            'NOT_FOUND'
        );
    }

    public function testPauseTubeNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new Pheanstalk_Command_PauseTubeCommand('not-a-tube', 1),
            'NOT_FOUND'
        );
    }

    public function testPutUnhandledResponse()
    {
        $this->_expectExceptionForResponse(
            new Pheanstalk_Command_PutCommand('data', 0, 0, 0),
            'unhandled response'
        );
    }

    // ----------------------------------------

    /**
     * @param int $id
     */
    private function _mockJob($id)
    {
        $job = $this->getMockBuilder('Pheanstalk_Job')
            ->disableOriginalConstructor()
            ->getMock();
        $job->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));
        return $job;
    }

    /**
     * @param Pheanstalk_Command
     * @param string the response line to parse.
     * @param string the type of exception to expect.
     */
    private function _expectExceptionForResponse($command, $response, $type = 'Pheanstalk_Exception')
    {
        $this->setExpectedException($type);
        $command->parseResponse($response, null);
    }

    /**
     * @param Pheanstalk_Command
     * @param string the response line to parse.
     */
    private function _expectServerExceptionForResponse($command, $response)
    {
        $this->_expectExceptionForResponse($command, $response,
            'Pheanstalk_Exception_ServerException');
    }
}
