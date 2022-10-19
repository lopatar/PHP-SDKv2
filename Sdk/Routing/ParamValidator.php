<?php
declare(strict_types=1);

namespace Sdk\Routing;

use Sdk\Routing\Entities\RouteParameter;
use Sdk\Routing\Entities\RouteParameterType;

final class ParamValidator
{
	public function __construct(private readonly RouteParameterType $type, private readonly int|float|null $minLimit, private readonly int|float|null $maxLimit) {}

	/**
	 * Method that validates the {@see RouteParameter} object and a value
	 * @param string $value
	 * @return bool False on failure
	 */
	public function validate(string $value): bool
	{
		switch ($this->type) {
			case RouteParameterType::STRING:
				return $this->validateStringLength($value);
			case RouteParameterType::BOOL:
				return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) !== null;
			case RouteParameterType::INT:
				if (filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) === null) {
					return false;
				}
				return $this->validateNumRange(intval($value));
			case RouteParameterType::FLOAT:
				if (filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) === null) {
					return false;
				}
				return $this->validateNumRange(floatval($value));
		}

		return false;
	}

	private function validateStringLength(string $value): bool
	{
		$strLength = strlen($value);

		if ($this->minLimit !== null && $strLength < $this->minLimit) {
			return false;
		}

		if ($this->maxLimit !== null && $strLength > $this->maxLimit) {
			return false;
		}

		return true;
	}

	private function validateNumRange(int|float $value): bool
	{
		if ($this->minLimit !== null && $value < $this->minLimit) {
			return false;
		}

		if ($this->maxLimit !== null && $value > $this->maxLimit) {
			return false;
		}

		return true;
	}
}