<?php

// Always need the autoloader firstly
require_once('../vendor/autoload.php');

// JWT
use \Firebase\JWT\JWT;

// key to sign
$key = "example_key";

// token
$token = array(
  "iss" => "http://example.org",
  "aud" => "http://example.com",
  "iat" => 1356999524,
  "nbf" => 1357000000
);

// key to sign the token, jwt is out.
// jwt contains the token
$jwt = JWT::encode($token, $key);

// decode the token, with $jwt, $key, algorithm 
$decoded = JWT::decode($jwt, $key, array('HS256'));

// Will get the original token
print_r($decoded);


// $decoded is object, not originally array
$decorded_array = (array) $decoded;

// Increase the time between encoding (client) and decoding (server)
JWT::$leeway = 60;

$decoded = (array) JWT::decode($jwt, $key, array('HS256'));

print_r($decoded);
