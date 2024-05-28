<?php

class Magazine extends Item implements JsonSerializable
{
    private $_pages;
    private $_cover;

    public function __construct($itemParams, $magazineParams)
    {
        parent::__construct($itemParams);

        $this->_pages = $magazineParams["pages"];
        $this->_cover = $magazineParams["cover"];
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


    // Only put properties here that you want serialized.
    public function jsonSerialize() {
        return Array(
            'id'    => $this->_id,
            'title'   => $this->_title,
            'description' => $this->_description,
            'publishedDate'     => $this->_publishedDate,
            'available' => $this->_available,
            'borrowedDate'    => $this->_borrowedDate, // example for other objects
            'returnDate'   => $this->_returnDate,
            'borrower'   => $this->_borrower,
            'hold'   => $this->_holds,
            'pages' => $this->_pages,
            'cover' => $this->_cover
        );
    }
}