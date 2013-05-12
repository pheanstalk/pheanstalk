<?php

/**
 * Use PHP's json_encode/json_decode on the job data
 *
 * @author Marius Ghita
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_JSONTransformer implements Pheanstalk_TransformerInterface
{

    protected $encode_options;
    protected $decode_assoc;
    protected $decode_depth;
    protected $decode_options;

    /**
     * Provide json encoding/decoding specific parameters
     *
     * @see json_encode
     * @see json_decode
     * @param int $encode_options
     * @param bool $decode_assoc
     */
    public function __construct($encode_options = 0, $decode_assoc = false, $decode_depth = 512, $decode_options = 0)
    {
        $this->encode_options = $encode_options;
        $this->decode_assoc   = $decode_assoc;
        $this->decode_depth   = $decode_depth;
        $this->decode_options = $decode_options;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($jobdata)
    {
        return json_encode($jobdata, $this->encode_options);
    }


    /**
     * {@inheritdoc}
     * @todo check for errors in decoding and report appropiately. With a typed exception?
     */
    public function inverseTransform($jobdata)
    {
        return json_decode($jobdata, $this->decode_assoc, $this->decode_depth, $this->decode_options);
    }
}
