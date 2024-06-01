<?php
require_once 'vendor/autoload.php';
require_once "database.php";

class DonationController
{
    private $_f3;
    private $dbh;

    public function __construct($f3)
    {
        $this->_f3 = $f3;
        $this->dbh = Database::getConnection();
    }

    function donate()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $amount = $_POST['amount'];

            // Get server and root url
            $success = "https://" . $_SERVER['HTTP_HOST'] . $this->_f3->BASE . "/success?session_id={CHECKOUT_SESSION_ID}";
            $cancel = "https://" . $_SERVER['HTTP_HOST'] . $this->_f3->BASE . "/cancel";

            \Stripe\Stripe::setApiKey('sk_test_51PKxM2B3EtH8G57ohLIr117P87NYvxjvGNdKuFYYegAaSNupUxeMfPC9TuVv08PxoPXEq7nBKPIM021HjcwNWVj200kmEkQ45z'); // 替换为您的 Stripe 私钥

            try {
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'Donation',
                            ],
                            'unit_amount' => $amount * 100, // Stripe 以最小货币单位计算
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => $success,
                    'cancel_url' => $cancel,
                ]);

                header("HTTP/1.1 303 See Other");
                header("Location: " . $session->url);
                exit();
            } catch (Exception $e) {
                $this->_f3->set('message', 'An error occurred: ' . $e->getMessage());
                $view = new Template();
                echo $view->render('views/donate.html');
            }
        } else {
            $view = new Template();
            echo $view->render('views/donate.html');
        }
    }

    function handleSuccess()
    {
        if (isset($_GET['session_id'])) {
            $sessionId = $_GET['session_id'];

            \Stripe\Stripe::setApiKey('sk_test_51PKxM2B3EtH8G57ohLIr117P87NYvxjvGNdKuFYYegAaSNupUxeMfPC9TuVv08PxoPXEq7nBKPIM021HjcwNWVj200kmEkQ45z');
            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);

                // Assuming the user is logged in and their ID is stored in the F3 session
                if ($this->_f3->exists('SESSION.userId')) {
                    $userId = $this->_f3->get('SESSION.userId');
                    $amount = $session->amount_total / 100;

                    // Record the donation directly in handleSuccess
                    $sql = 'INSERT INTO donations (user_id, amount) VALUES (:user_id, :amount)';
                    $statement = $this->dbh->prepare($sql);
                    $statement->bindParam(':user_id', $userId);
                    $statement->bindParam(':amount', $amount);

                    if (!$statement->execute()) {
                        throw new Exception('Failed to record the donation.');
                    }

                    // Get user information
                    $sql = "SELECT first, last, email FROM users WHERE id = :id";
                    $statement = $this->dbh->prepare($sql);
                    $statement->bindParam(':id', $userId);
                    $statement->execute();
                    $userInfo = $statement->fetch(PDO::FETCH_ASSOC);

                    // Set donation and user info to F3 session
                    $this->_f3->set('SESSION.donationAmount', $amount);
                    $this->_f3->set('SESSION.donationDate', date('Y-m-d H:i:s'));
                    $this->_f3->set('SESSION.userInfo', $userInfo);
                }

                $this->_f3->set('message', 'Donation successful! Thank you for your contribution.');
            } catch (Exception $e) {
                $this->_f3->set('message', 'An error occurred: ' . $e->getMessage());
            }
        }

        $view = new Template();
        echo $view->render('views/success.html');
    }
}
