<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/intent.proto

namespace Google\Cloud\Dialogflow\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The request message for [Intents.BatchUpdateIntents][google.cloud.dialogflow.v2.Intents.BatchUpdateIntents].
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.BatchUpdateIntentsRequest</code>
 */
class BatchUpdateIntentsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The name of the agent to update or create intents in.
     * Format: `projects/<Project ID>/agent`.
     *
     * Generated from protobuf field <code>string parent = 1;</code>
     */
    private $parent = '';
    /**
     * Optional. The language of training phrases, parameters and rich messages
     * defined in `intents`. If not specified, the agent's default language is
     * used. [Many
     * languages](https://cloud.google.com/dialogflow/docs/reference/language)
     * are supported. Note: languages must be enabled in the agent before they can
     * be used.
     *
     * Generated from protobuf field <code>string language_code = 4;</code>
     */
    private $language_code = '';
    /**
     * Optional. The mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 5;</code>
     */
    private $update_mask = null;
    /**
     * Optional. The resource view to apply to the returned intent.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.IntentView intent_view = 6;</code>
     */
    private $intent_view = 0;
    protected $intent_batch;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The name of the agent to update or create intents in.
     *           Format: `projects/<Project ID>/agent`.
     *     @type string $intent_batch_uri
     *           The URI to a Google Cloud Storage file containing intents to update or
     *           create. The file format can either be a serialized proto (of IntentBatch
     *           type) or JSON object. Note: The URI must start with "gs://".
     *     @type \Google\Cloud\Dialogflow\V2\IntentBatch $intent_batch_inline
     *           The collection of intents to update or create.
     *     @type string $language_code
     *           Optional. The language of training phrases, parameters and rich messages
     *           defined in `intents`. If not specified, the agent's default language is
     *           used. [Many
     *           languages](https://cloud.google.com/dialogflow/docs/reference/language)
     *           are supported. Note: languages must be enabled in the agent before they can
     *           be used.
     *     @type \Google\Protobuf\FieldMask $update_mask
     *           Optional. The mask to control which fields get updated.
     *     @type int $intent_view
     *           Optional. The resource view to apply to the returned intent.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\Intent::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The name of the agent to update or create intents in.
     * Format: `projects/<Project ID>/agent`.
     *
     * Generated from protobuf field <code>string parent = 1;</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The name of the agent to update or create intents in.
     * Format: `projects/<Project ID>/agent`.
     *
     * Generated from protobuf field <code>string parent = 1;</code>
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
     * The URI to a Google Cloud Storage file containing intents to update or
     * create. The file format can either be a serialized proto (of IntentBatch
     * type) or JSON object. Note: The URI must start with "gs://".
     *
     * Generated from protobuf field <code>string intent_batch_uri = 2;</code>
     * @return string
     */
    public function getIntentBatchUri()
    {
        return $this->readOneof(2);
    }

    /**
     * The URI to a Google Cloud Storage file containing intents to update or
     * create. The file format can either be a serialized proto (of IntentBatch
     * type) or JSON object. Note: The URI must start with "gs://".
     *
     * Generated from protobuf field <code>string intent_batch_uri = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setIntentBatchUri($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(2, $var);

        return $this;
    }

    /**
     * The collection of intents to update or create.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.IntentBatch intent_batch_inline = 3;</code>
     * @return \Google\Cloud\Dialogflow\V2\IntentBatch
     */
    public function getIntentBatchInline()
    {
        return $this->readOneof(3);
    }

    /**
     * The collection of intents to update or create.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.IntentBatch intent_batch_inline = 3;</code>
     * @param \Google\Cloud\Dialogflow\V2\IntentBatch $var
     * @return $this
     */
    public function setIntentBatchInline($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Dialogflow\V2\IntentBatch::class);
        $this->writeOneof(3, $var);

        return $this;
    }

    /**
     * Optional. The language of training phrases, parameters and rich messages
     * defined in `intents`. If not specified, the agent's default language is
     * used. [Many
     * languages](https://cloud.google.com/dialogflow/docs/reference/language)
     * are supported. Note: languages must be enabled in the agent before they can
     * be used.
     *
     * Generated from protobuf field <code>string language_code = 4;</code>
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->language_code;
    }

    /**
     * Optional. The language of training phrases, parameters and rich messages
     * defined in `intents`. If not specified, the agent's default language is
     * used. [Many
     * languages](https://cloud.google.com/dialogflow/docs/reference/language)
     * are supported. Note: languages must be enabled in the agent before they can
     * be used.
     *
     * Generated from protobuf field <code>string language_code = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setLanguageCode($var)
    {
        GPBUtil::checkString($var, True);
        $this->language_code = $var;

        return $this;
    }

    /**
     * Optional. The mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 5;</code>
     * @return \Google\Protobuf\FieldMask
     */
    public function getUpdateMask()
    {
        return $this->update_mask;
    }

    /**
     * Optional. The mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 5;</code>
     * @param \Google\Protobuf\FieldMask $var
     * @return $this
     */
    public function setUpdateMask($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\FieldMask::class);
        $this->update_mask = $var;

        return $this;
    }

    /**
     * Optional. The resource view to apply to the returned intent.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.IntentView intent_view = 6;</code>
     * @return int
     */
    public function getIntentView()
    {
        return $this->intent_view;
    }

    /**
     * Optional. The resource view to apply to the returned intent.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.IntentView intent_view = 6;</code>
     * @param int $var
     * @return $this
     */
    public function setIntentView($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Dialogflow\V2\IntentView::class);
        $this->intent_view = $var;

        return $this;
    }

    /**
     * @return string
     */
    public function getIntentBatch()
    {
        return $this->whichOneof("intent_batch");
    }

}
