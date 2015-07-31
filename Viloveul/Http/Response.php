<?php namespace Viloveul\Http;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Http
 */

use Viloveul\Core\Configure;

class Response {

	protected $output;

	protected $contentType = 'text/html';

	protected $headers = array();

	public function header($header, $overwrite = true) {
		$this->headers[] = array($header, $overwrite);
		return $this;
	}

	/**
	 * setContentType
	 * 
	 * @access	public
	 * @param	String content_type
	 */

	public function setContentType($contentType) {
		$this->contentType = $contentType;
		return $this;
	}

	/**
	 * getContentType
	 * 
	 * @access	public
	 * @return	String content_type
	 */

	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * setOutput
	 * 
	 * @access	public
	 * @param	String data
	 * @param	Boolean
	 */

	public function setOutput($data, $apppend = false) {
		$output = (string) $data;

		$this->output = (true === $apppend) ?
			($this->output.$output) :
				$output;

		return $this;
	}

	/**
	 * getOutput
	 * 
	 * @access	public
	 * @return	String output
	 */

	public function getOutput() {
		return $this->output;
	}

	/**
	 * send
	 * 
	 * @access	public
	 */

	public function send($data = null) {
		is_null($data) or $this->setOutput($data, true);

		if ( ! headers_sent() ) {

			$headers = array_map(
				'unserialize',
				array_unique(
					array_map('serialize', $this->headers)
				)
			);

			foreach ( $headers as $header ) {
				header($header[0], $header[1]);
			}

			@header('Content-Type: ' . $this->contentType, true);
		}

		$output = $this->getOutput();

		$this->clear();

		print $output;
	}

	/**
	 * clear
	 * 
	 * @access	public
	 */

	public function clear() {
		$this->output = '';
		$this->contentType = 'text/html';
		$this->headers = array();
	}

	/**
	 * redirect
	 * 
	 * @access	public
	 * @param	String dynamic/static url
	 * @return	String fixed url
	 */

	public static function redirect($target) {
		$url = !preg_match('#^(\W)\:\/\/#', $target) ?
			Configure::siteurl($target) :
				$target;

		if ( ! headers_sent() ) {
			header("Location: {$url}");
			exit();
		}

		printf('<script type="text/javascript">window.location.href = "%s";</script>', $url);
	}

}
