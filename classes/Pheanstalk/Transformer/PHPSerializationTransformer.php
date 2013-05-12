<?php

/**
 * Use PHP's serialize/unserialize on the job data
 *
 * @author Marius Ghita
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_PHPSerializationTransformer implements Pheanstalk_TransformerInterface
{

    /**
     * {@inheritDoc}
     */
    public function transform($jobdata)
    {
        return serialize($jobdata);
    }


    /**
     * {@inheritDoc}
     */
    public function inverseTransform($jobdata)
    {
        return unserialize($jobdata);
    }
}
