<?php
/**
 * Copyright 2018-2020 LiTEK
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);


namespace litek\bot\form\elements;

class Button extends Element{
	/** @var Image|null */
	private $image;
	/** @var string */
	private $type;

	/**
	 * @param string     $text
	 * @param Image|null $image
	 */
	public function __construct(string $text, ?Image $image = null){
		parent::__construct($text);
		$this->image = $image;
	}

	/**
	 * @return string|null
	 */
	public function getType() : ?string{
		return null;
	}

	/**
	 * @return bool
	 */
	public function hasImage() : bool{
		return $this->image !== null;
	}

	/**
	 * @return array
	 */
	public function serializeElementData() : array{
		$data = ["text" => $this->text];
		if($this->hasImage()){
			$data["image"] = $this->image;
		}
		return $data;
	}
}