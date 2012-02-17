<?php

require 'facebook.php';

$facebook = new Facebook(array(
    'appId'  => ' ',
    'secret' => ' '
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

$loginUrl   = $facebook->getLoginUrl(
            array(
                'scope'         => 'read_stream',
                'redirect_uri'  => 'http://apps.facebook.com/friends-status-on-fb/'
));
	
$logoutUrl  = $facebook->getLogoutUrl();
?>

<!DOCTYPE HTML>
<html>
<head>
<title>Your Friends Status Updates</title>
</head>
<body>
<center><h1>Your Friends Status Updates</h1></center>

<?php if ($user){
           echo $user_profile['name'];
	     } else {
		   echo "Please Login <br>";
		   echo $loginUrl;
        } ?>
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

$query = "Select uid, message from status where uid in (select uid2 from friend where uid1 = me() limit 50)";
$multiquery = "https://graph.facebook.com/fql?q=" . rawurlencode($query) . "&access_token=" . $access_token;

$json = get_data($multiquery);

$info = json_decode($json, true) ;
// debug, if json_decode fails
// $error = json_last_error(); echo $error; exit;  
// debug, check structure result
//echo "<pre>"; print_r($info ); echo "</pre>"; exit; 

$final = array();
foreach( $info['data'] as $status ) {
    $fid = $status['uid'];
	$message = $status['message'];

				    // URLs (from http://www.phpro.org/examples/URL-to-Link.html)
					$message = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a target=\"_blank\" href=\"$1\" target=\"_blank\">$1</a>",$message);

					// hash tags map to search?q=#hash
					$message = preg_replace('/(#)(\S+)/i',"<a target=\"_blank\" href=\"http://facebook.com/search?q=%23$2\" target=\"_blank\">$1$2</a>",$message);	

                     $pageContent = file_get_contents('http://graph.facebook.com/'.$fid);
                     $parsedJson  = json_decode($pageContent);

					 echo "<p><li>";
                     echo '<a target=\"_blank\" href="http://www.facebook.com/profile.php?id='.$fid.'"><img src=\"http://graph.facebook.com/$fid/picture\"></a><br>';
                     echo $parsedJson->name;
					 echo ':-';
					 echo $message;
					 echo "</p></li><br>";	
}
?>
<?php } ?>
</body>
</html>