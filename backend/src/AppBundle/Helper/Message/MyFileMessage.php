<?php


namespace AppBundle\Helper\Message;


use pimax\Messages\Attachment;
use pimax\Messages\Message;

/**
 * Class MyFileMessage
 * @package AppBundle\Helper\Message
 */
class MyFileMessage extends Message
{

    /**
     * MyFileMessage constructor.
     * @param $recipient
     * @param $file
     * @param array $quick_replies
     * @param null $tag
     * @param string $notification_type
     * @param string $messaging_type
     */
    public function __construct($recipient, $file, $quick_replies = array(), $tag = null, $notification_type = parent::NOTIFY_REGULAR, $messaging_type = parent::TYPE_RESPONSE)
    {
        $this->recipient = $recipient;
        $this->text = $file;
        $this->quick_replies = $quick_replies;
        $this->tag = $tag;
        $this->notification_type = $notification_type;
        $this->messaging_type = $messaging_type;
    }

    /**
     * Get message data
     *
     * @return array
     */
    public function getData()
    {
        $res = [
            'recipient' =>  [
                'id' => $this->recipient
            ],
            'tag' => $this->tag,
            'notification_type'=> $this->notification_type,
            'messaging_type' => $this->messaging_type
        ];

        $attachment = new Attachment(Attachment::TYPE_FILE, [], $this->quick_replies);

        if (strcmp(intval($this->text), $this->text) === 0) {
            $attachment->setPayload(array('attachment_id' => $this->text));
        } elseif (strpos($this->text, 'http://') === 0 || strpos($this->text, 'https://') === 0) {
            $attachment->setPayload(array('url' => $this->text));
        } else {
            $attachment->setFileData($this->getCurlValue($this->text, mime_content_type($this->text), basename($this->text)));
        }

        $res['message'] = $attachment->getData();

        return $res;
    }
}
