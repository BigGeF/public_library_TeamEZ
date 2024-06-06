<?php

class DataLayer
{
    private static $dbh = null;

    private function __construct() {}

    public static function getConnection()
    {
        if (self::$dbh === null) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/../libraryConfig.php';

            try {
                // Instantiate our PDO Database Object
                self::$dbh = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        return self::$dbh;
    }

    // Dummy data for books borrowed
    public static function getMyBorrowsData()
    {
        $path = 'model/testDataBorrows.json';
        $jsonString = file_get_contents($path);
        return json_decode($jsonString);
    }

    // Data from Google Books API
    public static function getSearchResultsCurl($searchTerm, $printType)
    {
        $url = "https://www.googleapis.com/books/v1/volumes?q=" . $searchTerm . "&printType=" . $printType;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return json_decode($output);
    }

    public static function isOverdue($returnDate)
    {
        $date = strtotime($returnDate);
        return ceil(($date - time()) / 60 / 60 / 24) < 0;
    }

    public static function getDaysFromReturnDate($returnDate)
    {
        $date = strtotime($returnDate);
        return abs(ceil(($date - time()) / 60 / 60 / 24)); // Absolute value used so no negatives returned
    }

    // Donation handling methods
    public static function createCheckoutSession($amount, $success_url, $cancel_url)
    {
        \Stripe\Stripe::setApiKey('sk_test_51PKxM2B3EtH8G57ohLIr117P87NYvxjvGNdKuFYYegAaSNupUxeMfPC9TuVv08PxoPXEq7nBKPIM021HjcwNWVj200kmEkQ45z'); // 替换为您的 Stripe 私钥

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
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
        ]);

        return $session->url;
    }

    public static function handleSuccess($sessionId, $f3)
    {
        \Stripe\Stripe::setApiKey('sk_test_51PKxM2B3EtH8G57ohLIr117P87NYvxjvGNdKuFYYegAaSNupUxeMfPC9TuVv08PxoPXEq7nBKPIM021HjcwNWVj200kmEkQ45z');

        $session = \Stripe\Checkout\Session::retrieve($sessionId);

        if ($f3->exists('SESSION.userId')) {
            $userId = $f3->get('SESSION.userId');
            $amount = $session->amount_total / 100;

            $dbh = self::getConnection();
            $sql = 'INSERT INTO donations (user_id, amount) VALUES (:user_id, :amount)';
            $statement = $dbh->prepare($sql);
            $statement->bindParam(':user_id', $userId);
            $statement->bindParam(':amount', $amount);

            if (!$statement->execute()) {
                throw new Exception('Failed to record the donation.');
            }

            // Get user information
            $sql = "SELECT first, last, email FROM users WHERE id = :id";
            $statement = $dbh->prepare($sql);
            $statement->bindParam(':id', $userId);
            $statement->execute();
            $userInfo = $statement->fetch(PDO::FETCH_ASSOC);

            // Set donation and user info to F3 session
            $f3->set('SESSION.donationAmount', $amount);
            $f3->set('SESSION.donationDate', date('Y-m-d H:i:s'));
            $f3->set('SESSION.userInfo', $userInfo);
        }

        $f3->set('message', 'Donation successful! Thank you for your contribution.');
    }

    public static function getLeaderboard()
    {
        $dbh = self::getConnection();
        $sql = "SELECT users.first, users.last, SUM(donations.amount) as total_amount
                FROM donations
                JOIN users ON donations.user_id = users.id
                GROUP BY donations.user_id
                ORDER BY total_amount DESC";
        $statement = $dbh->prepare($sql);
        $statement->execute();
        $leaderboard = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Calculate rank
        foreach ($leaderboard as $index => &$donor) {
            $donor['rank'] = $index + 1;
        }

        return $leaderboard;
    }

    // Send overdue email method
    public static function sendOverdueEmail($overdueUserID, $overdueItem)
    {
        $dbh = self::getConnection();
        $sql = "SELECT * FROM users WHERE id = :id";
        $statement = $dbh->prepare($sql);
        $statement->bindParam(':id', $overdueUserID);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $firstName = $user["first"];
            $lastName = $user["last"];
            $email = $user["email"];
            $message = "This is just a friendly reminder that " . $overdueItem . " is overdue. Please return the 
                        item at your earliest convenience.";

            $to = 'miss.matthew@student.greenriver.edu';
            $subject = 'Overdue Item Notification';
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
