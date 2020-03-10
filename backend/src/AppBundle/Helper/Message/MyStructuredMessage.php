<?php
/**
 * Created by PhpStorm.
 * Date: 07.12.18
 * Time: 10:47
 */

namespace AppBundle\Helper\Message;


use pimax\Messages\QuickReplyButton;
use pimax\Messages\StructuredMessage;

/**
 * Class MyStructuredMessage
 * @package AppBundle\Helper\Message
 */
class MyStructuredMessage extends StructuredMessage
{

    /**
     * @return array
     */
    public function getData()
    {

        $result = [
            'attachment' => [
                'type' => 'template',
                'payload' => [
                    'template_type' => $this->type
                ]
            ]
        ];

        if (is_array($this->quick_replies)) {
            foreach ($this->quick_replies as $qr) {
                if ($qr instanceof QuickReplyButton) {
                    $result['quick_replies'][] = $qr->getData();
                }
            }
        }

        switch ($this->type)
        {
            case self::TYPE_BUTTON:
                $result['attachment']['payload']['text'] = $this->title;
                $result['attachment']['payload']['buttons'] = [];

                foreach ($this->buttons as $btn) {
                    $result['attachment']['payload']['buttons'][] = $btn->getData();
                }

                break;

            case self::TYPE_GENERIC:
                $result['attachment']['payload']['elements'] = [];
                $result['attachment']['payload']['image_aspect_ratio'] = $this->image_aspect_ratio;

                foreach ($this->elements as $btn) {
                    $result['attachment']['payload']['elements'][] = $btn->getData();
                }
                break;

            case self::TYPE_MEDIA:
                $result['attachment']['payload']['elements'] = [];
                foreach ($this->elements as $btn) {
                    $data = $btn->getData();
                    if(isset($data['type'])){
                        $data['media_type'] = $data['type'];
                        unset($data['type']);
                    }
                    if(isset($data['attachment_id']) && !empty($data['attachment_id'])){
                        if(isset($data['url'])){
                            unset($data['url']);
                        }
                    }
                    $result['attachment']['payload']['elements'][] = $data;
                }
                break;

            case self::TYPE_LIST:
                $result['attachment']['payload']['elements'] = [];
                $result['attachment']['payload']['top_element_style'] = $this->top_element_style;
                //list items button
                foreach ($this->elements as $btn) {
                    $result['attachment']['payload']['elements'][] = $btn->getData();
                }
                //the whole list button
                foreach ($this->buttons as $btn) {
                    $result['attachment']['payload']['buttons'][] = $btn->getData();
                }
                break;

            case self::TYPE_RECEIPT:
                $result['attachment']['payload']['recipient_name'] = $this->recipient_name;
                $result['attachment']['payload']['order_number'] = $this->order_number;
                $result['attachment']['payload']['currency'] = $this->currency;
                $result['attachment']['payload']['payment_method'] = $this->payment_method;
                $result['attachment']['payload']['order_url'] = $this->order_url;
                $result['attachment']['payload']['timestamp'] = $this->timestamp;
                $result['attachment']['payload']['elements'] = [];

                foreach ($this->elements as $btn) {
                    $result['attachment']['payload']['elements'][] = $btn->getData();
                }

                $result['attachment']['payload']['address'] = $this->address->getData();
                $result['attachment']['payload']['summary'] = $this->summary->getData();
                $result['attachment']['payload']['adjustments'] = [];

                foreach ($this->adjustments as $btn) {
                    $result['attachment']['payload']['adjustments'][] = $btn->getData();
                }
                break;
        }


        if ($this->recipient) {
            return [
                'recipient' =>  [
                    'id' => $this->recipient
                ],
                'message' => $result,
                'tag' => $this->tag,
                'notification_type'=> $this->notification_type,
                'messaging_type' => $this->messaging_type
            ];
        } else {
            //share_contents only
            return [
                'attachment' => $result['attachment']
            ];
        }
    }
}