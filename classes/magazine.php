<?php

class Magazine extends Item
{
    private $_pages;
    private $_cover;

    public function __construct($params, $pages, $cover)
    {
        parent::__construct($params);

        $this->_pages = $pages;
        $this->_cover = $cover;
    }

    /**
     * @return Integer The page count
     */
    public function getPages()
    {
        return $this->_pages;
    }

    /**
     * @param Integer $pages
     */
    public function setPages($pages)
    {
        $this->_pages = $pages;
    }

    /**
     * @return String The url for the cover image
     */
    public function getCover()
    {
        return $this->_cover;
    }

    /**
     * @param String $cover
     */
    public function setCover($cover)
    {
        $this->_cover = $cover;
    }


}