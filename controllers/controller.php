<?php

class Controller
{
    private $_f3;   // Fat-Free Router

    function __construct($f3)
    {
        $this->_f3 = $f3;
    }

    function home()
    {
        // Render the home page
        $view = new Template();
        echo $view->render('views/home.html');
    }

    function signUp()
    {
        // Render the signUp page
        $view = new Template();
        echo $view->render('views/signUp.html');
    }

    function search()
    {
        // If the form has been posted
        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            // Get the type of book
            $printType = $_POST['type'];

            // Get User Input Search Term
            $searchTerm = trim($_POST['searchTerm']);
            if (isset($_POST['searchTerm']) && !empty(trim($_POST['searchTerm']))){

                // Get the search results using curl
                $items = DataLayer::getSearchResultsCurl($searchTerm, $printType)->items;

                // Set searchResults data
                $this->_f3->set('searchResults', array($items));
            }
        }

        // Render a search page
        $view = new Template();
        echo $view->render('views/search.html');
    }

    function borrows()
    {
        // Get Borrows Dummy data
        $data = json_encode(DataLayer::getMyBorrowsData());
        $borrows = json_decode($data)->items;

        // Set myBorrows data
        $this->_f3->set('myBorrows', array($borrows));

        // Render a borrows page
        $view = new Template();
        echo $view->render('views/borrows.html');
    }

    function contact()
    {
        // Render a contact page
        $view = new Template();
        echo $view->render('views/contact.html');
    }

    function logIn()
    {
        // Render a login page
        $view = new Template();
        echo $view->render('views/login.html');
    }
}