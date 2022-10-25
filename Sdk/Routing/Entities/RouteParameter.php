<?php
declare(strict_types=1);

namespace Sdk\Routing\Entities;

use PhpParser\Node\Param;
use Sdk\Routing\ParamValidator;

final class RouteParameter
{
	private mixed $value = '';

	/**
	 * @var RouteParameterType Data type that the parameter should have, used for validating request parameters
	 */
	private RouteParameterType $type = RouteParameterType::STRING;

	/**
	 * @var int|float|null Minimum limit the parameter should have (value for numbers, length for strings)
	 */
	private int|float|null $minLimit = null;

	/**
	 * @var int|float|null Maximum limit the parameter should have (value for numbers, length for strings)
	 */
	private int|float|null $maxLimit = null;

	private bool $shouldEscape = false;

	public function __construct(public readonly string $name, public readonly int $formatIndex, private readonly Route $route) {}

	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * DO NOT USE, value will be overwritten on {@see App::run()}
	 * @param string $value
	 * @return bool Whether the value followed all constraints defined by the {@see RouteParameter}
	 * @uses \Sdk\Routing\Entities\RouteParameter::validateValue()
	 * @internal
	 */
	public function setValue(string $value): bool
	{
		if (!$this->validateValue($value)) {
			return false;
		}

		$this->value = ($this->shouldEscape) ? htmlentities($value) : $value;

		return true;
	}

	/**
	 * Returns whether the value followed all constraints defined by the {@see RouteParameter}
	 * @param string $value
	 * @return bool
	 * @uses \Sdk\Routing\ParamValidator
	 */
	public function validateValue(string $value): bool
	{
		return (new ParamValidator($this))->validate($value);
	}

	/**
	 * Sets the parameter type, to validate for, STRING is default
	 * @param RouteParameterType $type
	 * @return $this
	 */
	public function setType(RouteParameterType $type): self
	{
		$this->type = $type;
		return $this;
	}

	public function getType(): RouteParameterType
	{
		return $this->type;
	}

	/**
	 * @param bool $shouldEscape Whether the {@see RouteParameter::$value} should be escaped using {@see htmlentities()}
	 * @return $this
	 */
	public function setShouldEscape(bool $shouldEscape): self
	{
		$this->shouldEscape = $shouldEscape;
		return $this;
	}

	/**
	 * @param int|float|null $minLimit {@see RouteParameter::setMinLimit()}
	 * @param int|float|null $maxLimit {@see RouteParameter::setMaxLimit()}
	 * @return $this
	 */
	public function setLimit(int|float|null $minLimit, int|float|null $maxLimit): self
	{
		if ($minLimit !== null) {
			$this->setMinLimit($minLimit);
		}

		if ($maxLimit !== null) {
			$this->setMaxLimit($maxLimit);
		}

		return $this;
	}

	/**
	 * Function that allows us to set minimum limit for the {@see RouteParameter::$value}
	 *
	 * If the {@see RouteParameter::$type} is STRING & {@see RouteParameter::$minLimit} is set lower than 1, then it is clamped to 1.
	 * If {@see RouteParameter::$maxLimit} is set & {@see RouteParameter::$minLimit} is higher than it, min limit is clamped to value of {@see RouteParameter::$maxLimit}
	 * @param int|float $minLimit Minimum limit value, value for numbers, length for strings
	 * @return $this
	 */
	public function setMinLimit(int|float $minLimit): self
	{
		if ($this->type === RouteParameterType::STRING && $minLimit < 1) {
			$minLimit = 1;
		}

		if ($this->maxLimit !== null && $minLimit > $this->maxLimit) {
			$minLimit = $this->maxLimit;
		}

		$this->minLimit = $minLimit;
		return $this;
	}

	public function getMinLimit(): float|int|null
	{
		return $this->minLimit;
	}

	/**
	 * Function that allows us to set maximum limit for the {@see RouteParameter::$value}
	 *
	 * If the {@see RouteParameter::$type} is STRING & {@see RouteParameter::$maxLimit} is set lower than 1, then it is clamped to 1.
	 * If {@see RouteParameter::$minLimit} is set & {@see RouteParameter::$maxLimit} is lower than it, min limit is clamped to value of {@see RouteParameter::$maxLimit}
	 * @param int|float $maxLimit Maximum limit value, value for numbers, length for strings
	 * @return $this
	 */
	public function setMaxLimit(int|float $maxLimit): self
	{
		if ($this->type === RouteParameterType::STRING && $maxLimit < 1) {
			$maxLimit = 1;
		}

		if ($this->minLimit !== null && $maxLimit < $this->minLimit) {
			$maxLimit = $this->minLimit;
		}

		$this->maxLimit = $maxLimit;
		return $this;
	}

	public function getMaxLimit(): int|float|null
	{
		return $this->maxLimit;
	}

	public function getRoute(): Route
	{
		return $this->route;
	}
}