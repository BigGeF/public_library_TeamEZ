<?php
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
    }

    function home()
    {
        // Render the home page
        $view = new Template();
        echo $view->render('views/home.html');
    }


    function signUp()
    {
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
            if (isset($_POST['searchTerm']) && !empty(trim($_POST['searchTerm']))){

                // Get the search results using curl
                $items = DataLayer::getSearchResultsCurl($searchTerm, $printType)->items;

                // Create an array to hold Item objects
                $itemObjects = array();

                // Go through each item from the search results
                foreach ($items as $item){

                    // Set general Item params
                    $itemParams = array();
                    $itemParams['id'] = 1;
                    $itemParams['title'] = $item->volumeInfo->title;
                    $itemParams['desc'] = $item->volumeInfo->description;
                    $itemParams['pubDate'] = $item->volumeInfo->publishedDate;
                    $secondaryParams = array();

                    // Create either a Book object or a Magazine object and add to the itemObject array
                    if ($item->volumeInfo->printType == "BOOK"){
                        // Set Book params
                        $secondaryParams["authors"] = $item->volumeInfo->authors;
                        $secondaryParams["pages"] = $item->volumeInfo->pageCount;
                        $secondaryParams["isbn"] = $item->volumeInfo->industryIdentifiers[0]->identifier;
                        $secondaryParams["cover"] = $item->volumeInfo->imageLinks->thumbnail;

                        // Create a Book from all params
                        $book = new Book($itemParams, $secondaryParams);
                        $itemObjects[] = $book;

                    }else if($item->volumeInfo->printType == "MAGAZINE"){
                        // Set Magazine params
                        $secondaryParams["pages"] = $item->volumeInfo->pageCount;
                        $secondaryParams["cover"] = $item->volumeInfo->imageLinks->thumbnail;

                        // Create a Magazine from all params
                        $magazine = new Magazine($itemParams, $secondaryParams);
                        $itemObjects[] = $magazine;
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
        // Get Borrows Dummy data
        $data = json_encode(DataLayer::getMyBorrowsData());
        $borrows = json_decode($data)->items;

        // Set myBorrows data
        $this->_f3->set('myBorrows', array($borrows));

        // Render a borrows page
        $view = new Template();
        echo $view->render('views/borrows.html');
    }

    function contact()
    {
        // Render a contact page
        $view = new Template();
        echo $view->render('views/contact.html');
    }

    function logIn()
    {
        // Render a login page
        $view = new Template();
        echo $view->render('views/login.html');
    }
    //get all users and show at admin.html page
    function adminGetUsers(){
        //get all users data
        $sql = "SELECT * FROM users";
        $statement = $this->dbh->prepare($sql);
        $statement->execute();
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);

        //save the users into f3's "hive"
        $this->_f3->set('users', $users);

        //render the admin page
        $view = new Template();
        echo $view->render('views/admin.html');
    }
}