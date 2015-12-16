<?php

/*
 * A basic script which will verify IPN via the sandbox server.
 * If an error occurs inside of processIpn() we will catch it
 * and output full text report.
 *
 * Note that 'echo' commands are just for demonstration purposes.
 * In reality, your script is going to be called by PayPal server and you
 * will not actually see the output unless you log it somewhere on your server.
 */

include(dirname(dirname(__FILE__)).'/src/IpnListener.php');

use dezlov\PayPal\IpnListener;

$listener = new IpnListener();
$listener->use_sandbox = true;

try
{
	$verified = $listener->processIpn();
	if ($verified)
		echo 'IPN request was "VERIFIED".'.PHP_EOL;
	else
		echo 'IPN request was "INVALID".'.PHP_EOL;
}
catch (Exception $e)
{
	echo 'IPN error: '.$e->getMessage().PHP_EOL;
	echo $listener->getTextReport();
}
