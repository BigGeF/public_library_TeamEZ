<?php

class DataLayer
{
    private $_dbh;

    /**
     * Data Layer constructor
     */
    public function __construct()
    {
        // Require my PDO database connection credentials
        require_once($_SERVER['DOCUMENT_ROOT'].'/../config.php');

        try {
            // Instantiate our PDO Database Object
            $this->_dbh = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            //echo 'Connected to database!';
        }
        catch(PDOException $e){
            //die($e->getMessage());
            die("<p>Something went wrong!</p>");
        }
    }


    /**
     *
     * Get dummy data for books borrowed for default user
     *
     * @return  string  test dummy data
     *
     */
    static function getMyBorrowsData(){
        $path = 'model/testDataBorrows.json';
        $jsonString = file_get_contents($path);
        return json_decode($jsonString);
    }

    /**
     *
     * Get data from Google Books API using supplied search term
     *
     * @param   string  $searchTerm The string search term to search for
     * @param   string  $printType type of book to search (all, books, magazines)
     * @return  string  search result data
     *
     */
    public function getSearchResultsCurl($searchTerm, $printType){
        // Create the search url string
        $url = "https://www.googleapis.com/books/v1/volumes?q=" . $searchTerm . "&printType=" . $printType;

        // create & initialize a curl session
        $curl = curl_init();

        // set our url with curl_setopt()
        curl_setopt($curl, CURLOPT_URL, $url);

        // return the transfer as a string, also with setopt()
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // curl_exec() executes the started curl session
        // $output contains the output string
        $output = curl_exec($curl);

        // close curl resource to free up system resources
        // (deletes the variable made by curl_init)
        curl_close($curl);

        // decode the json
        return json_decode($output);
    }

    public function getItemsAsObjects($items)
    {
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
        return $itemObjects;
    }

    public function getUserBorrowedItems($userId)
    {
        //get all users data
        $sql = "SELECT * FROM books WHERE user_id = :user_id";

        $statement = $this->_dbh->prepare($sql);

        $statement->bindParam(':user_id', $userId);
        $statement->execute();
        $items = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $items;
    }

    public function checkoutItem()
    {
        // 1. Define the query
        $sql = "INSERT INTO books (title, description, available, publishedDate, borrowedDate, returnDate,
                   user_id, author, pages, isbn, cover) VALUES (:title, :description, :available, :publishedDate,
                                                                :borrowedDate, :returnDate, :user_id, :authors,
                                                                :pages, :isbn, :cover)";

        // 2. Prepare the statement
        $statement = $this->_dbh->prepare($sql);

        // 3. Bind the parameters
        $title = $_POST['modal-item-title'] == "null" ? null : $_POST['modal-item-title'];
        $description = $_POST['modal-item-description'] == "null" ? null : substr($_POST['modal-item-description'], 0, 497).'...';
        $available = 0;
        $publishedDate = $_POST['modal-item-publishedDate'] == "null" ? null : $_POST['modal-item-publishedDate'];
        // Make sure date is in the correct format
        if (strlen($publishedDate) == 4){
            // Add Jan 1st if only year is available
            $publishedDate .= '-01-01';
        }else if (strlen($publishedDate) == 7){
            // Add the 1st if only year and month are available
            $publishedDate .= '-01';
        }
        $borrowedDate = date('Y-m-d', time());
        $returnDate = date('Y-m-d', strtotime($borrowedDate . '+14days'));
        $userId = $GLOBALS['f3']->get("SESSION.userId");
        $authors = $_POST['modal-item-authors'] == "null" ? null : $_POST['modal-item-authors'];
        $pages = $_POST['modal-item-pages'] == "null" ? null : $_POST['modal-item-pages'];
        $isbn = $_POST['modal-item-isbn'] == "null" ? null : $_POST['modal-item-isbn'];
        $cover = $_POST['modal-item-cover'] == "null" ? null : $_POST['modal-item-cover'];

        //echo $title . ", " . $description  . ", " . $available  . ", " . $publishedDate  . ", " . $borrowedDate  . ", " . $returnDate  . ", " . $userId  . ", " . $authors  . ", " . $pages  . ", " . $isbn  . ", " . $cover;

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
                return true;
            }else{
                return false;
            }
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function returnItem($bookId, $userId)
    {
        $newId = null;
        $available = true;

        // 1. Define the query
        $sql = "UPDATE books SET available=:available, user_id=:new_id WHERE id=:id && user_id=:old_id";

        // 2. Prepare the statement
        $statement = $this->_dbh->prepare($sql);

        $statement->bindParam(':available', $available, PDO::PARAM_INT);
        $statement->bindParam(':id', $bookId);
        $statement->bindParam(':new_id', $newId);
        $statement->bindParam(':old_id', $userId);

        echo $bookId . " - " . $userId . " - " . $newId . " - " . $available;

        echo $statement->queryString;

        // 4. Execute the query
        try {
            $successful = $statement->execute();
            return $successful;
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function sendOverdueEmail($overdueUserID)
    {
        // get user info from id
        //get all books data
        $sql = "SELECT * FROM users WHERE id=:id";
        $statement = $this->_dbh->prepare($sql);

        $statement->bindParam(':id', $overdueUserID);
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

            // Send email to user
            if (mail($to, $subject, $body, $headers)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
    static function isOverdue($returnDate){
        $date = strtotime($returnDate);
        return ceil(($date-time())/60/60/24) < 0;
    }

    static function getDaysFromReturnDate($returnDate){
        $date = strtotime($returnDate);
        return abs(ceil(($date-time())/60/60/24)); //absolute value used so no negatives returned
    }


}

