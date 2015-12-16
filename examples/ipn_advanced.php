<?php

/*
 * Advanced example which demonstrates the complete processing chain of IPN,
 * including validation of IPN, verification of transaction details,
 * order fulfilment and error logging.
 *
 * This example follows the recommended practices of IPN integration from PayPal:
 * https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNIntro/
 *
 * Dummy functions at the bottom of the script give examples of how to actually
 * handle various parts of the process.
 */

include(dirname(dirname(__FILE__)).'/src/IpnListener.php');

use dezlov\PayPal\IpnListener;

$listener = new IpnListener();
$listener->use_sandbox = true;

try
{
	// Process IPN request
	$verified = $listener->processIpn();
	if (!$verified)
		throw new Exception('Invalid IPN request');

	// Skip all notifications except for completed payments
	$payment_status = $listener->getData('payment_status');
	if ($payment_status != 'Completed')
		exit;

	// Check transaction ID
	$transaction_id = $listener->getData('txn_id');
	if (!check_transaction($transaction_id))
		throw new Exception('Unable to process IPN for transaction ID: '.$transaction_id);

	// Check receiver email
	$receiver_email = $listener->getData('receiver_email');
	if (!check_receiver_email($receiver_email))
		throw new Exception('Unable to process IPN for receiver email: '.$receiver_email);

	// Check price and currency
	$payment_total = $listener->getData('mc_gross');
	$payment_currency = $listener->getData('mc_currency');
	if (!check_price($payment_total, $payment_currency))
		throw new Exception('Unable to process IPN due to issues with price/currency');

	// If we got this far, then its ok to fulfil the order
	$item_name = $listener->getData('item_name');
	$item_number = $listener->getData('item_number');
	$payer_name = trim($listener->getData('first_name').' '.$listener->getData('last_name'));
	$payer_email = $listener->getData('payer_email');
	process_order($transaction_id, $item_name, $item_number, $payer_name, $payer_email);

	// Tell PayPal that we have successfully processing IPN.
	header('HTTP/1.1 200 OK');
}
catch (Exception $e)
{
	// Tell PayPal that we had problems processing IPN.
	header('HTTP/1.1 500 Internal Server Error');

	// Report error message.
	$message = 'IPN error: '.((string)$e);
	$message .= PHP_EOL.PHP_EOL.$listener->getTextReport();
	report_problem($message);
}



function process_order($transaction_id, $item_name, $item_number, $payer_name, $payer_email)
{
	// For example: Fulfil the order, save details in the database.
}

function check_price($total, $currency)
{
	// For example: Check that payment matches your advertised price.
	return true;
}

function check_receiver_email($email)
{
	// For example: Check that email matches your PayPal account.
	return true;
}

function check_transaction($transaction_id)
{
	// For example: Check that transaction ID does not yet exist if your database.
	return true;
}

function report_problem($message)
{
	// For example: Log via PHP error log.
	error_log($message);
}
