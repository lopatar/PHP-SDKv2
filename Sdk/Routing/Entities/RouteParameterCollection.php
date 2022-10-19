<?php
declare(strict_types=1);

namespace Sdk\Routing\Entities;

use ArrayIterator;
use InvalidArgumentException;

/**
 * Object that holds {@see RouteParameter} values for a {@see Route} object
 * @uses ArrayIterator
 * @uses \Sdk\Routing\Entities\RouteParameter
 * @uses InvalidArgumentException
 */
final class RouteParameterCollection extends ArrayIterator
{
	/**
	 * @param RouteParameter $value
	 * @throws InvalidArgumentException When $value not an instance of {@see RouteParameter}
	 */
	public function append(mixed $value): void
	{
		if (!($value instanceof RouteParameter)) {
			throw new InvalidArgumentException('Value must be an instance of Sdk\Routing\Entities\RouteParameter');
		}

		parent::append($value);
	}

	public function current(): RouteParameter
	{
		return parent::current();
	}

	public function offsetGet(mixed $key): RouteParameter|null
	{
		return parent::offsetGet($key);
	}

	/**
	 * @param mixed $key
	 * @param RouteParameter $value
	 * @throws InvalidArgumentException When $value not an instance of {@see RouteParameter}
	 */
	public function offsetSet(mixed $key, mixed $value): void
	{
		if (!($value instanceof RouteParameter)) {
			throw new InvalidArgumentException('Value must be an instance of Sdk\Routing\Entities\RouteParameter');
		}

		parent::offsetSet($key, $value);
	}

	/**
	 *
	 * @param int $index
	 * @return bool
	 */
	public function isParameterAtIndex(int $index): bool
	{
		return isset($this[$index]);
	}

	/**
	 * Method that updates the {@see RouteParameter}
	 *
	 * @param string[] $requestPathParts
	 * @return bool False on failure (when extracted parameter is empty)
	 */
	public function updateValues(array $requestPathParts): bool
	{
		foreach ($this->getParamIndices() as $i) {
			$paramValue = $requestPathParts[$i];

			if (empty($paramValue)) {
				return false;
			}

			if (!$this[$i]->setValue($requestPathParts[$i])) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @returns int[]
	 */
	private function getParamIndices(): array
	{
		return array_keys($this->getArrayCopy());
	}

	/**
	 * @return RouteParameter[]
	 */
	public function getArrayCopy(): array
	{
		return parent::getArrayCopy();
	}

	/**
	 * @return array
	 * Returns an associative array of parameters (key = {@see RouteParameter::$name}, value = parameter value)
	 */
	public function getAssoc(): array
	{
		$parameters = [];

		foreach ($this as $parameter) {
			$parameters[$parameter->name] = $parameter->getValue();
		}

		return $parameters;
	}

	public function getParamByName(string $name): ?RouteParameter
	{
		foreach ($this as $parameter) {
			if ($parameter->name === $name) {
				return $parameter;
			}
		}

		return null;
	}
}