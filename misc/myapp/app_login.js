// https://stackoverflow.com/questions/9768192/sending-data-through-post-request-from-a-node-js-server-to-a-node-js-server

// Need query string
var querystring = require('querystring');
var http = require('http');

// stirngify username and password
var data = querystring.stringify({
  username: "test", // match up with login.php username
  password: "test" // match up with password password
});

// Don't write http://jwt.local
// https://stackoverflow.com/questions/17690803/node-js-getaddrinfo-enotfound
var options = {
  host: 'jwt.local', // no http
  port: 80,
  path: '/login.php',
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded', // url encode
    'Content-Length': Buffer.byteLength(data) // buffer byte length
  }
};

var req = http.request(options, function(res) {

  // set utf8
  res.setEncoding('utf8');

  res.on('data', function (chunk) {
    console.log("body: " + chunk);
  });

  
});

// write data
req.write(data);

// end
req.end();
