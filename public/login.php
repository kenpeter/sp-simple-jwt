<?php

// to current dir
chdir(dirname(__DIR__));

// autoload
require_once('vendor/autoload.php');

// zend factory
use Zend\Config\Factory;

// http request
use Zend\Http\PhpEnvironment\Request;

// request object
$request = new Request();

/*
 * Validate that the request was made using HTTP POST method
 */

// if request is post
if ($request->isPost()) {
  /*
   * Simple sanitization
   */

  // User name and pass, coming from form submit
  $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
  $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
  
  // Coming from index.html  
  if ($username && $password) {
    try {
      // Factory gets config
      $config = Factory::fromFile('config/config.php', true);
      
      // mysql connection string
      $dsn = 'mysql:host=' . $config->get('database')->get('host') . ';dbname=' . $config->get('database')->get('name');
    
      // pdo, actual connect
      $db = new PDO($dsn, $config->get('database')->get('user'), $config->get('database')->get('password'));
                 
      $sql = <<<EOL
      SELECT id,
             password
      FROM   users
      WHERE  username = ?
EOL;
    
      $stmt = $db->prepare($sql);

      // pass array of something
      $stmt->execute([$username]);

      $rs = $stmt->fetch();
            
      if ($rs) {
        /*
         * Password was generated by password_hash(), so we need to use
         * password_verify() to check it.
         * 
         * @see http://php.net/manual/en/ref.password.php
         */
        if (password_verify($password, $rs['password'])) {
          // different seed, mcrypt_create_iv
          // token id, generated by server
          $tokenId = base64_encode(mcrypt_create_iv(32));

          // Current time
          $issuedAt = time();

          // Delay
          $notBefore = $issuedAt + 10;  //Adding 10 seconds

          // Adding 60 seconds, the jwt expired.
          $expire = $notBefore + 60; 

          // part of the payload
          $serverName = $config->get('serverName');
                    
          /*
           * Create the token as an array
           */
          $data = [
            'iat'  => $issuedAt,         // Issued at: time when the token was generated
            'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss'  => $serverName,       // Issuer
            'nbf'  => $notBefore,        // Not before
            'exp'  => $expire,           // Expire
            'data' => [                  // Data related to the signer user
              'userId'   => $rs['id'], // userid from the users table
              'userName' => $username, // User name
            ]
          ];
          
          // header as json type          
          header('Content-type: application/json');
                    
                    /*
                     * Extract the key, which is coming from the config file. 
                     * 
                     * Best suggestion is the key to be a binary string and 
                     * store it in encoded in a config file. 
                     *
                     * Can be generated with base64_encode(openssl_random_pseudo_bytes(64));
                     *
                     * keep it secure! You'll need the exact key to verify the 
                     * token later.
                     */
                    $secretKey = base64_decode($config->get('jwt')->get('key'));
                    
                    /*
                     * Extract the algorithm from the config file too
                     */
                    $algorithm = $config->get('jwt')->get('algorithm');
                    
                    /*
                     * Encode the array to a JWT string.
                     * Second parameter is the key to encode the token.
                     * 
                     * The output string can be validated at http://jwt.io/
                     */
                    $jwt = JWT::encode(
                        $data,      //Data to be encoded in the JWT
                        $secretKey, // The signing key
                        $algorithm  // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
                        );
                        
                    $unencodedArray = ['jwt' => $jwt];
                    echo json_encode($unencodedArray);
                } else {
                    header('HTTP/1.0 401 Unauthorized');
                }
            } else {
                header('HTTP/1.0 404 Not Found');
            }
        } catch (Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
        }
    } else {
        header('HTTP/1.0 400 Bad Request');
    }
} else {
    header('HTTP/1.0 405 Method Not Allowed');
}
