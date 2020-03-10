<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 11:43
 */

namespace AppBundle\Flows\Util;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use AppBundle\Entity\UploadAttachments;
use AppBundle\Helper\Message\UploadAttachment;
use AppBundle\Helper\MyFbBotApp;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class UploaderAttachmentItems
 * @package AppBundle\Flows\Util
 */
class UploaderAttachmentItems implements UploaderAttachmentItemsInterface
{
    /**
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @return mixed|void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function upload(EntityManager $em, Page $page, FlowItems $flowItem){
        if(!empty($flowItem->getItems())){
            $bot = new MyFbBotApp($page->getAccessToken());
            foreach($flowItem->getItems() as $item){
                if(isset($item['type']) && !empty($item['type']) && isset($item['params'])
                    && ((isset($item['params']['url']) && !empty($item['params']['url'])) || (isset($item['params']['img_url']) && !empty($item['params']['img_url'])))
                ) {
                    if($item['type'] == 'image' || $item['type'] == 'card' || $item['type'] == 'gallery' || $item['type'] == 'list' || $item['type'] == 'audio' || $item['type'] == 'video' || $item['type'] == 'file'){
                        if(isset($item['params']['url']) && !empty($item['params']['url'])){
                            $url = $item['params']['url'];
                        }
                        else{
                            $url = $item['params']['img_url'];
                        }
                        $checkUpload = $em->getRepository("AppBundle:UploadAttachments")->findOneBy(['page_id'=>$page->getPageId(), 'url'=>$url]);
                        if(!$checkUpload instanceof UploadAttachments){
                            if($item['type'] == 'audio'){
                                $typeAttachment = 'audio';
                            }
                            elseif($item['type'] == 'video'){
                                $typeAttachment = 'video';
                            }
                            elseif($item['type'] == 'file'){
                                $typeAttachment = 'file';
                            }
                            else{
                                $typeAttachment = 'image';
                            }
                            $upload = new UploadAttachment($url, $typeAttachment);
                            $result_upload = $bot->callWithUpperLimit('me/message_attachments', $upload->getData());

                            if(isset($result_upload['attachment_id']) && !empty($result_upload['attachment_id'])){
                                $uploadAttachment = new UploadAttachments($page->getPageId(), $url, $result_upload['attachment_id']);
                                $em->persist($uploadAttachment);
                                $em->flush();
                            }
                            else{
                                $fs = new Filesystem();
                                $fs->appendToFile('upload_attachment_error.txt', json_encode($result_upload)."\n\n");
                            }
                        }
                    }
                }
            }
        }
    }
}
