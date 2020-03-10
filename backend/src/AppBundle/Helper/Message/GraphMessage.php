<?php
/**
 * Created by PhpStorm.
 * Date: 21.12.18
 * Time: 15:37
 */

namespace AppBundle\Helper\Message;


use pimax\Messages\Attachment;
use pimax\Messages\Message;

class GraphMessage extends Message
{
    /**
     * @var null
     */
    protected $url;

    /**
     * GraphMessage constructor.
     * @param null $recipient
     * @param null $url
     * @param bool|string $notification_type
     */
    public function __construct($recipient=null, $url=null, $notification_type = parent::NOTIFY_REGULAR)
    {
        $this->recipient=$recipient;
        $this->url=$url;
        $this->notification_type = $notification_type;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $res = [
            'recipient' => [
                'id' => $this->recipient
            ],
            'message' => [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'open_graph',
                        'elements' => [
                            [
                                'url' => $this->url
                            ]
                        ]
                    ]
                ]
            ],
            'notification_type'=> $this->notification_type,
        ];

        return $res;
    }
}