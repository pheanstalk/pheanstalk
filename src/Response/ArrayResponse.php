<?php

declare(strict_types=1);

namespace Pheanstalk\Response;

use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\ResponseType;

/**
 * A response with an ArrayObject interface to key => value data.
 *
 * @author  Paul Annesley
 */
class ArrayResponse extends \ArrayObject implements ResponseInterface
{
    public function __construct(private ResponseType $responseType, array $data)
    {
        parent::__construct($data);
    }

    public function getResponseType(): ResponseType
    {
        return $this->responseType;
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
