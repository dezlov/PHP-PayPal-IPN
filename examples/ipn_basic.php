<?php

/*
 * A basic script which will verify IPN via the sandbox server.
 * Uses an alternative tryProcessIpn() method which will not throw exceptions
 * but instead will return an error in the $error variable if an error occurs.
 * 
 * Note that 'echo' commands are just for demonstration purposes.
 * In reality, your script is going to be called by PayPal server and you
 * will not actually see the output unless you log it somewhere on your server.
 */

include(dirname(dirname(__FILE__)).'/src/IpnListener.php');

use dezlov\PayPal\IpnListener;

$listener = new IpnListener();
$listener->use_sandbox = true;

$error = null;
$verified = $listener->tryProcessIpn(null, $error);
if ($verified)
	echo 'IPN verified successfully.'.PHP_EOL;
else
	echo 'IPN error: '.$error.PHP_EOL;
