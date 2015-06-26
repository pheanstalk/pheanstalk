<?php

namespace Pheanstalk\Response;

use Pheanstalk\Response;

/**
 * A response with an ArrayObject interface to key=>value data
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class ArrayResponse
    extends \ArrayObject
    implements Response
{
    private $_name;

    /**
     * @param string $name
     * @param array  $data
     */
    public function __construct($name, $data)
    {
        $this->_name = $name;
        parent::__construct($data);
    }

    /* (non-phpdoc)
     * @see Response::getResponseName()
     */
    public function getResponseName()
    {
        return $this->_name;
    }

    /**
     * Object property access to ArrayObject data.
     */
    public function __get($property)
    {
        $key = $this->_transformPropertyName($property);

        return isset($this[$key]) ? $this[$key] : null;
    }

    /**
     * Object property access to ArrayObject data.
     */
    public function __isset($property)
    {
        $key = $this->_transformPropertyName($property);

        return isset($this[$key]);
    }

    // ----------------------------------------

    /**
     * Tranform underscored property name to hyphenated array key.
     * @param string
     * @return string
     */
    private function _transformPropertyName($propertyName)
    {
        return str_replace('_', '-', $propertyName);
    }
}
