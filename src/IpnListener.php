<?php

namespace dezlov\PayPal;

use Exception;

/**
 *  A class for handling Instant Payment Notifications (IPN) from PayPal.
 *
 *  @package    PHP-PayPal-IPN
 *  @link       https://github.com/dezlov/PHP-PayPal-IPN
 *  @author     Denis Kozlov
 *  @license    http://choosealicense.com/licenses/gpl-2.0/
 */
class IpnListener
{
	/**
	 *  If true, the recommended cURL PHP library is used to send the post back
	 *  to PayPal. If flase then fsockopen() is used. Default true.
	 *
	 *  @var boolean
	 */
	public $use_curl = true;

	/**
	 *  If true, cURL will use the CURLOPT_FOLLOWLOCATION to follow any
	 *  "Location: ..." headers in the response.
	 *
	 *  @var boolean
	 */
	public $follow_location = false;

	/**
	 *  If true, the paypal sandbox URI www.sandbox.paypal.com is used for the
	 *  post back. If false, the live URI www.paypal.com is used. Default false.
	 *
	 *  @var boolean
	 */
	public $use_sandbox = false;

	/**
	 *  The amount of time, in seconds, to wait for the PayPal server to respond
	 *  before timing out. Default 30 seconds.
	 *
	 *  @var int
	 */
	public $timeout = 30;

	/**
	 * If true, enable SSL certification validation when using cURL
	 *
	 * @var boolean
	 */
	public $verify_ssl = true;

	/**
	 * Require that POST method is used when processing an IPN request from POST data.
	 * Exception is thrown if this check has failed. Default is TRUE.
	 *
	 * @var boolean
	 */
	public $requirePostMethod = true;

	private $postData = array();
	private $rawPostData = null;
	private $post_uri = '';
	private $response_status = '';
	private $response = '';

	const PAYPAL_HOST = 'www.paypal.com';
	const SANDBOX_HOST = 'www.sandbox.paypal.com';

	/**
	 *  Post Back Using cURL
	 *
	 *  Sends the post back to PayPal using the cURL library. Called by
	 *  the processIpn() method if the use_curl property is true. Throws an
	 *  exception if the post fails. Populates the response, response_status,
	 *  and post_uri properties on success.
	 *
	 *  @todo add URL param so function is more dynamic
	 *
	 *  @param  string  The post data as a URL encoded string
	 */
	protected function curlPost($encoded_data)
	{
		$uri = 'https://'.$this->getPaypalHost().'/cgi-bin/webscr';
		$this->post_uri = $uri;

		$ch = curl_init();

		if ($this->verify_ssl)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_CAINFO, dirname(dirname(__FILE__)) . '/cert/api_cert_chain.crt');
		}

		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_data);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->follow_location);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);

		$this->response = curl_exec($ch);
		$this->response_status = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

		if ($this->response === false || $this->response_status == '0')
		{
			$errno = curl_errno($ch);
			$errstr = curl_error($ch);
			throw new Exception("cURL error: [$errno] $errstr");
		}

		return $this->response;
	}

	/**
	 *  Post Back Using fsockopen()
	 *
	 *  Sends the post back to PayPal using the fsockopen() function. Called by
	 *  the processIpn() method if the use_curl property is false. Throws an
	 *  exception if the post fails. Populates the response, response_status,
	 *  and post_uri properties on success.
	 *
	 *  @todo add URL param so function is more dynamic
	 *
	 *  @param  string  The post data as a URL encoded string
	 */
	protected function fsockPost($encoded_data)
	{
		$uri = 'ssl://'.$this->getPaypalHost();
		$port = '443';
		$this->post_uri = $uri.'/cgi-bin/webscr';

		$fp = fsockopen($uri, $port, $errno, $errstr, $this->timeout);

		if (!$fp)
		{
			// fsockopen error
			throw new Exception("fsockopen error: [$errno] $errstr");
		}

		$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Host: ".$this->getPaypalHost()."\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: ".strlen($encoded_data)."\r\n";
		$header .= "Connection: Close\r\n\r\n";

		fputs($fp, $header.$encoded_data."\r\n\r\n");

		while(!feof($fp))
		{
			if (empty($this->response))
			{
				// extract HTTP status from first line
				$this->response .= $status = fgets($fp, 1024);
				$this->response_status = trim(substr($status, 9, 4));
			}
			else
			{
				$this->response .= fgets($fp, 1024);
			}
		}

		fclose($fp);
		return $this->response;
	}

	private function getPaypalHost()
	{
		return ($this->use_sandbox) ? self::SANDBOX_HOST : self::PAYPAL_HOST;
	}

	public function getPostData()
	{
		return $this->postData;
	}

	public function getRawPostData()
	{
		return $this->rawPostData;
	}

	/**
	 *  Get POST URI
	 *
	 *  Returns the URI that was used to send the post back to PayPal. This can
	 *  be useful for troubleshooting connection problems. The default URI
	 *  would be "ssl://www.sandbox.paypal.com:443/cgi-bin/webscr"
	 *
	 *  @return string
	 */
	public function getPostUri()
	{
		return $this->post_uri;
	}

	/**
	 *  Get Response
	 *
	 *  Returns the entire response from PayPal as a string including all the
	 *  HTTP headers.
	 *
	 *  @return string
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 *  Get Response Status
	 *
	 *  Returns the HTTP response status code from PayPal. This should be "200"
	 *  if the post back was successful.
	 *
	 *  @return string
	 */
	public function getResponseStatus()
	{
		return $this->response_status;
	}

	/**
	 *  Get Text Report
	 *
	 *  Returns a report of the IPN transaction in plain text format. This is
	 *  useful in emails to order processors and system administrators. Override
	 *  this method in your own class to customize the report.
	 *
	 *  @return string
	 */
	public function getTextReport()
	{
		$r = '';

		// date and POST url
		for ($i=0; $i<80; $i++) { $r .= '-'; }
		$r .= "\n[".date('m/d/Y g:i A').'] - '.$this->getPostUri();
		if ($this->use_curl)
			$r .= " (curl)\n";
		else
			$r .= " (fsockopen)\n";

		// HTTP Response
		for ($i=0; $i<80; $i++) { $r .= '-'; }
		$r .= "\n{$this->getResponse()}\n";

		// POST vars
		for ($i=0; $i<80; $i++) { $r .= '-'; }
		$r .= "\n";

		if (!is_array($this->postData))
			$r .= print_r($this->postData, true);
		else
			foreach ($this->postData as $key => $value)
				$r .= str_pad($key, 25)."$value\n";

		$r .= "\n\n";

		return $r;
	}

	/**
	 * Process an Instant Payment Notification (IPN).
	 * 
	 *   1. Load POST data or data supplied via <code>$custom_data</code>.
	 *   2. Generate and submit a validation request for PayPal.
	 *   3. Parse the response from PayPal.
	 * 
	 * Call this method from your IPN listener script.
	 *
	 * @param mixed $custom_data Custom supplied data in a form of
	 *   a query string or an associative array. If NULL, POST data is used.
	 *   Default is NULL.
	 * @return boolean Returns TRUE if the response came back as "VERIFIED",
	 *   FALSE if the response came back as "INVALID", and throws an exception
	 *   if there was an error.
	 * @throws Exception
	 */
	public function processIpn($custom_data=null)
	{
		// Load POST data (avoid use of $_POST superglobal)
		if ($custom_data === null)
		{
			if ($this->requirePostMethod)
				$this->requirePostMethod();
			$this->rawPostData = file_get_contents('php://input');
			$this->postData = $this->decodeQueryString($this->rawPostData);
		}
		// Load custom data as an associative array
		else if (is_array($custom_data))
		{
			$this->postData = $custom_data;
			$this->rawPostData = $this->encodeQueryString($this->postData);
		}
		// Load custom data as a query string
		else
		{
			$this->rawPostData = $custom_data;
			$this->postData = $this->decodeQueryString($this->rawPostData);
		}

		// Generate request data to be sent to PayPal. Use the original
		// raw data string to avoid potential validation failure due to
		// encoding/decoding differences between client and server.
		$request_qeury = 'cmd=_notify-validate'.'&'.$this->rawPostData;

		// Submit request to PayPal and get response.
		if ($this->use_curl)
			$response = $this->curlPost($request_qeury);
		else
			$response = $this->fsockPost($request_qeury);

		// Check response HTTP status code
		if (strpos($response, '200') === false)
			throw new Exception("Invalid response status: " . $response);

		// Split response headers and payload, a better way for strcmp
		$tokens = explode("\r\n\r\n", trim($response));
		$response = trim(end($tokens));
		if (strcmp ($response, "VERIFIED") == 0)
			return true;
		else if (strcmp ($response, "INVALID") == 0)
			return false;
		else
			throw new Exception("Unexpected response from PayPal.");
	}

	/**
	 * Process IPN by calling <code>processIpn()</code> but catch the exception
	 * if error occurs and return it in the <code>$error</code> parameter.
	 *
	 * @param mixed $custom_data
	 * @param string $error Populated with an error message.
	 * @return boolean
	 */
	public function tryProcessIpn($custom_data=null, &$error=null)
	{
		try
		{
			$valid = $this->processIpn($custom_data);
			if (!$valid)
				$error = 'Invalid IPN request.';
			return $valid;
		}
		catch (Exception $e)
		{
			$error = $e->getMessage();
			return false;
		}
	}

	/**
	 *  Require Post Method
	 *
	 *  Throws an exception and sets a HTTP 405 response header if the request
	 *  method was not POST.
	 */
	public function requirePostMethod()
	{
		// Cannot use filter_input(INPUT_SERVER) due to a buggy behaviour of PHP.
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
		if ($method != 'POST')
		{
			header('Allow: POST', true, 405);
			throw new Exception("Invalid HTTP request method.");
		}
	}

	/**
	 * Encode an associative array into a query string.
	 * Optionally, add a question mark (?) at the beginning of the query string.
	 * @param array $data
	 * @param boolean $addQuestionMark
	 * @return string
	 */
	public static function encodeQueryString(array $data, $addQuestionMark=false)
	{
		$query = http_build_query($data);
		if ($addQuestionMark)
			$query = '?'.$query;
		return $query;
	}

	/**
	 * Decode a query string into an associative array with URL-decoded values.
	 * Returned data mymics the structure of <code>$_GET</code> and
	 * <code>$_POST</code> superglobals. Function automatically strips the
	 * question mark (?) at the beginning of the query string.
	 * @param string $query Query string.
	 * @return array
	 */
	public static function decodeQueryString($query)
	{
		// Strip '?' sign at the begining, if it exists
		if (strlen($query) > 0)
			if (substr($query, 0, 1) == '?')
				$query = substr($query, 1);

		// Break down query string into associative array
		$data = array();
		$parts = explode('&', $query);
		foreach ($parts as $keyval)
		{
			$keyval = explode('=', $keyval);
			if (count($keyval) == 2)
			{
				list($key, $value) = $keyval;
				$data[$key] = urldecode($value);
			}
		}
		
		return $data;
	}
}
