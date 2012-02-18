<?php
/*
    Demo App By :- Virendra Rajput 
    Read more about it here :- http://teckzone.in/blog/2012/02/friends-status-sample-iframe-base-facebook-app-using-php-sdk-v-3-1-1/
    HomePage :- www.teckzone.in
    Created on Feb 18, 2012
*/

require 'facebook.php'; 

$facebook = new Facebook(array(
    'appId' => ' ',    //Your App ID
    'secret' => ' '    //Your App Secret
));

$user = $facebook->getUser();

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

$loginUrl = $facebook->getLoginUrl(
            array(
                'scope' => 'read_stream',
                'redirect_uri' => 'http://apps.facebook.com/friends-status-on-fb/' // Your Redirect URL
));

$logoutUrl = $facebook->getLogoutUrl();
?>

<!DOCTYPE HTML>
<html>
<head>
<title>Your Friends Status Updates</title>
<style type="text/css">
body {
	background-color:rgb(102,131,244);
	font-family:Tahoma, Geneva, sans-serif;
}
h1 {
	alignment-adjust:central;
	font-family:"Palatino Linotype", "Book Antiqua", Palatino, serif;
	font-size:36px;
	color:#EEEEEE;
	text-align:center;
}
p {
	color:#000000;
}
h2 {
	color:#EEEEEE;
	font-style:normal;
	font-family:Tahoma, Geneva, sans-serif;
	font-size:24px;
	text-align:center;
}
    * {
    margin: 1;
    }
    html, body {
    height: 100%;
    }
    .wrapper {
    min-height: 100%;
    height: auto !important;
    height: 100%;
    margin: 0 auto -4em;
    }
    .footer, .push {
    height: 4em;
    }
</style>
</head>
<body>
<div class="wrapper">
<h1>Your Friends Status Updates</h1>
<?php if ($user){
           echo "<h2>Welcome ".$user_profile['name'];
		   echo "</h2><br>";
} else {
echo "<h2>Please Login <br><br>";
?> 
<a href="<?php echo $loginUrl; ?>" target="_blank">Click here</a>
</h2>
<?php } ?>

<?php if ($user) { ?>
<?php

function get_data($multiquery) {
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch,CURLOPT_URL,$multiquery);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

$access_token = $facebook->getAccessToken();

$query = "Select uid, message from status where uid in (select uid2 from friend where uid1 = me() limit 20)";
$multiquery = "https://graph.facebook.com/fql?q=" . rawurlencode($query) . "&access_token=" . $access_token;

$json = get_data($multiquery);

$info = json_decode($json, true) ;
// debug, if json_decode fails
// $error = json_last_error(); echo $error; exit;
// debug, check structure result
//echo "<pre>"; print_r($info ); echo "</pre>"; exit;

foreach( $info['data'] as $status ) {
$fid = $status['uid'];
$message = $status['message'];

// URLs (from http://www.phpro.org/examples/URL-to-Link.html)
$message = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a target=\"_blank\" href=\"$1\" target=\"_blank\">$1</a>",$message);

// hash tags map to search?q=#hash
$message = preg_replace('/(#)(\S+)/i',"<a target=\"_blank\" href=\"http://facebook.com/search?q=%23$2\" target=\"_blank\">$1$2</a>",$message);

//Displaying the statuses of friends
echo "<p><br>";
echo '<br>';
echo '<a target=\"_blank\" href="http://www.facebook.com/profile.php?id='.$fid.'"><img src="http://graph.facebook.com/'.$fid.'/picture"></a>';
echo "<br>";
echo $message;
echo "</p><br>";
}
?>
<?php } ?>
<div class="push"></div>
</div>
<div class="footer" align="center">
   <p>Developed By <a href="http://www.teckzone.in" target="_blank">TeckZone</a></p>
</div>
</body>
</html>