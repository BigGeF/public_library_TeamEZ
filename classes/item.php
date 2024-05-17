<?php

abstract class Item {
    protected $_id;
    protected $_title;
    protected $_description;
    protected $_publishedDate;
    protected $_available;
    protected $_borrowedDate;
    protected $_returnDate;
    protected $_borrower;
    protected $_holds;

    public function __construct($params)
    {
        $this->_id = $params['id'];
        $this->_title = $params['title'];
        $this->_description = $params['desc'];
        $this->_publishedDate = $params['pubDate'];
        $this->_available = $params['available'];
        $this->_borrowedDate = $params['borrowDate'];
        $this->_returnDate = $params['returnDate'];
        $this->_borrower = $params['borrower'];
        $this->_holds = $params['holds'];
    }

    /**
     * @return Integer The id of the item
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param Integer $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return String The title of the item
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @param String $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return String The description of the item
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param String $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * @return DateTime The date the item was published
     */
    public function getPublishedDate()
    {
        return $this->_publishedDate;
    }

    /**
     * @param DateTime $publishedDate
     */
    public function setPublishedDate($publishedDate)
    {
        $this->_publishedDate = $publishedDate;
    }

    /**
     * @return Boolean True if the item is available; False if not available
     */
    public function getAvailable()
    {
        return $this->_available;
    }

    /**
     * @param Boolean $available
     */
    public function setAvailable($available)
    {
        $this->_available = $available;
    }

    /**
     * @return DateTime The date the item was borrowed last
     */
    public function getBorrowedDate()
    {
        return $this->_borrowedDate;
    }

    /**
     * @param DateTime $borrowedDate
     */
    public function setBorrowedDate($borrowedDate)
    {
        $this->_borrowedDate = $borrowedDate;
    }

    /**
     * @return DateTime The date the item needs to be returned
     */
    public function getReturnDate()
    {
        return $this->_returnDate;
    }

    /**
     * @param DateTime $returnDate
     */
    public function setReturnDate($returnDate)
    {
        $this->_returnDate = $returnDate;
    }

    /**
     * @return Integer The id of the User borrowing the item
     */
    public function getBorrower()
    {
        return $this->_borrower;
    }

    /**
     * @param Integer $borrower The id of the borrower
     */
    public function setBorrower($borrower)
    {
        $this->_borrower = $borrower;
    }

    /**
     * @return Integer[] An array of user IDs
     */
    public function getHolds()
    {
        return $this->_holds;
    }

    /**
     * @param Integer[] $holds
     */
    public function setHolds($holds)
    {
        $this->_holds = $holds;
    }


    /**
     * @param Integer $id ID of the user to add to the hold array
     */
    public function placeHold($id)
    {
        $this->_holds[] = $id;
    }

    public function checkIn()
    {
        $this->_available = true;
        $this->_borrowedDate = null;
        $this->_returnDate = null;
        $this->_borrower = null;
    }

    public function checkOut($id)
    {
        $this->_available = false;
        $this->_borrower = $id;

        // Get today's date and 2 weeks from today's date
        $today = date("m/d/y");
        $today = strtotime($today);
        $return = strtotime("+14 day", $today);
        //echo date('m/d/Y', $return);

        $this->_borrowedDate = $today;
        $this->_returnDate = $return;
    }

    public function extendDueDate($days)
    {
        // Add days to the return date
        $oldDate = $this->_returnDate;
        $newDate = strtotime("+".$days." day", $oldDate);
        $this->_returnDate = $newDate;
        //echo date('m/d/Y', $newDate);
    }


}