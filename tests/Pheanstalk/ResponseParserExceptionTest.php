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
            new \Pheanstalk\Command\DeleteCommand($this->_mockJob(5)),
            'NOT_FOUND'
        );
    }

    public function testReleaseBuried()
    {
        $this->_expectServerExceptionForResponse(
            new \Pheanstalk\Command\ReleaseCommand($this->_mockJob(5), 1, 0),
            'BURIED'
        );
    }

    public function testReleaseNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new \Pheanstalk\Command\ReleaseCommand($this->_mockJob(5), 1, 0),
            'NOT_FOUND'
        );
    }

    public function testBuryNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new \Pheanstalk\Command\BuryCommand($this->_mockJob(5), 1),
            'NOT_FOUND'
        );
    }

    public function testIgnoreNotIgnored()
    {
        $this->_expectServerExceptionForResponse(
            new \Pheanstalk\Command\IgnoreCommand('test'),
            'NOT_IGNORED'
        );
    }

    public function testTouchNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new \Pheanstalk\Command\TouchCommand($this->_mockJob(5)),
            'NOT_FOUND'
        );
    }

    public function testPeekNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new \Pheanstalk\Command\PeekCommand(5),
            'NOT_FOUND'
        );
    }

    /**
     * @expectedException \Pheanstalk\Exception\CommandException
     */
    public function testPeekInvalidSubject()
    {
        new \Pheanstalk\Command\PeekCommand('invalid');
    }

    public function testYamlResponseParserNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new \Pheanstalk\YamlResponseParser(\Pheanstalk\YamlResponseParser::MODE_DICT),
            'NOT_FOUND'
        );
    }

    public function testPauseTubeNotFound()
    {
        $this->_expectServerExceptionForResponse(
            new \Pheanstalk\Command\PauseTubeCommand('not-a-tube', 1),
            'NOT_FOUND'
        );
    }

    public function testPutUnhandledResponse()
    {
        $this->_expectExceptionForResponse(
            new \Pheanstalk\Command\PutCommand('data', 0, 0, 0),
            'unhandled response'
        );
    }

    // ----------------------------------------

    /**
     * @param int $id
     */
    private function _mockJob($id)
    {
        $job = $this->getMockBuilder('\Pheanstalk\Job')
            ->disableOriginalConstructor()
            ->getMock();
        $job->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));
        return $job;
    }

    /**
     * @param \Pheanstalk\Command
     * @param string the response line to parse.
     * @param string the type of exception to expect.
     */
    private function _expectExceptionForResponse($command, $response, $type = '\Pheanstalk\Exception')
    {
        $this->setExpectedException($type);
        $command->parseResponse($response, null);
    }

    /**
     * @param \Pheanstalk\Command
     * @param string the response line to parse.
     */
    private function _expectServerExceptionForResponse($command, $response)
    {
        $this->_expectExceptionForResponse($command, $response,
            '\Pheanstalk\Exception\ServerException');
    }
}
