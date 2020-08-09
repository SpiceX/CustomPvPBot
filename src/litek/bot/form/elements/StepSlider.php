<?php

declare(strict_types=1);

namespace litek\bot\form\elements;

class StepSlider extends Dropdown{

	/**
	 * @return string
	 */
	public function getType() : string{
		return "step_slider";
	}

	/**
	 * @return array
	 */
	public function serializeElementData() : array{
		return [
			"steps" => $this->getOptions(),
			"default" => $this->getDefault()
		];
	}
}