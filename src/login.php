<?php

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!isset($_POST['username']) || !isset($_POST['password'])) {
  header("Location: index.php?err=1" . time());
  die();
}

$username = $_POST['username'];
$password = $_POST['password'];

// isYubikeyEnabled() ?
$otp = (isset($_POST['otp'])) ? $_POST['otp'] : "";
$fw = (isset($_POST['fw'])) ? $_POST['fw'] : "";

//DUO 2FA support added by @rurapenthe0
//Get these values from your DUO admin page when adding a new app. Add them between the quotes.
define('IKEY', "");
define('SKEY', "");
define('HOST', "");
//This is a unique key YOU must generate. Make it strong.
define('MYKEY', "<delete me and put key here>");

if (strlen($username) == 0 || strlen($password) == 0) {
  header("Location: index.php?err=2" . time());
  die();
}

$LOGIN->login($username, $password, $otp);

if ($LOGIN->isLoggedin()) {
	if (isset($_POST['sig_response'])) {
		$resp = Duo\Web::verifyResponse(IKEY, SKEY, AKEY, $_POST['sig_response']);
		if ($resp === USERNAME) {
 			 if (strlen($fw) > 0) {
    				$fw = urldecode($fw);
    				$url = Util::buildServerUrl() . ((Util::startsWith($fw, '/')) ? "" : "/") . $fw;
    				header("Location: " . $url);
    				die();
				}
		}
	}
	else {
		 $sig_request = Duo\Web::signRequest(IKEY, SKEY, AKEY, $_POST['user']);
    		 ?>
       		 <script type="text/javascript" src="js/Duo-Web-v2.js"></script>
        	 <link rel="stylesheet" type="text/css" href="static/Duo-Frame.css">
      	         <iframe id="duo_iframe"
           	 data-host="<?php echo HOST; ?>"
            	 data-sig-request="<?php echo $sig_request; ?>"
        	 ></iframe>
		<?php
  	}
  header("Location: index.php");
  die();
}

header("Location: index.php?err=3" . time());




