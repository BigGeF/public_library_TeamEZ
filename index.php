<?php

// SDEV328/application/index.php
// This is my controller

// Turn on error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Require the autoload file
require_once('vendor/autoload.php');
//require_once ('model/data-layer.php');

// Create an instance of the Base class
$f3 = Base::instance();
$con = new Controller($f3);

/*// Test Item
$params = array("id"=>1, "title"=>"Dune", "desc"=>"Dune description", "pubDate"=>"1/2/88", "available"=>false,
    "borrowDate"=>"5/4/24", "returnDate"=>"5/21/24", "borrower"=>2, "holds"=>array(2, 4, 5));

$book = new Book($params);
$book->checkOut(3);
$book->extendDueDate(10);
//var_dump($book);*/

// Define a default route
$f3->route('GET /', function() {
    // Render Home Page
    $GLOBALS['con']->home();
});

// Define a signUp route
$f3->route('GET /signUp', function() {
    // Render SignUp Page
    $GLOBALS['con']->signUp();
});

// Define a search route
$f3->route('GET|POST /search', function() {
    // Render Search Page
    $GLOBALS['con']->search();
});

// Define a borrows route
$f3->route('GET /borrows', function() {
    // Render a borrows page
    $GLOBALS['con']->borrows();
});


// Define a contact route
$f3->route('GET /contact', function() {
    // Render a contact page
    $GLOBALS['con']->contact();
});

$f3->route('GET /login', function() {
    // Render a login page
    $GLOBALS['con']->logIn();
});

//test
// Run fat free
$f3->run();