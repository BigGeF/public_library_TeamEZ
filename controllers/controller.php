<?php
session_start();

/**
 * Class Controller
 *
 * Handles the routing and logic for the application using the Fat-Free Framework.
 */
class Controller
{
    private $_f3;        // Fat-Free Router
    private $_dataLayer; // DataLayer Instance

    /**
     * Controller constructor.
     *
     * @param $f3 Fat-Free Framework instance.
     */
    function __construct($f3, $dataLayer)
    {
        $this->_f3 = $f3;
        $this->_dataLayer = $dataLayer;
    }

    /**
     * Renders the home page.
     */
    function home()
    {
        // Render the home page
        $view = new Template();
        echo $view->render('views/home.html');
    }

    /**
     * Handles user sign-up. Validates form fields and adds user to database.
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

                $this->_dataLayer->addUser($firstName, $lastName, $email, $hash);

                $id = $this->_dataLayer->getLastInsertId();

                $this->_f3->set("SESSION.userId", $id);

                // send user to login form
                $this->_f3->reroute('search');
            }
        }

        // Render the signUp page
        $view = new Template();
        echo $view->render('views/signUp.html');
    }

    /**
     * Handles item search functionality. Searches Google Book API with the users searchTerm
     */
    function search()
    {
        $searchTerm = '';

        // If the form has been posted
        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            // Get the type of book
            $printType = $_POST['type'];

            if (isset($_POST['searchTerm']) && !empty(trim($_POST['searchTerm']))) {
                // Get User Input Search Term
                $searchTerm = trim($_POST['searchTerm']);
                $searchTerm = str_replace(' ', '%20', $searchTerm); // replace spaces with %20 for api search

                // Get the search results using curl
                $items = $this->_dataLayer->getSearchResultsCurl($searchTerm, $printType)->items;

                // Create an array of item objects
                $itemObjects = $this->_dataLayer->getItemsAsObjects($items);

                // Set searchResults data
                $this->_f3->set('searchResults', $itemObjects);

                // Set searchTerm session variable in order to retain search term after user logs in
                $this->_f3->set('SESSION.lastSearchResults', $itemObjects);
            }
        }else {  // GET method
            if ($this->_f3->get('SESSION.lastSearchResults')) {
                $this->_f3->set('searchResults', $this->_f3->get('SESSION.lastSearchResults'));
            }
        }

        // Render a search page
        $view = new Template();
        echo $view->render('views/search.html');
    }

    /**
     * Displays the borrowed items for the user.
     */
    function borrows()
    {
        // Set myBorrows data
        $id = $this->_f3->get("SESSION.userId");

        if ($id){
            // Get users borrowed items from database
            $items = $this->_dataLayer->getUserBorrowedItems($id);

            //save the users into f3's "hive"
            $this->_f3->set('borrowedItems', $items);
        }

        // Render a borrows page
        $view = new Template();
        echo $view->render('views/borrows.html');
    }

    /**
     * Handles user login. Validates form fields and checks user credentials against database users.
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
                if ($row = $this->_dataLayer->getCredentials($email)) {
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
                            if ($this->_f3->get('SESSION.lastSearchResults')){
                                // Return user to the recently searched item, route them to Search
                                $this->_f3->reroute('search');
                            }else{
                                // If user wasn't searching for an item, route them to Borrows
                                $this->_f3->reroute('borrows');
                            }

                        }else{
                            // send admins to admin page
                            $this->_f3->reroute('admin');
                        }

                    }
                    else { // user not found
                        // set error message
                        $this->_f3->set('errors["login_failure"]', 'Email or password is incorrect');
                        // reroute to login page
                        //$this->_f3->reroute('login');
                    }
                }
            }
        }
        // Render a login page
        $view = new Template();
        echo $view->render('views/login.html');
    }

    /**
     * Logs out the user and destroys the session.
     */
    function logOut()
    {
        session_destroy();
        $this->_f3->reroute('/');
    }


    /**
     * Retrieves all users and items and displays them on the admin page.
     */
    function adminGetUsers()
    {
        $users = $this->_dataLayer->getUsers();

        //save the users into f3's "hive"
        $this->_f3->set('users', $users);

        // Get items from database
        $itemObjects = $this->_dataLayer->getAllBorrowedItems();

        //save the users into f3's "hive"
        $this->_f3->set('items', $itemObjects);

        //render the admin page
        $view = new Template();
        echo $view->render('views/admin.html');
    }

    /**
     * Adds an item to the database.
     */
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

    /**
     * Returns an item to the library.
     */
    function returnItem(){
        $bookId = $_POST['modal-item-id'];
        $userId = $_POST['modal-item-user'];

        if ($bookId && $userId){
            $return = $this->_dataLayer->returnItem($bookId, $userId);

            if ($return){
                $this->_f3->reroute("borrows");
            }else{
                //TODO: Make a failure page or just reroute
                echo "Something went wrong";
            }
        }
    }

    /**
     * Sends an overdue email to the user.
     */
    function sendOverdueEmail(){
        if ($this->_dataLayer->sendOverdueEmail()){
            $this->_f3->set('SESSION.lastOverdueEmailDate', date('Y/m/d'));
        }
        $this->_f3->reroute('admin');
    }

}
