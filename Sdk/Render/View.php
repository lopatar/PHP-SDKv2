<?php
declare(strict_types=1);

namespace Sdk\Render;

use Sdk\Render\Exceptions\ViewFileNotFound;

final class View
{
	public readonly string $filePath;

	/**
	 * @throws ViewFileNotFound
	 */
	public function __construct(string $fileName)
	{
		$path = $this->buildViewPath($fileName);

		if (!file_exists($path)) {
			throw new ViewFileNotFound($path);
		}

		$this->filePath = $path;
	}

	private function buildViewPath(string $fileName): string
	{
		return __DIR__ . "/../../App/Views/$fileName";
	}

	/**
	 * This method allows us to inject data into the View file, can be accessed using "$this->$name" or {@see View::getProperty() $this->getProperty()}
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setProperty(string $name, mixed $value): self
	{
		$this->$name = $value;
		return $this;
	}

	public function getProperty(string $name): mixed
	{
		return $this->$name ?? "$name not found";
	}

	public function render(): void
	{
		require_once $this->filePath;
	}
}