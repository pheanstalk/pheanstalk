<?php

/**
 * A response with an ArrayObject interface to key=>value data
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Response_ArrayResponse
	extends ArrayObject
	implements Pheanstalk_Response
{
	private $_name;

	/**
	 * Constructor
	 */
	public function __construct($name, $data)
	{
		$this->_name = $name;
		parent::__construct($data);
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Response::getResponseName()
	 */
	public function getResponseName()
	{
		return $this->_name;
	}
}
