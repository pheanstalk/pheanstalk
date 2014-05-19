<?php

/**
 * Interface by which job data transformations can be provided. By default messages
 * are strings, but with this interface a way to transform/serialize that data upon
 * storage and retrieval can be specified.
 *
 * @author Marius Ghita
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
interface Pheanstalk_TransformerInterface
{
    /**
     * @param  string $jobdata
     * @return string
     */
    public function transform($jobdata);

    /**
     * @param  string $jobdata
     * @return string
     */
    public function inverseTransform($jobdata);
}
