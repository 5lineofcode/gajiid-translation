<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/bigquery/datatransfer/v1/datatransfer.proto

namespace Google\Cloud\BigQuery\DataTransfer\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A request to schedule transfer runs for a time range.
 *
 * Generated from protobuf message <code>google.cloud.bigquery.datatransfer.v1.ScheduleTransferRunsRequest</code>
 */
class ScheduleTransferRunsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Transfer configuration name in the form:
     * `projects/{project_id}/transferConfigs/{config_id}`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. Start time of the range of transfer runs. For example,
     * `"2017-05-25T00:00:00+00:00"`.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp start_time = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $start_time = null;
    /**
     * Required. End time of the range of transfer runs. For example,
     * `"2017-05-30T00:00:00+00:00"`.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp end_time = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $end_time = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. Transfer configuration name in the form:
     *           `projects/{project_id}/transferConfigs/{config_id}`.
     *     @type \Google\Protobuf\Timestamp $start_time
     *           Required. Start time of the range of transfer runs. For example,
     *           `"2017-05-25T00:00:00+00:00"`.
     *     @type \Google\Protobuf\Timestamp $end_time
     *           Required. End time of the range of transfer runs. For example,
     *           `"2017-05-30T00:00:00+00:00"`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Bigquery\Datatransfer\V1\Datatransfer::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Transfer configuration name in the form:
     * `projects/{project_id}/transferConfigs/{config_id}`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. Transfer configuration name in the form:
     * `projects/{project_id}/transferConfigs/{config_id}`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setParent($var)
    {
        GPBUtil::checkString($var, True);
        $this->parent = $var;

        return $this;
    }

    /**
     * Required. Start time of the range of transfer runs. For example,
     * `"2017-05-25T00:00:00+00:00"`.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp start_time = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\Timestamp
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * Required. Start time of the range of transfer runs. For example,
     * `"2017-05-25T00:00:00+00:00"`.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp start_time = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setStartTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->start_time = $var;

        return $this;
    }

    /**
     * Required. End time of the range of transfer runs. For example,
     * `"2017-05-30T00:00:00+00:00"`.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp end_time = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\Timestamp
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * Required. End time of the range of transfer runs. For example,
     * `"2017-05-30T00:00:00+00:00"`.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp end_time = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setEndTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->end_time = $var;

        return $this;
    }

}

