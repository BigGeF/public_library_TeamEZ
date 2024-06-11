<?php
session_start();

require_once "./model/data-layer.php";

class Controller
{
    private $_f3;        // Fat-Free Router
    private $_dataLayer; // DataLayer Instance

    function __construct($f3)
    {
        $this->_f3 = $f3;
        $this->_dataLayer = new DataLayer(); // Use the DataLayer class to manage database connections
    }

    function home()
    {
        // Render the home page
        $view = new Template();
        echo $view->render('views/home.html');
    }

    function signUp()
    {
        // Declare variables
        $firstName = '';
        $lastName = '';
        $email = '';
        $password = '';

        // Signup form posted
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            // Validate form data
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

            // If no errors, add user to database
            if (empty($this->_f3->get('errors'))) {
                // Hash password
                $options = [
                    'cost' => 12,
                ];

                $hash = password_hash($password, PASSWORD_BCRYPT, $options);

                // Call DataLayer method to insert user information
                $this->_dataLayer->addUser($firstName, $lastName, $email, $hash);

                // Get the last inserted ID
                $id = $this->_dataLayer->getLastInsertId();

                $this->_f3->set("SESSION.userId", $id);

                // Send user to login form
                $this->_f3->reroute('search');
            }
        }

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
            $searchTerm = str_replace(' ', '%20', $searchTerm); // Replace spaces with %20 for API search
            if (isset($_POST['searchTerm']) && !empty(trim($_POST['searchTerm']))) {

                // Get the search results using curl
                $items = $this->_dataLayer->getSearchResultsCurl($searchTerm, $printType)->items;

                // Create an array of item objects
                $itemObjects = $this->_dataLayer->getItemsAsObjects($items);

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
            $items = $this->_dataLayer->getUserBorrowedItems($id);

            // Save the users into F3's "hive"
            $this->_f3->set('borrowedItems', $items);
        }

        // Render a borrows page
        $view = new Template();
        echo $view->render('views/borrows.html');
    }

    function logIn()
    {
        // Declare variables
        $email = '';
        $password = '';

        // Login form posted
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            // Validate form data
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

            // Begin login process if no errors
            if (empty($this->_f3->get('errors'))) {
                // Fetch user credentials
                if ($row = $this->_dataLayer->getCredentials($email)) {
                    // Assign variables
                    $hash = $row['password'];
                    $id = $row['id'];
                    $role = $row['role'];
                    $first = $row['first'];
                    // Verify credentials
                    if (password_verify($password, $hash)) {
                        $this->_f3->set('SESSION["first"]',$first);
                        // Set user id
                        $this->_f3->set('SESSION["userId"]',$id);
                        // Set user role
                        $this->_f3->set('SESSION["role"]',$role);
                        if ($role == 0){
                            // Send user to borrows page
                            $this->_f3->reroute('borrows');
                        }else{
                            // Send admins to admin page
                            $this->_f3->reroute('admin');
                        }

                    }
                    else { // User not found
                        // Set error message
                        $this->_f3->set('errors["login_failure"]', 'Email or password is incorrect');
                        // Reroute to login page
                        $this->_f3->reroute('login');
                    }
                }
            }
        }
        // Render a login page
        $view = new Template();
        echo $view->render('views/login.html');
    }

    function logOut()
    {
        session_destroy();
        $this->_f3->reroute('/');
    }

    // Get all users and show at admin.html page
    function adminGetUsers()
    {
        $users = $this->_dataLayer->getUsers();

        // Save the users into F3's "hive"
        $this->_f3->set('users', $users);

        // Get items from database
        $itemObjects = $this->_dataLayer->getAllBorrowedItems();

        // Save the users into F3's "hive"
        $this->_f3->set('items', $itemObjects);

        // Render the admin page
        $view = new Template();
        echo $view->render('views/admin.html');
    }

    function addItemToDatabase()
    {
        // Try to add item to the database
        $addItem = $this->_dataLayer->checkoutItem();

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
            $return = $this->_dataLayer->returnItem($bookId, $userId);

            if ($return){
                $this->_f3->reroute("borrows");
            }else{
                // TODO: Make a failure page or just reroute
                echo "Something went wrong";
            }
        }
    }

    function sendOverdueEmail(){
        $overdueUserID = $_POST['overdueId'];

        $this->_dataLayer->sendOverdueEmail($overdueUserID);
    }

}
