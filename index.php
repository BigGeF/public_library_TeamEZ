<?php

// SDEV328/application/index.php
// This is my controller

// Turn on error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Require the autoload file
require_once('vendor/autoload.php');

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
$f3->route('GET /search', function($f3) {

    $f3->set('SESSION.searchTitle', 'Search Books');

    // Render a view page
    $view = new Template();
    echo $view->render('views/search.html');
});

// Define a borrows route
$f3->route('GET /borrows', function() {
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