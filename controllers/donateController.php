<?php
require_once 'vendor/autoload.php';
require_once "model/data-layer.php";

class DonationController
{
    private $_f3;
    private $_dataLayer;

    public function __construct($f3)
    {
        $this->_f3 = $f3;
        $this->_dataLayer = new DataLayer();
    }

    function donate()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $amount = $_POST['amount'];

            // Get server and root url
            $success = "https://" . $_SERVER['HTTP_HOST'] . $this->_f3->BASE . "/success?session_id={CHECKOUT_SESSION_ID}";
            $cancel = "https://" . $_SERVER['HTTP_HOST'] . $this->_f3->BASE . "/cancel";

            $sessionUrl = $this->_dataLayer->createCheckoutSession($amount, $success, $cancel);

            header("HTTP/1.1 303 See Other");
            header("Location: " . $sessionUrl);
            exit();
        } else {
            $view = new Template();
            echo $view->render('views/donate.html');
        }
    }

    function handleSuccess()
    {
        if (isset($_GET['session_id'])) {
            $sessionId = $_GET['session_id'];
            $this->_dataLayer->handleSuccess($sessionId, $this->_f3);
        }

        $view = new Template();
        echo $view->render('views/success.html');
    }

    function leaderboard()
    {
        $leaderboard = $this->_dataLayer->getLeaderboard();
        $this->_f3->set('leaderboard', $leaderboard);

        $view = new Template();
        echo $view->render('views/leaderboard.html');
    }
}
