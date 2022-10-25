<?php
declare(strict_types=1);

namespace Sdk\Routing;

use Sdk\Routing\Entities\RouteParameter;
use Sdk\Routing\Entities\RouteParameterType;

final class ParamValidator
{
	public function __construct(private readonly RouteParameter $parameter) {}

	/**
	 * Method that validates the {@see RouteParameter} object and a value
	 * @param string $value
	 * @return bool False on failure
	 */
	public function validate(string $value): bool
	{
		switch ($this->parameter->getType()) {
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

		if ($this->parameter->getMinLimit() !== null && $strLength < $this->parameter->getMinLimit()) {
			return false;
		}

		if ($this->parameter->getMaxLimit() !== null && $strLength > $this->parameter->getMaxLimit()) {
			return false;
		}

		return true;
	}

	private function validateNumRange(int|float $value): bool
	{
		if ($this->parameter->getMinLimit() !== null && $value < $this->parameter->getMinLimit()) {
			return false;
		}

		if ($this->parameter->getMaxLimit() !== null && $value > $this->parameter->getMaxLimit()) {
			return false;
		}

		return true;
	}
}