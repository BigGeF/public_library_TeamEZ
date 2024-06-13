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
$dataLayer = new DataLayer();
$con = new Controller($f3, $dataLayer);
$emailCon = new EmailController($f3);
$donateCon = new DonationController($f3);


// Define a default route
$f3->route('GET /', function() {
    $GLOBALS['con']->home();
});

// Define a signUp route
$f3->route('GET|POST /signUp', function() {
    $GLOBALS['con']->signUp();
});

// Define a search route
$f3->route('GET|POST /search', function() {
    $GLOBALS['con']->search();
});

// Define a borrows route
$f3->route('GET /borrows', function() {
    $GLOBALS['con']->borrows();
});

// Define a contact route
$f3->route('GET|POST /contact', function() {
    $GLOBALS['emailCon']->contact();
});

$f3->route('GET|POST /login', function() {
    $GLOBALS['con']->logIn();
});

$f3->route('GET|POST /logout', function() {
    $GLOBALS['con']->logOut();
});

// Define an admin route
$f3->route('GET /admin', function() {
    $GLOBALS['con']->adminGetUsers();
});

// Stripe Define a route for the donation page
$f3->route('GET|POST /donate', function() {
    $GLOBALS['donateCon']->donate();
});

// Stripe Define a route for successfully paid by card
$f3->route('GET /success', function() {
    $GLOBALS['donateCon']->handleSuccess();
});

// Stripe Define a route for cancel card page
$f3->route('GET /cancel', function() {
    $view = new Template();
    echo $view->render('views/cancel.html');
});

// Define an add-to-database route
$f3->route('POST /add-to-database', function() {
    $GLOBALS['con']->addItemToDatabase();
});

// Define a route to send overdue email
$f3->route('POST /overdue-email', function() {
    $GLOBALS['con']->sendOverdueEmail();
});

// Define a leaderboard route
$f3->route('GET /leaderboard', function() {
    $GLOBALS['donateCon']->leaderboard();
});

// Define a leaderboard route
$f3->route('POST /return-item', function() {
    $GLOBALS['con']->returnItem();
});

// Run fat free
$f3->run();
