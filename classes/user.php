<?php

class User
{
    private $_id;
    private $_role;
    private $_fName;
    private $_lName;
    private $_email;
    private $_borrows;

    public function __construct($id, $role, $fName, $lName, $email, $borrows)
    {
        $this->_id = $id;
        $this->_role = $role;
        $this->_fName = $fName;
        $this->_lName = $lName;
        $this->_email = $email;
        $this->_borrows = $borrows;
    }

    /**
     * @return Integer The id of the user
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
     * @return Integer The role of the user. 0 = USER; 1 = ADMIN;
     */
    public function getRole()
    {
        return $this->_role;
    }

    /**
     * @param Integer $role
     */
    public function setRole($role)
    {
        $this->_role = $role;
    }

    /**
     * @return String The first name of the user
     */
    public function getFName()
    {
        return $this->_fName;
    }

    /**
     * @param String $fName
     */
    public function setFName($fName)
    {
        $this->_fName = $fName;
    }

    /**
     * @return String The last name of the user
     */
    public function getLName()
    {
        return $this->_lName;
    }

    /**
     * @param String $lName
     */
    public function setLName($lName)
    {
        $this->_lName = $lName;
    }

    /**
     * @return String The email address of the user
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * @param String $email
     */
    public function setEmail($email)
    {
        $this->_email = $email;
    }

    /**
     * @return Item[] An array of Item objects
     */
    public function getBorrows()
    {
        return $this->_borrows;
    }

    /**
     * @param Item[] $borrows An array of Item objects
     */
    public function setBorrows($borrows)
    {
        $this->_borrows = $borrows;
    }



}