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
        // Get Borrows Dummy data
        $data = json_encode(DataLayer::getMyBorrowsData());
        $borrows = json_decode($data)->items;

        // Set myBorrows data
        //$this->_f3->set('myBorrows', array($borrows));

        //get all users data
        $sql = "SELECT * FROM books";
        $statement = $this->dbh->prepare($sql);
        $statement->execute();
        $items = $statement->fetchAll(PDO::FETCH_ASSOC);

        //save the users into f3's "hive"
        $this->_f3->set('borrowedItems', $items);

        // Render a borrows page
        $view = new Template();
        echo $view->render('views/borrows.html');
    }


    function logIn()
    {
        // Render a login page
        $view = new Template();
        echo $view->render('views/login.html');
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

        //render the admin page
        $view = new Template();
        echo $view->render('views/admin.html');
    }

    function addItemToDatabase()
    {

        $item = json_decode($_POST['item']);
        //echo $item;

        // 1. Define the query
        $sql = "INSERT INTO books (title, description, available, publishedDate, borrowedDate, returnDate,
                   user_id, author, pages, isbn, cover) VALUES (:title, :description, :available, :publishedDate,
                                                                :borrowedDate, :returnDate, :user_id, :authors,
                                                                :pages, :isbn, :cover)";

        // 2. Prepare the statement
        $statement = $this->dbh->prepare($sql);

        // 3. Bind the parameters
        $title = $item->title;
        $description = $item->description;
        $available = true;
        $publishedDate = $item->publishedDate == "null" ? null : $item->publishedDate;
        $borrowedDate = $item->borrowedDate == "null" ? null : $item->borrowedDate;
        $returnDate = $item->returnDate == "null" ? null : $item->returnDate;
        $userId = 101;
        $authors = implode(", ", $item->authors);
        $pages = $item->pages;
        $isbn = $item->isbn;
        $cover = $item->cover;

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
}


