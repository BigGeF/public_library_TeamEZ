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

$f3->route('GET /signUp', function() {
    // Render a view page
    $view = new Template();
    echo $view->render('views/signUp.html');
});

// Define a search route
$f3->route('GET|POST /search', function($f3) {


//    // Get Search Dummy Data
//    $data = json_encode(getSearchTestResults());
//    $items = json_decode($data)->items;
//
//    // Set searchResults data
//    $f3->set('searchResults', array($items));


    // If the form has been posted
    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        $searchTerm = $_POST['searchTerm'];
        if (isset($_POST['searchTerm']) && !empty(trim($_POST['searchTerm']))){
            // create & initialize a curl session
            $curl = curl_init();

            // set our url with curl_setopt()
            curl_setopt($curl, CURLOPT_URL, "https://www.googleapis.com/books/v1/volumes?q=" . $searchTerm);

            // return the transfer as a string, also with setopt()
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            // curl_exec() executes the started curl session
            // $output contains the output string
            $output = curl_exec($curl);
            $items = json_decode($output)->items;

            // Set searchResults data
            $f3->set('searchResults', array($items));

            //var_dump($output);

            // close curl resource to free up system resources
            // (deletes the variable made by curl_init)
            curl_close($curl);
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