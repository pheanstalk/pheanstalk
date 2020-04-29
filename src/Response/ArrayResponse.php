<?php

namespace Pheanstalk\Response;

use Pheanstalk\Contract\ResponseInterface;

/**
 * A response with an ArrayObject interface to key => value data.
 *
 * @author  Paul Annesley
 */
class ArrayResponse extends \ArrayObject implements ResponseInterface
{
    private $name;

    /**
     * @param string $name
     * @param array  $data
     */
    public function __construct(string $name, array $data)
    {
        $this->name = $name;
        parent::__construct($data);
    }

    public function getResponseName(): string
    {
        return $this->name;
    }

    /**
     * Object property access to ArrayObject data.
     */
    public function __get($property)
    {
        $key = $this->transformPropertyName($property);

        return $this[$key] ?? null;
    }

    /**
     * Object property access to ArrayObject data.
     */
    public function __isset($property)
    {
        $key = $this->transformPropertyName($property);

        return isset($this[$key]);
    }

    // ----------------------------------------

    /**
     * Tranform underscored property name to hyphenated array key.
     */
    private function transformPropertyName(string $propertyName): string
    {
        return str_replace('_', '-', $propertyName);
    }
}
