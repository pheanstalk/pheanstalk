<?php

namespace Pheanstalk\Command;
use Pheanstalk\IResponseParser;
use Pheanstalk\IResponse;

use Pheanstalk\Exception\ServerException;

/**
 * The 'ignore' command.
 * Removes a tube from the watch list to reserve jobs from.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class IgnoreCommand extends AbstractCommand implements IResponseParser
{
    private $_tube;

    /**
     * @param string $tube
     */
    public function __construct($tube)
    {
        $this->_tube = $tube;
    }

    /* (non-phpdoc)
	 * @see \Pheanstalk\ICommand::getCommandLine()
     */
    public function getCommandLine()
    {
        return 'ignore '.$this->_tube;
    }

    /* (non-phpdoc)
	 * @see \Pheanstalk\IResponseParser::parseRespose()
     */
    public function parseResponse($responseLine, $responseData)
    {
        if (preg_match('#^WATCHING (\d+)$#', $responseLine, $matches)) {
            return $this->_createResponse('WATCHING', array(
                'count' => (int)$matches[1]
            ));
		}
		elseif ($responseLine == IResponse::RESPONSE_NOT_IGNORED)
		{
			throw new ServerException($responseLine .
				': cannot ignore last tube in watchlist');
		}
		else
		{
			throw new \Pheanstalk\Exception('Unhandled response: '.$responseLine);
        }
    }
}
