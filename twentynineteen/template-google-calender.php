<?php
/* Template Name: Google Calender */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if (!isset($_GET['booking_id']) || empty('booking_id')) {
    wp_redirect(home_url());
    exit();
}
$trackingHash = $_GET['booking_id'];
$booking_id = encrypt_decrypt($trackingHash, 'decrypt');
if (!$booking_id) {
    wp_redirect(home_url());
    exit();
}
$booking = MPHB()->getBookingRepository()->findById( $booking_id, true );
if (!$booking) {
    wp_redirect(home_url());
    exit();
}



?>
<?php get_header(); ?>
<div style="text-align:center">
<a class="btn  btn-primary backBtn" style="display:none" href="<?php echo home_url() ?>">Back to home</a>
</div>
<?php get_footer(); ?>


<button id="authorize-button" class="btn btn-primary"><img src="https://booking.arienzobeachclub.com/wp-content/uploads/2021/12/google.png"  /> <span style="    vertical-align: text-top;">Add To Google Calender</span></button>
<script async defer src="https://apis.google.com/js/api.js"
  onload="this.onload=function(){};handleClientLoad()"
  onreadystatechange="if (this.readyState === 'complete') this.onload()">
</script>

<style>
    #authorize-button,.backBtn{
        background: #ededed;
    color: #262626;
    border: 1px solid #cdcdcd;
    border-radius: 0;
    line-height: 1;    
    display:none;
        padding: 10px;
    }
    
</style>

<script type="text/javascript">
  var now = new Date("<?= date("Y-m-d 00:00:00",$booking->getCheckInDate()->getTimestamp()) ?>");
  today = now.toISOString();


  var now = new Date("<?= date("Y-m-d 23:59:59",$booking->getCheckInDate()->getTimestamp()) ?>");
  enddate = now.toISOString();


  var resource = {
    "summary": "Arienzo Beach Club Booking",
    "start": {
      "dateTime": today
    },
    "end": {
      "dateTime": enddate
    }
  };

  // Client ID and API key from the Developer Console
  var CLIENT_ID = '707294692731-guc4lkkk6pihupvu15d3e0vffqnnaame.apps.googleusercontent.com';
  var API_KEY = 'AIzaSyCS9EsAmLgKtsrJxvW-d7abaMMWTtpsqbo';

  // Array of API discovery doc URLs for APIs used by the quickstart
  

  // Authorization scopes required by the API; multiple scopes can be
  // included, separated by spaces.
  var SCOPES = "https://www.googleapis.com/auth/calendar";

  
  

  /**
   *  On load, called to load the auth2 library and API client library.
   */
  function handleClientLoad() {
    gapi.load('client:auth2', initClient);
  }

  /**
   *  Initializes the API client library and sets up sign-in state
   *  listeners.
   */
  function initClient() {
    gapi.client.init({
      apiKey: API_KEY,
      clientId: CLIENT_ID,
      scope: SCOPES
    }).then(function () {
      // Listen for sign-in state changes.
      gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);

      // Handle the initial sign-in state.
      updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
      
      
    }, function(error) {
      
    });
  }

  /**
   *  Called when the signed in status changes, to update the UI
   *  appropriately. After a sign-in, the API is called.
   */
  var handleAuthClick = function(event) {
    gapi.auth2.getAuthInstance().signIn();
  }

   var makeApiCall = function() {
    gapi.client.load('calendar', 'v3', function() {         // load the calendar api (version 3)
      var request = gapi.client.calendar.events.insert({
        'calendarId':   'primary',  // calendar ID
        "resource":     resource              // pass event details with api call
      });
      
      // handle the response from our api call
      request.execute(function(resp) {
        /*if(resp.status=='confirmed') {
          document.getElementById('event-response').innerHTML = "Event created successfully. View it <a href='" + resp.htmlLink + "'>online here</a>.";
        } else {
          document.getElementById('event-response').innerHTML = "There was a problem. Reload page and try again.";
        }*/
        /* for (var i = 0; i < resp.items.length; i++) {    // loop through events and write them out to a list
          var li = document.createElement('li');
          var eventInfo = resp.items[i].summary + ' ' +resp.items[i].start.dateTime;
          li.appendChild(document.createTextNode(eventInfo));
          document.getElementById('events').appendChild(li);
        } */
        //console.log(resp);
        alert("Event add To calendar successfully");
        jQuery(".backBtn").show();
      });
    });
  }
  var auth_already = "";
  function updateSigninStatus(isSignedIn) {
    if (isSignedIn) {
        makeApiCall();
    }else{
      gapi.auth2.getAuthInstance().signIn();
      auth_already = 1;
    }
  }

  /**
   *  Sign in the user upon button click.
   */
  
 
 
</script>