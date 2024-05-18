<?php


class Book extends Item
{
    private $_authors;
    private $_pages;
    private $_isbn;
    private $_cover;

    public function __construct($itemParams,$bookParams)
    {
        parent::__construct($itemParams);

        $this->_authors = $bookParams["authors"];
        $this->_pages = $bookParams["pages"];
        $this->_isbn = $bookParams["isbn"];
        $this->_cover = $bookParams["cover"];
    }

    /**
     * @return String The name of the authors
     */
    public function getAuthors()
    {
        return $this->_authors;
    }

    /**
     * @param String $authors
     */
    public function setAuthors($authors)
    {
        $this->_authors = $authors;
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
     * @return Integer The ISBN number
     */
    public function getIsbn()
    {
        return $this->_isbn;
    }

    /**
     * @param Integer $isbn
     */
    public function setIsbn($isbn)
    {
        $this->_isbn = $isbn;
    }

    /**
     * @return String The url to the cover image
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