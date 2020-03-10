<?php


namespace AppBundle\Pages;


use AppBundle\Entity\Folders;
use AppBundle\Entity\Page;
use AppBundle\Entity\PageShare;
use Doctrine\ORM\EntityManager;

/**
 * Class SharePage
 * @package AppBundle\Pages
 */
class SharePage extends ClonePage
{
    /**
     * SharePage constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param Page $clonePage
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function __construct(EntityManager $em, Page $page, Page $clonePage)
    {
        $this->em = $em;
        $this->page = $page;
        $this->clonePage = $clonePage;
        $this->folder = new Folders($clonePage->getPageId(), 'Geteilt von '.$page->getTitle());
        $this->em->persist($this->folder);
        $this->em->flush();
    }

    /**
     * @param array $options
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function share(array $options){
        //Clone All Flows
        if(array_key_exists('flows', $options) && $options['flows'] == true){
            $this->cloneAllFlows();
        }
        //Clone All Sequences
        if(array_key_exists('sequences', $options) && $options['sequences'] == true){
            $this->cloneAllSequences();
        }
        //Clone All Widgets
        if(array_key_exists('widgets', $options) && $options['widgets'] == true){
            $this->cloneAllWidgets();
        }
        //Clone All Keywords
        if(array_key_exists('keywords', $options) && $options['keywords'] == true){
            $this->cloneAllKeywords();
        }
        //Clone Welcome Message
        if(array_key_exists('welcomeMessage', $options) && $options['welcomeMessage'] == true){
            $this->cloneWelcomeMessage();
        }
        //Clone Default Reply
        if(array_key_exists('defaultReply', $options) && $options['defaultReply'] == true){
            $this->cloneDefaultReply();
        }
        //Clone Main Menu
        if(array_key_exists('mainMenu', $options) && $options['mainMenu'] == true){
            $this->cloneMainMenu();
        }
    }
}
