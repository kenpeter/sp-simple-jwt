<?php

// change dir
chdir(dirname(__DIR__));

// auto load
require_once('vendor/autoload.php');

// JWT
use \Firebase\JWT\JWT;

// config
use Zend\Config\Config;

// factory
use Zend\Config\Factory;

// request
use Zend\Http\PhpEnvironment\Request;

/*
 * Get all headers from the HTTP request
 */
// actual request
$request = new Request();

// do get
if ($request->isGet()) {
  // Need to authorization header  
  $authHeader = $request->getHeader('authorization');

  /*
   * Look for the 'authorization' header
   */
  if ($authHeader) {
    /*
     * Extract the jwt from the Bearer
     */
    // sscanf, like printf
    list($jwt) = sscanf( $authHeader->toString(), 'Authorization: Bearer %s');

    if ($jwt) {
      try {
        // Get config
        $config = Factory::fromFile('config/config.php', true);

        // key to sign
        $secretKey = base64_decode($config->get('jwt')->get('key'));
        
        // use algorithm, secret key to decode  
        $token = JWT::decode($jwt, $secretKey, [$config->get('jwt')->get('algorithm')]);

        // NOTE
        // Basically, we parse the token in the above step, passed from the client.
        // we should check that the token is stored in our database.
        // If it is, then we know the request is good.
        //
        // we void the token if user logout or after certain time.

        // get assets
        $asset = base64_encode(file_get_contents('http://lorempixel.com/200/300/cats/'));

        // header is json
        header('Content-type: application/json');
        echo json_encode([
          'img' => $asset
        ]);

      } catch (Exception $e) {
          /*
           * the token was not able to be decoded.
           * this is likely because the signature was not able to be verified (tampered token)
           */
          header('HTTP/1.0 401 Unauthorized');
      }
        } else {
            /*
             * No token was able to be extracted from the authorization header
             */
            header('HTTP/1.0 400 Bad Request');
        }
    } else {
        /*
         * The request lacks the authorization token
         */
        header('HTTP/1.0 400 Bad Request');
        echo 'Token not found in request';
    }
} else {
    header('HTTP/1.0 405 Method Not Allowed');
}
