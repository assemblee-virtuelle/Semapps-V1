<?php

/**
 * Example of password hashing and verification with password_hash and
 * password_verify
 *
 * This script is intended to be run from the command line
 * like so: 'php -f password_hash_example.php'
 *
 * @see http://stackoverflow.com/a/20809916/1134565
 */
/**
 * STEP 1: USER JOINS SITE AND CREATES PASSWORD
 */
// User signs up and creates a password:
$password = 'test';
$passwordHash = password_hash($password, PASSWORD_BCRYPT,["cost"=>13]);
// These are the hashed password's components
// password_verify will use this info to recreate the hash created by password_hash()
$algo = substr($passwordHash, 0, 4); // $2y$ == BLOWFISH
$cost = substr($passwordHash, 4, 2);
$salt = substr($passwordHash, 7, 22);
/**
 * STEP 2: USER LOGS INTO SITE
 */
// User now attempts to log in with $password:
// User provides the password in plain text
$plainText = $password;
// Password hash created when user signed up is now retireved from database
$passwordHashFromDatabase = $passwordHash;
// The application will now use password_verify() to recreate the hash and test
// it against the hash in the database.
$result = password_verify($plainText, $passwordHashFromDatabase);
$success = ($result) ? 'True': 'False';
echo $success."<br>";
echo $password."<br>";
echo $passwordHash."<br>";

