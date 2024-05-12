<?php

// SDEV328/application/index.php
// This is my controller

// Turn on error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Require the autoload file
require_once('vendor/autoload.php');
require_once ('model/data-layer.php');

// Create an instance of the Base class
$f3 = Base::instance();


// Define a default route
$f3->route('GET /', function() {
    // Render a view page
    $view = new Template();
    echo $view->render('views/home.html');
});

// Define a signUp route
$f3->route('GET /signUp', function() {
    // Render a view page
    $view = new Template();
    echo $view->render('views/signUp.html');
});

// Define a search route
$f3->route('GET|POST /search', function($f3) {

    // If the form has been posted
    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        // Get the type of book
        $printType = $_POST['type'];

        // Get User Input Search Term
        $searchTerm = trim($_POST['searchTerm']);
        if (isset($_POST['searchTerm']) && !empty(trim($_POST['searchTerm']))){

            // Get the search results using curl
            $items = getSearchResultsCurl($searchTerm, $printType)->items;

            // Set searchResults data
            $f3->set('searchResults', array($items));
        }
    }

    // Render a view page
    $view = new Template();
    echo $view->render('views/search.html');
});

// Define a borrows route
$f3->route('GET /borrows', function($f3) {

    // Get Borrows Dummy data
    $data = json_encode(getMyBorrowsData());
    $borrows = json_decode($data)->items;

    // Set myBorrows data
    $f3->set('myBorrows', array($borrows));

    // Render a view page
    $view = new Template();
    echo $view->render('views/borrows.html');
});


// Define a contact route
$f3->route('GET /contact', function() {
    // Render a view page
    $view = new Template();
    echo $view->render('views/contact.html');
});


//test
// Run fat free
$f3->run();