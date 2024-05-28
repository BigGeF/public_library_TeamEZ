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
$emailCon = new EmailController($f3);
$donateCon = new DonationController($f3);

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
$f3->route('GET|POST /contact', function() {
    // Get contact function from controller
    $GLOBALS['emailCon']->contact();
});

$f3->route('GET /login', function() {
    // Render a login page
    $GLOBALS['con']->logIn();
});

// Define an admin route
$f3->route('GET /admin', function() {
    $GLOBALS['con']->adminGetUsers();
});

// Define a route for the donation page
$f3->route('GET|POST /donate', function() {
    // Render a donation page
    $GLOBALS['donateCon']->donate();
});

// Define a route for creating the Checkout Session
//$f3->route(' /create-checkout-session', function() {
//    // Create Checkout Session
//    $GLOBALS['donateCon']->donate();
//});

// Define a route for success page
$f3->route('GET /success', function() {
    // Render success page
    $view = new Template();
    echo $view->render('views/success.html');
});

// Define a route for cancel page
$f3->route('GET /cancel', function() {
    // Render cancel page
    $view = new Template();
    echo $view->render('views/cancel.html');
});

// Run fat free
$f3->run();

