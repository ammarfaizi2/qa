<?php

namespace Facebook\Browser;

use Curlfile;
use Facebook\Facebook;
use Facebook\Browser\OutputFixer;
use Facebook\Browser\ActionUrlGenerator;
use Facebook\Browser\HeaderUrlGenerator;
use Facebook\Contracts\Browser as BrowserContract;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @package \Facebook\Browser
 */
class Browser implements BrowserContract
{
	/**
	 * @var \Facebook\Facebook
	 */
	public $fb;

	/**
	 * @var string
	 */
	public $url;

	/**
	 * @var mixed
	 */
	public $data;

	/**
	 * @var string
	 */
	public $baseUrl;

	/**
	 * @var string
	 */
	public $method;

	/**
	 * @var callable
	 */
	public $call;

	/**
	 * @var bool
	 */
	public $asRouter = false;

	/**
	 * @var array
	 */
	public $optContext = [];

	/**
	 * @var \Facebook\Browser\OutputFixer
	 */
	public $bodyResponse;

	/**
	 * @var array
	 */
	public $headerResponse = [];

	/**
	 * Constructor.
	 *
	 * @param \Facebook\Facebook $fb
	 */
	public function __construct(Facebook $fb, $baseUrl = "https://m.facebook.com")
	{
		$this->fb = $fb;
		$this->baseUrl = $baseUrl;
		$this->prohobiter = function () {
			return false;
		};
	}

	/**
	 * Init browser.
	 * @return $this
	 */
	public function init($asRouter = false)
	{
		$this->asRouter = $asRouter;
		$this->generateUrl();
		$this->buildMethodContext();
		$this->buildOptContext();
		return $this;
	}

	/**
	 * Run browser.
	 */
	public function run()
	{
		$this->sendRequest();
		$this->renderHeaderResponse();
		$this->renderBodyResponse();
	}

	/**
	 * Generate action url.
	 */
	private function generateUrl()
	{
		$url = new ActionUrlGenerator($this->asRouter, $this->baseUrl);
		$url->generate();
		$this->url = $url->getUrl();
	}

	/**
	 * Build method context.
	 */
	private function buildMethodContext()
	{
		if ($_SERVER['REQUEST_METHOD'] === "POST") {
			$this->buildPostContext();
		} else {
			$this->buildGetContext();
		}
	}

	/**
	 * Build post context.
	 */
	private function buildPostContext()
	{
		if (count($_FILES)) {
			$this->data = $_POST;
			foreach ($_FILES as $key => $value) {
				if (! empty($value['tmp_name'])) {
					$this->data[$key] = new Curlfile(realpath($value['tmp_name']));
				}
			}
		} else {
			$this->data = file_get_contents("php://input");
		}
		$this->method = "POST";
	}

	/**
	 * Build get context.
	 */
	private function buildGetContext()
	{
		$this->method = "GET";
	}

	/**
	 * Build opt context
	 */
	private function buildOptContext()
	{
		if ($this->method === "POST") {
			$this->optContext = [
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $this->data
			];
		} else {
			$this->optContext = [];
		}
		$this->optContext[CURLOPT_FOLLOWLOCATION] = false;
		$this->optContext[CURLOPT_HEADER] = true;
		$this->optContext[CURLOPT_TIMEOUT] = 300;
		$this->optContext[CURLOPT_CONNECTTIMEOUT] = 300;

		if (isset($_SERVER['HTTP_REFERER'])) {
			$this->optContext[CURLOPT_REFERER] = preg_replace("/^http.+\/\/.+\//Usi", "{$this->baseUrl}/", $this->url);
		}
	}

	/**
	 * Send request.
	 */
	private function sendRequest()
	{
		$prohobiter = $this->prohobiter;
		if ($prohobiter($this)) {
			http_response_code(403);
			die("403 Forbidden Action");
		}
		$ch = curl_init($this->url);
		curl_setopt_array($ch, $this->fb->genOpt($this->optContext, true));
		$out = curl_exec($ch);
		$info = curl_getinfo($ch);
		if ($ern = curl_errno($ch)) {
			http_response_code(500);
			exit(("Error ({$ern}): ".curl_error($ch)));
		}
		curl_close($ch);
		$this->buildResponse($out, $info);
	}

	/**
	 * Build response.
	 */
	private function buildResponse($out, $info)
	{
		$headers = explode("\n", substr($out, 0, $info['header_size']));
		$this->bodyResponse = new OutputFixer(substr($out, $info['header_size']), $this->asRouter, $this->baseUrl);
		array_walk($headers, function ($header) {
			$header = explode(":", $header, 2);
			if (count($header) > 1) {
				$header[0] = strtolower($header[0]);
				$header[1] = trim($header[1]);
				if (in_array($header[0], ["location"])) {
					if ($header[0] === "location") {
						$url = new HeaderUrlGenerator($this->asRouter, $this->baseUrl, $header[1]);
						$url->generate();
						$this->headerResponse[$header[0]] = $url->getUrl();
					}
				}
			} else {
				$this->headerResponse[$header[0]] = "";
			}
		});
	}

	/**
	 * Render header response.
	 */
	private function renderHeaderResponse()
	{
		foreach ($this->headerResponse as $key => $val) {
			if (empty($val)) {
				header($key);
			} else {
				header("{$key}: {$val}");
			}
		}
	}

	/**
	 * Render body response.
	 */
	private function renderBodyResponse()
	{
		$this->bodyResponse = $this->bodyResponse->__toString();
		$len = strlen($this->bodyResponse);
		for ($i=0; $i < $len; $i++) { 
			echo $this->bodyResponse[$i];
			flush();
		}
	}

	/**
	 * @param callable $call
	 * @return $this
	 */
	public function prohibitAction(callable $call)
	{
		$this->prohobiter = $call;
		return $this;
	}
}
