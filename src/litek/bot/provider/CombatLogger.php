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
use pocketmine\Player;

class CombatLogger
{
	/** @var CustomPvPBot */
	private $plugin;

	/** @var Player[] */
	private $combatList = [];

	public function __construct(CustomPvPBot $plugin)
	{
		$this->plugin = $plugin;
	}

	public function add(Player $player)
	{
		$this->combatList[$player->getName()] = $player;
	}

	public function get(Player $player)
	{
		if ($this->inCombat($player)) {
			return $this->combatList[$player->getName()];
		}
		return null;
	}

	public function inCombat(Player $player)
	{
		return isset($this->combatList[$player->getName()]);
	}

	public function remove(Player $player)
	{
		if ($this->inCombat($player)) {
			unset($this->combatList[$player->getName()]);
		}
	}
}