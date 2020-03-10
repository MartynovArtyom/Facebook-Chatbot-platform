<?php


namespace AppBundle\Pages;


interface ClonePageInterface
{
    /**
     * @return mixed
     */
    public function cloneAll();

    /**
     * @return mixed
     */
    public function cloneAllFlows();

    /**
     * @return mixed
     */
    public function cloneAllSequences();

    /**
     * @return mixed
     */
    public function cloneAllWidgets();

    /**
     * @return mixed
     */
    public function cloneAllKeywords();

    /**
     * @return mixed
     */
    public function cloneWelcomeMessage();

    /**
     * @return mixed
     */
    public function cloneDefaultReply();

    /**
     * @return mixed
     */
    public function cloneMainMenu();

}
