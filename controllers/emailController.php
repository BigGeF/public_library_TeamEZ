<?php
// This is an email controller file

class EmailController
{
    private $_f3;

    public function __construct($_f3)
    {
        $this->_f3 = $_f3;
    }

    function contact()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            error_log("Form submitted"); // 调试信息

            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $email = $_POST['email'];
            $message = $_POST['message'];

            // 使用 PHP 内置的 mail 函数发送邮件
            $to = 'Fan.Hao@student.greenriver.edu';
            $subject = 'Contact Form Submission';
            $body = "First Name: $firstName\nLast Name: $lastName\nEmail: $email\nMessage: $message";
            $headers = "From: $email";

            if (mail($to, $subject, $body, $headers)) {
                $this->_f3->set('message', 'Message has been sent');
            } else {
                $this->_f3->set('message', 'Failed to send message');
            }
        }

        // Render the contact page
        $view = new Template();
        echo $view->render('views/contact.html');
    }
}
