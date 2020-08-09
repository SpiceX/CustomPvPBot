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

namespace litek\bot\provider;

use litek\bot\CustomPvPBot;

class YamlDataProvider
{
	/** @var CustomPvPBot $plugin */
	private $plugin;

	/**
	 * YamlDataProvider constructor.
	 * @param CustomPvPBot $plugin
	 */
	public function __construct(CustomPvPBot $plugin)
	{
		$this->plugin = $plugin;
		$this->init();
	}

	public function init()
	{
		if (!is_dir($this->getDataFolder())) {
			@mkdir($this->getDataFolder());
		}
		if (!is_dir($this->getDataFolder() . "skins")) {
			@mkdir($this->getDataFolder() . "skins");
		}
		if (!is_dir($this->getDataFolder() . "templates")) {
			@mkdir($this->getDataFolder() . "templates");
		}
	}

	/**
	 * @return string $dataFolder
	 */
	private function getDataFolder(): string
	{
		return $this->plugin->getDataFolder();
	}
}