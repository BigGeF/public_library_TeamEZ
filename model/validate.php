<?php

/**
 * Class Validate
 * Provides validation methods for user input.
 */
class Validate
{

    /**
     * Validates a name.
     *
     * @param string $name The name to validate.
     * @return bool True if the name contains at least 2 characters and no numbers, false otherwise.
     */
    static function validName($name) {
        return strlen(preg_replace('/^\d+|\d+$/', '', trim($name))) >= 2;
    }

    /**
     * Validates an email address.
     *
     * @param string $email The email address to validate.
     * @return bool True if the email address is valid, false otherwise.
     */
    static function validEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) != false;
    }

    /**
     * Validates a password.
     *
     * @param string $password The password to validate.
     * @return bool True if the password contains at least 2 letters and no spaces, false otherwise.
     */
    static function validPassword($password) {

        // check for at least 2 letters
        if (preg_match_all('/[a-zA-Z]/', $password) < 2) {
            return false;
        }

        // password is valid
        return true;
    }

    /**
     * Validates a contact message.
     *
     * @param string $msg The message to validate.
     * @return bool True if the message contains at least 2 characters, false otherwise.
     */
    static function validMessage($msg) {
        return strlen(trim($msg)) >= 2;
    }

}