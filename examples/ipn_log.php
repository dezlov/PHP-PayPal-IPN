<?php

/*
 * This script verifies and logs to a file all received IPN requests.
 * You can use it with PayPal IPN Simulator, just upload it to your server
 * and specify its path as IPN handler URL:
 * https://developer.paypal.com/developer/ipnSimulator/
 *
 * Make sure that your web server has write access to the folder with this script,
 * otherwise you won't see any logs being created.
 */

include(dirname(dirname(__FILE__)).'/src/IpnListener.php');

use dezlov\PayPal\IpnListener;

$listener = new IpnListener();
$listener->use_sandbox = true;

$error = null;
$verified = $listener->tryProcessIpn(null, $error);
$report = $listener->getTextReport();
ipn_log($verified, $report, $error);

function ipn_log($verified, $report, $error)
{
	if ($verified)
	{
		$filename = 'ipn_verified.log';
		$content = $report;
	}
	else
	{
		$filename = 'ipn_errors.log';
		$content = 'ERROR: '.$error.PHP_EOL.$report;
	}
	file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
}
