<?php


namespace AppBundle\MainMenu;

/**
 * Interface MenuMainInterface
 * @package AppBundle\MainMenu
 */
interface MenuMainInterface
{
    /**
     * @return mixed
     */
    public function getData();

    /**
     * @return mixed
     */
    public function publish();

    /**
     * @param $items
     * @return mixed
     */
    public function generateMenuItems($items);

    /**
     * @param $parentID
     * @return mixed
     */
    public function generateData($parentID);
}
