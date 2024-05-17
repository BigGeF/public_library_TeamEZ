<?php


class Book extends Item
{
    private $_author;
    private $_pages;
    private $_isbn;
    private $_cover;

    public function __construct($params, $author, $pages, $isbn, $cover)
    {
        parent::__construct($params);

        $this->_author = $author;
        $this->_pages = $pages;
        $this->_isbn = $isbn;
        $this->_cover = $cover;
    }

    /**
     * @return String The name of the author
     */
    public function getAuthor()
    {
        return $this->_author;
    }

    /**
     * @param String $author
     */
    public function setAuthor($author)
    {
        $this->_author = $author;
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