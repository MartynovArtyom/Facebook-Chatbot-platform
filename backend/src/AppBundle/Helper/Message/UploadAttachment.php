<?php
/**
 * Created by PhpStorm.
 * Date: 06.12.18
 * Time: 16:31
 */

namespace AppBundle\Helper\Message;


use pimax\Messages\Attachment;
use pimax\Messages\Message;
use Symfony\Component\HttpFoundation\Request;

class UploadAttachment extends Message
{
    const TYPE_IMAGE = 'image';
    const TYPE_AUDIO = 'audio';
    const TYPE_VIDEO = 'video';
    const TYPE_FILE = 'file';

    protected $url;

    protected $type;

    /**
     * UploadAttachment constructor.
     * @param $url
     * @param $type
     */
    public function __construct($url, $type)
    {
        $this->url = $url;
        $this->type = $type;
    }


    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return array
     */
    public function getData()
    {
        $res = [];

        $attachment = new Attachment($this->getType());
        $attachment->setPayload(['url'=>$this->getUrl(),'is_reusable'=>true]);
        $res['message'] = $attachment->getData();

        return $res;
    }

    /**
     * @return array
     */
    public function getDataNew(){
        $request = Request::createFromGlobals();
        $filePath = substr($this->getUrl(), strlen($request->getSchemeAndHttpHost().'/'));
        $fileMime = mime_content_type($filePath);
        $file_name_with_full_path = realpath($filePath);
        $attachment = new Attachment($this->getType());
        $attachment->setPayload(['is_reusable'=>true]);
        $res = [
            'message' => json_encode($attachment->getData()),
            'file_contents'=>'@'.$file_name_with_full_path.';'.$fileMime
        ];


        return $res;
    }
}
