<?php

/* Validate data for sign up and login form */
class Validate
{

    // Return true if name contains at least 2 chars and no numbers
    static function validName($name) {
        return strlen(preg_replace('/^\d+|\d+$/', '', trim($name))) >= 2;
    }

    // Return true if email is valid: you@example.com
    static function validEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) != false;
    }

    // Return true if password contains at least 2 letters and no spaces
    static function validPassword($password) {
        // check for spaces
//        if (str_contains($password, ' ')) {
//            return false;
//        }

        // check for at least 2 letters
        if (preg_match_all('/[a-zA-Z]/', $password) < 2) {
            return false;
        }

        // password is valid
        return true;
    }
}