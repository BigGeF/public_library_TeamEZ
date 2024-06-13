<?php

/**
 * Class DonationController
 * Handles donation-related actions and routes.
 */
class DonationController
{
    private $_f3;
    private $_dataLayer;

    /**
     * DonationController constructor.
     *
     * @param object $f3 Fat-Free Framework instance.
     * @param DataLayer $dataLayer Data layer instance.
     */
    public function __construct($f3, $dataLayer)
    {
        $this->_f3 = $f3;
        $this->_dataLayer = $dataLayer;
    }

    /**
     * Handles donation process.
     * If the request method is POST, it initiates a Stripe checkout session and redirects to it.
     * If the request method is GET, it renders the donation form.
     */
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

    /**
     * Handles the success of a donation.
     * If a session ID is provided in the GET parameters, it processes the successful donation.
     * Renders the success view.
     */
    function handleSuccess()
    {
        if (isset($_GET['session_id'])) {
            $sessionId = $_GET['session_id'];
            $this->_dataLayer->handleSuccess($sessionId, $this->_f3);
        }

        $view = new Template();
        echo $view->render('views/success.html');
    }

    /**
     * Displays the donation leaderboard.
     * Fetches the leaderboard data from the data layer and sets it to the F3 instance.
     * Renders the leaderboard view.
     */
    function leaderboard()
    {
        $leaderboard = $this->_dataLayer->getLeaderboard();
        $this->_f3->set('leaderboard', $leaderboard);

        $view = new Template();
        echo $view->render('views/leaderboard.html');
    }
}
