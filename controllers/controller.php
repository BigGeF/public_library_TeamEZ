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
        $this->_dbh = Database::getConnection();
    }

    function home()
    {
        // Render the home page
        $view = new Template();
        echo $view->render('views/home.html');
    }


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

                // get user information from database
                $sql = 'INSERT INTO users (first, last, email, password)
                VALUES (:first, :last, :email, :password)';

                $statement = $this->_dbh->prepare($sql);
                $statement->bindParam(':first', $firstName);
                $statement->bindParam(':last', $lastName);
                $statement->bindParam(':email', $email);
                $statement->bindParam(':password', $hash);
                $statement->execute();

                //get the last inserted ID
                $id = $this->_dbh->lastInsertId();

                $this->_f3->set("SESSION.userId", $id);

                // send user to login form
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
            // TODO: debug error messages. Login currently works though
            if (empty($this->_f3->get('errors'))) {
                // get user information from database
                $sql = 'SELECT * FROM users WHERE `email`= :email';
                $statement = $this->_dbh->prepare($sql);
                $statement->bindParam(':email', $email);
                $statement->execute();

                echo '<script>console.log("statement executed");</script>';
                // fetch the result
                if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    // assign variables
                    $hash = $row['password'];
                    $id = $row['id'];
                    $role = $row['role'];
                    $first = $row['first'];
                    // verify credentials
                    if (password_verify($password, $hash)) {
                        $this->_f3->set('SESSION["first"]',$first);
                        // set user id
                        $this->_f3->set('SESSION["userId"]',$id);
                        // set user role
                        $this->_f3->set('SESSION["role"]',$role);
                        if ($role == 0){
                            // send user to borrows page
                            $this->_f3->reroute('borrows');
                        }else{
                            // send admins to admin page
                            $this->_f3->reroute('admin');
                        }

                    }
                    else { // user not found
                        // set error message
                        $this->_f3->set('errors["login_failure"]', 'Email or password is incorrect');
                        // reroute to login page
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

    //get all users and show at admin.html page
    function adminGetUsers()
    {
        //get all users data
        $sql = "SELECT * FROM users";
        $statement = $this->_dbh->prepare($sql);
        $statement->execute();
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);

        //save the users into f3's "hive"
        $this->_f3->set('users', $users);

        $avail = 0;

        //get all books data
        $sql = "SELECT * FROM books WHERE available=:available order by returnDate";

        $statement = $this->_dbh->prepare($sql);
        $statement->bindParam(':available', $avail, PDO::PARAM_INT);
        $statement->execute();
        $books = $statement->fetchAll(PDO::FETCH_ASSOC);

        $itemObjects = array();

        foreach ($books as $book){
            // Set General Item Parameters
            $itemParams = array();
            $itemParams['id'] = $book['id'];
            $itemParams['title'] = $book['title'];
            $itemParams['desc'] = $book['description'];
            $itemParams['pubDate'] = $book['publishedDate'];
            $itemParams['available'] = $book['available'];
            $itemParams['borrowDate'] = $book['borrowedDate'];
            $itemParams['returnDate'] = $book['returnDate'];
            $itemParams['borrower'] = $book['user_id'];

            // Set Book Parameters
            $secondaryParams = array();
            $secondaryParams['authors'] = $book['authors'];
            $secondaryParams['pages'] = $book['pages'];
            $secondaryParams['isbn'] = $book['isbn'];
            $secondaryParams['cover'] = $book['cover'];

            // Instantiate a Book and add to the item array
            $bookObj = new Book($itemParams, $secondaryParams);
            $itemObjects[] = $bookObj;
        }

        //get all magazines data
        $sql = "SELECT * FROM magazines WHERE available=:available ORDER BY returnDate";
        $avail = 0;
        $statement = $this->_dbh->prepare($sql);
        $statement->bindParam(':available', $avail, PDO::PARAM_INT);
        $statement->execute();
        $mags = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($mags as $mag){
            // Set General Item Parameters
            $itemParams = array();
            $itemParams['id'] = $mag['id'];
            $itemParams['title'] = $mag['title'];
            $itemParams['desc'] = $mag['description'];
            $itemParams['pubDate'] = $mag['publishedDate'];
            $itemParams['available'] = $mag['available'];
            $itemParams['borrowDate'] = $mag['borrowedDate'];
            $itemParams['returnDate'] = $mag['returnDate'];
            $itemParams['borrower'] = $mag['user_id'];

            // Set Book Parameters
            $secondaryParams = array();
            $secondaryParams['pages'] = $mag['pages'];
            $secondaryParams['cover'] = $mag['cover'];

            // Instantiate a Book and add to the item array
            $magObj = new Magazine($itemParams, $secondaryParams);
            $itemObjects[] = $magObj;
        }

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

        if ($overdueUserID){
            $emailSent = $GLOBALS['dataLayer']->sendOverdueEmail($overdueUserID);

            //TODO: display message for user
            if ($emailSent){
                // Email sent
            }else{
                // Email failed
            }
        }
    }

}


