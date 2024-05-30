<?php
session_start();

//get the database.php

require_once "database.php";
class Controller
{
    private $_f3;   // Fat-Free Router
    private $dbh;


    function __construct($f3)
    {
        $this->_f3 = $f3;
        $this->dbh = Database::getConnection();
        $this->_f3->set('_itemClicked', function ($item) {
            return $this->itemClicked($item);
        });
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

                $statement = $this->dbh->prepare($sql);
                $statement->bindParam(':first', $firstName);
                $statement->bindParam(':last', $lastName);
                $statement->bindParam(':email', $email);
                $statement->bindParam(':password', $hash);
                $statement->execute();

                //get the last inserted ID
                $id = $this->dbh->lastInsertId();
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
                $items = DataLayer::getSearchResultsCurl($searchTerm, $printType)->items;

                // Create an array to hold Item objects
                $itemObjects = array();

                if (sizeof($items) > 0){
                    // Go through each item from the search results
                    foreach ($items as $item) {

                        // Set general Item params
                        $itemParams = array();
                        $itemParams['id'] = 1;
                        $itemParams['title'] = $item->volumeInfo->title;
                        $itemParams['desc'] = $item->volumeInfo->description;
                        $itemParams['pubDate'] = $item->volumeInfo->publishedDate;
                        $secondaryParams = array();

                        // Create either a Book object or a Magazine object and add to the itemObject array
                        if ($item->volumeInfo->printType == "BOOK") {
                            // Set Book params
                            $secondaryParams["authors"] = $item->volumeInfo->authors;
                            $secondaryParams["pages"] = $item->volumeInfo->pageCount;
                            $secondaryParams["isbn"] = $item->volumeInfo->industryIdentifiers[0]->identifier;
                            $secondaryParams["cover"] = $item->volumeInfo->imageLinks->thumbnail;

                            // Create a Book from all params
                            $book = new Book($itemParams, $secondaryParams);
                            $itemObjects[] = $book;

                        } else if ($item->volumeInfo->printType == "MAGAZINE") {
                            // Set Magazine params
                            $secondaryParams["pages"] = $item->volumeInfo->pageCount;
                            $secondaryParams["cover"] = $item->volumeInfo->imageLinks->thumbnail;

                            // Create a Magazine from all params
                            $magazine = new Magazine($itemParams, $secondaryParams);
                            $itemObjects[] = $magazine;
                        }
                    }
                }


                /*echo "<pre>";
                foreach ($itemObjects as $itemObj){
                    var_dump($itemObj);
                }
                echo "</pre>";*/

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
            //get all users data
            $sql = "SELECT * FROM books WHERE user_id = $id";
            $statement = $this->dbh->prepare($sql);
            $statement->execute();
            $items = $statement->fetchAll(PDO::FETCH_ASSOC);

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
                $sql = 'SELECT password, id, role FROM users WHERE `email`= :email';
                $statement = $this->dbh->prepare($sql);
                $statement->bindParam(':email', $email);
                $statement->execute();

                echo '<script>console.log("statement executed");</script>';
                // fetch the result
                if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    // assign variables
                    $hash = $row['password'];
                    $id = $row['id'];
                    $role = $row['role'];

                    // verify credentials
                    if (password_verify($password, $hash)) {
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
        $statement = $this->dbh->prepare($sql);
        $statement->execute();
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);

        //save the users into f3's "hive"
        $this->_f3->set('users', $users);

        //get all books data
        $sql = "SELECT * FROM books ORDER BY returnDate";
        $statement = $this->dbh->prepare($sql);
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

        //get all books data
        $sql = "SELECT * FROM magazines ORDER BY returnDate";
        $statement = $this->dbh->prepare($sql);
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

        $item = json_decode($_POST['item']);


        // 1. Define the query
        $sql = "INSERT INTO books (title, description, available, publishedDate, borrowedDate, returnDate,
                   user_id, author, pages, isbn, cover) VALUES (:title, :description, :available, :publishedDate,
                                                                :borrowedDate, :returnDate, :user_id, :authors,
                                                                :pages, :isbn, :cover)";

        // 2. Prepare the statement
        $statement = $this->dbh->prepare($sql);

        // 3. Bind the parameters
        $title = $item->title == "null" ? null : $item->title;
        $description = $item->description == "null" ? null : $item->description;
        $available = 0;
        $publishedDate = $item->publishedDate == "null" ? null : $item->publishedDate;
        $borrowedDate = date('Y-m-d', time());
        $returnDate = date('Y-m-d', strtotime($borrowedDate . '+14days'));
        $userId = $this->_f3->get("SESSION.userId");
        $authors = $item->authors ? implode(", ", $item->authors) : null;
        $pages = $item->pages == "null" ? null : $item->pages;
        $isbn = $item->isbn == "null" ? null : $item->isbn;
        $cover = $item->cover == "null" ? null : $item->cover;

        echo $title . ", " . $description  . ", " . $available  . ", " . $publishedDate  . ", " . $borrowedDate  . ", " . $returnDate  . ", " . $userId  . ", " . $authors  . ", " . $pages  . ", " . $isbn  . ", " . $cover;
            /*// 3. TEST Bind the parameters
            $title = "Dune";
            $description = "Dune description";
            $available = true;
            $publishedDate = "1980-12-05";
            $borrowedDate = "2024-5-28";
            $returnDate = "2024-6-12";
            $userId = 101;
            $authors = "Frank Herbert";
            $pages = "980";
            $isbn = "9780593438367";
            $cover = "http://books.google.com/books/content?id=UAhAEAAAQBAJ&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api";*/

        $statement->bindParam(':title', $title);
        $statement->bindParam(':description', $description);
        $statement->bindParam(':available', $available);
        $statement->bindParam(':publishedDate', $publishedDate);
        $statement->bindParam(':borrowedDate', $borrowedDate);
        $statement->bindParam(':returnDate', $returnDate);
        $statement->bindParam(':user_id', $userId);
        $statement->bindParam(':authors', $authors);
        $statement->bindParam(':pages', $pages);
        $statement->bindParam(':isbn', $isbn);
        $statement->bindParam(':cover', $cover);


// 4. Execute the query
        try {
            $successful = $statement->execute();
            if ($successful){
                echo "Success";
            }
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

    }

    function sendOverdueEmail(){
        $overdueUserID = $_POST['overdueId'];

        // get user info from id
        //get all books data
        $sql = "SELECT * FROM users WHERE id = $overdueUserID";
        $statement = $this->dbh->prepare($sql);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if ($user){
            $overdueItem = $_POST['overdueItem'];

            $firstName = $user["fName"];
            $lastName = $user["lName"];
            $email = $user["email"];
            $message = "This is just a friendly reminder that " . $overdueItem . " is overdue. Please return the 
                                                                               item at your earliest convenience.";

            $to = 'miss.matthew@student.greenriver.edu';
            $subject = 'Contact Form Submission';
            $body = "First Name: $firstName\nLast Name: $lastName\nEmail: $email\nMessage: $message";
            $headers = "From: $email";

            if (mail($to, $subject, $body, $headers)) {
                echo 'Message has been sent';
            } else {
                echo 'Failed to send message';
            }
        }

    }

}


