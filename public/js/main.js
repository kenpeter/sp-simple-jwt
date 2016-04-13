$(function(){
  // to store the jwt 
  var store = store || {};

  // init func
  store.init = function() {

    // issue at, store prop
    this.iat = null;

    // not before, store prop
    this.nbf = null;

    // expired, store prop
    this.exp = null;

    // time, html
    this.currentTimeContainer = $("#currentTime");

    // issue at, html
    this.iatContainer = $("#iat");

    // not before, html
    this.nbfContainer = $("#nbf");

    // expired, html
    this.expContainer = $("#exp");

    // jwt, html
    this.tokenContainer = $("#token");

    // decoded jwt, html
    this.decodedTokenContainer = $("#decodedToken");
  }

  /**
   * Export the current JWT values to the specified containers.
   */
  store.exportValues = function(){
    // put token into html
    this.tokenContainer.html(this.jwt || '&nbsp;');

    // Parse the claim to json
    var parsedJSON = JSON.parse(this.claim);

    var iatString = '';
    var nbfString = '';
    var expString = '';

    if (parsedJSON) {
      // iat prop
      // need 1000   
      this.iat = new Date(parsedJSON.iat * 1000);

      //
      this.nbf = new Date(parsedJSON.nbf * 1000);

      //  
      this.exp = new Date(parsedJSON.exp * 1000);

      // this.iat, obj to string
      iatString = this.dateObjToString(this.iat);

      nbfString = this.dateObjToString(this.nbf);

      expString = this.dateObjToString(this.exp);
    }

    // beautify the claim
    var beautifiedJSON = JSON.stringify(parsedJSON, null, 4);

    // assign the claim to html
    this.decodedTokenContainer.html( ((this.claim)) ? beautifiedJSON : '&nbsp;');

    // assign iat, nbf, exp to html
    store.iatContainer.html(iatString);
    store.nbfContainer.html(nbfString);
    store.expContainer.html(expString);

  }

    /**
     * Decodes the JWT
     * @param jwt
     * @returns {*}
     */
    store.decodeToken = function(jwt){
      // The token has 3 parts, 1. header; 2. claim + payload; 3. signature.
      var a = jwt.split(".");

      // Get 2nd part, to claim, iss, iat, etc
      // 2nd part is claim, then we decode it.
      return b64utos(a[1]);
    }

    /**
     * Sets the JWT to the store object
     * @param data
     */
    store.setJwt = function(data){

      // here we extend the store
      // store contains jwt and claim
      // jwt is jwt, claim
      this.jwt = data;
      this.claim = this.decodeToken(data);
    }

    /**
     *
     * @param date
     * @returns {string}
     */
    store.dateObjToString = function(date) {
      return date.toDateString() + ' ' + date.toLocaleTimeString();
    }

    setInterval(function() {
      var currentTime = new Date();
      store.currentTimeContainer.html(store.dateObjToString(currentTime));
    }, 100);

    // Basically, the idea is 
    // 1. user post with username and password
    // 2. username and password sent to server.
    // 3. server generates jwt
    // 4. jwt contains jwt meta, payload returned by server, secret key, algorithm
    // 5. server has a secret key to encode the jwt.
    // 6. the client doesn't have the server secret key, it gets the entire jwt, then return back to server
    // 7. the client has callback to catch the jwt, returned by server.
    // 8. the client should store the jwt, because it use for the next request.
    $("#frmLogin").submit(function(e){
      // prevent
      e.preventDefault();

      // auth/token post
      //$.post('auth/token', $("#frmLogin").serialize(), function(data){
      $.post('login.php', $("#frmLogin").serialize(), function(data){        

        // we store the jwt in client site
        store.setJwt(data.jwt);
    
        //test
        console.log("after login, jwt");
        console.log(data.jwt);

        // Export to html?
        store.exportValues();

      }).fail(function(){
        alert('form login error');
      });
    });

    $("#btnGetResource").click(function(e){
      e.preventDefault();
      $.ajax({
        //url: 'resource/image',
        url: 'resource.php',
        beforeSend: function(request){
          // Contain text "Authorization"
          // store.jwt
          
          //test
          console.log("btn get resource, jwt");
          console.log(store.jwt);

          // subsequence request with jwt
          request.setRequestHeader('Authorization', 'Bearer ' + store.jwt);
        },
        type: 'GET',
        success: function(data) {
          $("#resourceContainer").html('<img src="data:image/jpeg;base64,' + data.img + '" />');
        },
        error: function() {
          alert('resource button error');
        }
      });
    });

    $("#btnExpire").click(function(e){
      // prevent default
      e.preventDefault();

      // jwt
      store.jwt = null;

      // claim, 
      store.claim = null;

      // issue at
      store.iat = null;

      store.nbf = null;

      store.exp = null;

      store.exportValues();

      $("#resourceContainer").html('');
    });

    store.init();
});
