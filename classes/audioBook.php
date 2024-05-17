<?php

class AudioBook extends Book
{
    private $_length;
    private $_narrator;

    public function __construct($params, $author, $pages, $isbn, $cover, $length, $narrator)
    {
        parent::__construct($params, $author, $pages, $isbn, $cover);

        $this->_length = $length;
        $this->_narrator = $narrator;
    }

    /**
     * @return Integer Length of audiobook in minutes
     */
    public function getLength()
    {
        return $this->_length;
    }

    /**
     * @param Integer $length
     */
    public function setLength($length)
    {
        $this->_length = $length;
    }

    /**
     * @return String The name of the narrator
     */
    public function getNarrator()
    {
        return $this->_narrator;
    }

    /**
     * @param String $narrator
     */
    public function setNarrator($narrator)
    {
        $this->_narrator = $narrator;
    }


}