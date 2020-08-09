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

namespace litek\bot\entity;

use litek\bot\CustomPvPBot;
use litek\bot\entity\types\Bot;
use pocketmine\entity\Entity;
use pocketmine\Player;

class EntityManager
{
	/** @var CustomPvPBot */
	private $plugin;

	public function __construct(CustomPvPBot $plugin)
	{
		$this->plugin = $plugin;
		$this->registerEntities();
	}

	private function registerEntities(){
		Entity::registerEntity(Bot::class, true);
	}

	public function prepareBot(Player $player): Bot
	{
		$nbt = Entity::createBaseNBT($player->getLevel()->getSafeSpawn($player->asVector3()->subtract(10, 0, 10)));
		$nbt->setTag($player->namedtag->getTag("Skin"));
		$bot = new Bot($player->getLevel(), $nbt, $player->getName());
		$bot->setNameTagAlwaysVisible(true);
		$bot->setNameTagVisible(true);
		$bot->setCanSaveWithChunk(false);
		$this->plugin->getCombatLogger()->add($player);
		return $bot;
	}
}