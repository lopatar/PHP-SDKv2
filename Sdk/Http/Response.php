<?php
declare(strict_types=1);

namespace Sdk\Http;

use Sdk\Http\Entities\StatusCode;
use Sdk\Render\Exceptions\ViewFileNotFound;
use Sdk\Render\View;

final class Response
{
	private StatusCode $statusCode = StatusCode::OK;
	private ?View $view = null;
	private string $text = '';

	public function getStatusCode(): StatusCode
	{
		return $this->statusCode;
	}

	public function setStatusCode(StatusCode $statusCode): self
	{
		$this->statusCode = $statusCode;
		return $this;
	}

	/**
	 * @see https://php.net/header
	 */
	public function addHeader(string $name, string $value): self
	{
		header("$name: $value");
		return $this;
	}

	/**
	 * @see https://php.net/header_remove
	 */
	public function removeHeader(string $name): self
	{
		header_remove($name);
		return $this;
	}

	public function write(string $text): self
	{
		$this->text .= $text;
		return $this;
	}

	/**
	 * @param string $text
	 * @param bool $useHtmlBr Whether to use the br html tag or \n for new line
	 * @return $this
	 */
	public function writeLine(string $text, bool $useHtmlBr = false): self
	{
		$text .= ($useHtmlBr) ? "<br/>" : "\n";
		$this->text .= $text;
		return $this;
	}

	public function getContent(): string
	{
		return $this->text;
	}

	public function wipeContent(): self
	{
		$this->text = '';
		return $this;
	}

	/**
	 * This method create a {@see View} object and sets it using the {@see Response::setView()} method
	 * @param string $fileName View file name
	 * @return View|null
	 */
	public function createView(string $fileName): ?View
	{
		try {
			$view = new View($fileName);
			$this->setView($view);
			return $view;
		} catch (ViewFileNotFound) {
			return null;
		}
	}

	public function getView(): ?View
	{
		return $this->view;
	}

	public function setView(View $view): self
	{
		$this->view = $view;
		return $this;
	}

	/**
	 * Sends the response with headers, text and the status code. Application ends
	 */
	public function send(): never
	{
		http_response_code($this->statusCode->value);

		echo $this->text;

		$this->view?->render();

		die();
	}
}