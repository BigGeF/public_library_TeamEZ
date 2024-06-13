<?php

/**
 * Class EmailController
 * Handles contact form submission and email sending.
 */
class EmailController
{
    private $_f3;

    /**
     * EmailController constructor.
     *
     * @param object $_f3 Fat-Free Framework instance.
     */
    public function __construct($_f3)
    {
        $this->_f3 = $_f3;
    }

    /**
     * Handles contact form submission.
     * If the request method is POST, it processes the form data and sends an email.
     * Sets a message indicating whether the email was sent successfully or not.
     * Renders the contact form page.
     */
    function contact()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            error_log("Form submitted"); // Debug information
            $this->_f3->set('errors', '');

            // declare variables
            $firstName = '';
            $lastName = '';
            $email = '';
            $message = '';

            // validate first name
            if (Validate::validName($_POST['firstName'])) {
                $firstName = $_POST['firstName'];
            } else {
                $this->_f3->set('errors["firstName"]', 'Invalid first name');
            }

            // validate last name
            if (Validate::validName($_POST['lastName'])) {
                $lastName = $_POST['lastName'];
            } else {
                $this->_f3->set('errors["lastName"]', 'Invalid last name');
            }

            // validate email
            if (Validate::validEmail($_POST['email'])) {
                $email = $_POST['email'];
            } else {
                $this->_f3->set('errors["email"]', 'Please enter a valid email');
            }

            // validate message
            if (Validate::validMessage($_POST['message'])) {
                $message = $_POST['message'];
            } else {
                $this->_f3->set('errors["message"]', 'Please enter a message with more than 2 characters');
            }

            // Email admin if no errors are found
            if (empty($this->_f3->get('errors'))) {
                // Use PHP's built-in mail function to send email
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



        }

        // Render the contact page
        $view = new Template();
        echo $view->render('views/contact.html');
    }
}
