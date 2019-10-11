<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/automl/v1/operations.proto

namespace Google\Cloud\AutoMl\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Metadata used across all long running operations returned by AutoML API.
 *
 * Generated from protobuf message <code>google.cloud.automl.v1.OperationMetadata</code>
 */
class OperationMetadata extends \Google\Protobuf\Internal\Message
{
    /**
     * Output only. Progress of operation. Range: [0, 100].
     * Not used currently.
     *
     * Generated from protobuf field <code>int32 progress_percent = 13;</code>
     */
    private $progress_percent = 0;
    /**
     * Output only. Partial failures encountered.
     * E.g. single files that couldn't be read.
     * This field should never exceed 20 entries.
     * Status details field will contain standard GCP error details.
     *
     * Generated from protobuf field <code>repeated .google.rpc.Status partial_failures = 2;</code>
     */
    private $partial_failures;
    /**
     * Output only. Time when the operation was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 3;</code>
     */
    private $create_time = null;
    /**
     * Output only. Time when the operation was updated for the last time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 4;</code>
     */
    private $update_time = null;
    protected $details;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\AutoMl\V1\DeleteOperationMetadata $delete_details
     *           Details of a Delete operation.
     *     @type \Google\Cloud\AutoMl\V1\CreateModelOperationMetadata $create_model_details
     *           Details of CreateModel operation.
     *     @type int $progress_percent
     *           Output only. Progress of operation. Range: [0, 100].
     *           Not used currently.
     *     @type \Google\Rpc\Status[]|\Google\Protobuf\Internal\RepeatedField $partial_failures
     *           Output only. Partial failures encountered.
     *           E.g. single files that couldn't be read.
     *           This field should never exceed 20 entries.
     *           Status details field will contain standard GCP error details.
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Output only. Time when the operation was created.
     *     @type \Google\Protobuf\Timestamp $update_time
     *           Output only. Time when the operation was updated for the last time.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Automl\V1\Operations::initOnce();
        parent::__construct($data);
    }

    /**
     * Details of a Delete operation.
     *
     * Generated from protobuf field <code>.google.cloud.automl.v1.DeleteOperationMetadata delete_details = 8;</code>
     * @return \Google\Cloud\AutoMl\V1\DeleteOperationMetadata
     */
    public function getDeleteDetails()
    {
        return $this->readOneof(8);
    }

    /**
     * Details of a Delete operation.
     *
     * Generated from protobuf field <code>.google.cloud.automl.v1.DeleteOperationMetadata delete_details = 8;</code>
     * @param \Google\Cloud\AutoMl\V1\DeleteOperationMetadata $var
     * @return $this
     */
    public function setDeleteDetails($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AutoMl\V1\DeleteOperationMetadata::class);
        $this->writeOneof(8, $var);

        return $this;
    }

    /**
     * Details of CreateModel operation.
     *
     * Generated from protobuf field <code>.google.cloud.automl.v1.CreateModelOperationMetadata create_model_details = 10;</code>
     * @return \Google\Cloud\AutoMl\V1\CreateModelOperationMetadata
     */
    public function getCreateModelDetails()
    {
        return $this->readOneof(10);
    }

    /**
     * Details of CreateModel operation.
     *
     * Generated from protobuf field <code>.google.cloud.automl.v1.CreateModelOperationMetadata create_model_details = 10;</code>
     * @param \Google\Cloud\AutoMl\V1\CreateModelOperationMetadata $var
     * @return $this
     */
    public function setCreateModelDetails($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AutoMl\V1\CreateModelOperationMetadata::class);
        $this->writeOneof(10, $var);

        return $this;
    }

    /**
     * Output only. Progress of operation. Range: [0, 100].
     * Not used currently.
     *
     * Generated from protobuf field <code>int32 progress_percent = 13;</code>
     * @return int
     */
    public function getProgressPercent()
    {
        return $this->progress_percent;
    }

    /**
     * Output only. Progress of operation. Range: [0, 100].
     * Not used currently.
     *
     * Generated from protobuf field <code>int32 progress_percent = 13;</code>
     * @param int $var
     * @return $this
     */
    public function setProgressPercent($var)
    {
        GPBUtil::checkInt32($var);
        $this->progress_percent = $var;

        return $this;
    }

    /**
     * Output only. Partial failures encountered.
     * E.g. single files that couldn't be read.
     * This field should never exceed 20 entries.
     * Status details field will contain standard GCP error details.
     *
     * Generated from protobuf field <code>repeated .google.rpc.Status partial_failures = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPartialFailures()
    {
        return $this->partial_failures;
    }

    /**
     * Output only. Partial failures encountered.
     * E.g. single files that couldn't be read.
     * This field should never exceed 20 entries.
     * Status details field will contain standard GCP error details.
     *
     * Generated from protobuf field <code>repeated .google.rpc.Status partial_failures = 2;</code>
     * @param \Google\Rpc\Status[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPartialFailures($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Rpc\Status::class);
        $this->partial_failures = $arr;

        return $this;
    }

    /**
     * Output only. Time when the operation was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 3;</code>
     * @return \Google\Protobuf\Timestamp
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * Output only. Time when the operation was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 3;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setCreateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->create_time = $var;

        return $this;
    }

    /**
     * Output only. Time when the operation was updated for the last time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 4;</code>
     * @return \Google\Protobuf\Timestamp
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    /**
     * Output only. Time when the operation was updated for the last time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 4;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setUpdateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->update_time = $var;

        return $this;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->whichOneof("details");
    }

}

