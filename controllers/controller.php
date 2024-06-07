<?php
session_start();

//get the database.php

require_once "database.php";
class Controller
{
    private $_f3;   // Fat-Free Router
    private $_dbh;


    function __construct($f3)
    {
        $this->_f3 = $f3;
        // TODO: Remove the dbh from controller once remaining functions using it are moved to dataLayer
        $this->_dbh = Database::getConnection();

    }

    function home()
    {
        // Render the home page
        $view = new Template();
        echo $view->render('views/home.html');
    }

    function search()
    {
        // If the form has been posted
        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            // Get the type of book
            $printType = $_POST['type'];

            // Get User Input Search Term
            $searchTerm = trim($_POST['searchTerm']);
            $searchTerm = str_replace(' ', '%20', $searchTerm); // replace spaces with %20 for api search
            if (isset($_POST['searchTerm']) && !empty(trim($_POST['searchTerm']))) {

                // Get the search results using curl
                $items =  $GLOBALS['dataLayer']->getSearchResultsCurl($searchTerm, $printType)->items;

                // Create an array of item objects
                $itemObjects = $GLOBALS['dataLayer']->getItemsAsObjects($items);

                // Set searchResults data
                $this->_f3->set('searchResults', $itemObjects);
            }
        }

        // Render a search page
        $view = new Template();
        echo $view->render('views/search.html');
    }

    function borrows()
    {
        // Set myBorrows data
        $id = $this->_f3->get("SESSION.userId");

        if ($id){
            // Get users borrowed items from database
            $items = $GLOBALS['dataLayer']->getUserBorrowedItems($id);

            //save the users into f3's "hive"
            $this->_f3->set('borrowedItems', $items);
        }

        // Render a borrows page
        $view = new Template();
        echo $view->render('views/borrows.html');
    }

    /**
     * Controller function that handles user's sign up requests
     *
     * Validates sign up form data and then creates a row
     * in our database for the user. Successful sign up stores
     * user ID and then routes them to the search page.
     *
     * @return void
     */
    function signUp()
    {
        // declare variables
        $firstName = '';
        $lastName = '';
        $email = '';
        $password = '';

        // signup form posted
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            // validate form data
            if (Validate::validName($_POST['firstName'])) {
                $firstName = $_POST['firstName'];
            } else {
                $this->_f3->set('errors["firstName"]', 'Invalid first name');
            }

            if (Validate::validName($_POST['lastName'])) {
                $lastName = $_POST['lastName'];
            } else {
                $this->_f3->set('errors["lastName"]', 'Invalid last name');
            }

            if (Validate::validPassword($_POST['password'])) {
                $password = $_POST['password'];
            } else {
                $this->_f3->set('errors["password"]', 'Invalid Password');
            }

            if (Validate::validEmail($_POST['email'])) {
                $email = $_POST['email'];
            } else {
                $this->_f3->set('errors["email"]', 'Please enter a valid email');
            }

            // if no errors, add user to database
            if (empty($this->_f3->get('errors'))) {
                // hash password
                $options = [
                    'cost' => 12,
                ];

                $hash = password_hash($password, PASSWORD_BCRYPT, $options);

                // create user and get the last inserted ID
                $id =  $GLOBALS['dataLayer']->createUser($firstName, $lastName, $email, $hash);

                // log the user in
                $this->_f3->set("SESSION.userId", $id);

                // send user to search form
                $this->_f3->reroute('search');
            }
        }

        // Render the signUp page
        $view = new Template();
        echo $view->render('views/signUp.html');
    }

    /**
     * Controller function that handles user's login requests
     *
     * Validates login form data then compares it to an entry
     * in our database. Successful login stores: first name,
     * user ID, and role (administrative access level).
     *
     * @return void
     */
    function logIn()
    {
        // declare variables
        $email = '';
        $password = '';

        // login form posted
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            // validate form data
            if (Validate::validPassword($_POST['password'])) {
                $password = $_POST['password'];
            } else {
                $this->_f3->set('errors["password"]', 'Invalid Password');
            }

            if (Validate::validEmail($_POST['email'])) {
                $email = $_POST['email'];
            } else {
                $this->_f3->set('errors["email"]', 'Please enter a valid email');
            }

            // begin login process if no errors
            if (empty($this->_f3->get('errors'))) {
                // fetch user credentials
                if ($row = $GLOBALS['dataLayer']->getCredentials($email)) {
                    // assign variables
                    $hash = $row['password'];
                    $id = $row['id'];
                    $role = $row['role'];
                    $first = $row['first'];

                    // verify credentials
                    if (password_verify($password, $hash)) {
                        // set user first name
                        $this->_f3->set('SESSION["first"]',$first);
                        // set user id
                        $this->_f3->set('SESSION["userId"]',$id);
                        // set user role
                        $this->_f3->set('SESSION["role"]',$role);

                        if ($role == 0) {
                            // send users to borrows page
                            $this->_f3->reroute('borrows');
                        } else {
                            // send admins to admin page
                            $this->_f3->reroute('admin');
                        }
                    }
                }
                // user not found, set error message
                $this->_f3->set('errors["email"]', 'Email or password is incorrect');
            }
        }
        // render the login page
        $view = new Template();
        echo $view->render('views/login.html');
    }

    /**
     * Controller function that handles user's logouts
     *
     * @return void
     */
    function logOut()
    {
        session_destroy();
        $this->_f3->reroute('/');
    }

    //get all users and show at admin.html page
    function adminGetUsers()
    {
        $users = $GLOBALS['dataLayer']->getUsers();

        //save the users into f3's "hive"
        $this->_f3->set('users', $users);

        // Get items from database
        $itemObjects = $GLOBALS['dataLayer']->getAllBorrowedItems();

        //save the users into f3's "hive"
        $this->_f3->set('items', $itemObjects);

        //render the admin page
        $view = new Template();
        echo $view->render('views/admin.html');
    }

    function addItemToDatabase()
    {
        // Try to add item to the database
        $addItem = $GLOBALS['dataLayer']->checkoutItem();

        if ($addItem){
            // Item successfully added to database
            $this->_f3->reroute("borrows");
        }else{
            // Something went wrong
        }
    }

    function returnItem(){
        $bookId = $_POST['modal-item-id'];
        $userId = $_POST['modal-item-user'];

        if ($bookId && $userId){
            $return = $GLOBALS['dataLayer']->returnItem($bookId, $userId);

            if ($return){
                $this->_f3->reroute("borrows");
            }else{
                //TODO: Make a failure page or just reroute
                echo "Something went wrong";
            }
        }
    }

    function sendOverdueEmail(){
        $overdueUserID = $_POST['overdueId'];

        $GLOBALS['dataLayer']->sendOverdueEmail($overdueUserID);
    }

}


