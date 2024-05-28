<?php
require_once('vendor/autoload.php');

class DonationController
{
    private $_f3;

    public function __construct($f3)
    {
        $this->_f3 = $f3;
    }

    function donate()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $amount = $_POST['amount'];

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
                    'success_url' => 'https://yourdomain.com/success',
                    'cancel_url' => 'https://yourdomain.com/cancel',
                ]);

                header("HTTP/1.1 303 See Other");
                header("Location: " . $session->url);
                exit();
            } catch (Exception $e) {
                $this->_f3->set('message', 'An error occurred: ' . $e->getMessage());
                $view = new Template();
                echo $view->render('views/donate.html');
            }
        }
        $view = new Template();
        echo $view->render('views/donate.html');
    }
}
