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

namespace litek\bot\provider\elements;


use litek\bot\CustomPvPBot;
use litek\bot\math\Vector3X;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\entity\Skin;
use pocketmine\utils\Config;

class Template
{
	/** @var string */
	private $name;

	/** @var float */
	private $health;

	/** @var float */
	private $damage;

	/** @var Skin */
	private $skin;

	/** @var string */
	private $command;

	/** @var int */
	private $respawnTime;

	/** @var Position */
	private $defaultPosition;

	public function __construct(string $name, float $health, float $damage, Skin $skin, string $command, int $respawnTime, Position $defaultPosition)
	{
		$this->name = $name;
		$this->health = $health;
		$this->damage = $damage;
		$this->skin = $skin;
		$this->command = $command;
		$this->respawnTime = $respawnTime;
		$this->defaultPosition = $defaultPosition;
	}

	public function save(): void
    {
		$config = new Config($this->getPlugin()->getDataFolder() . 'templates' . "/{$this->name}.json", Config::JSON);
		$config->set('name', $this->name);
		$config->set('health', $this->health);
		$config->set('damage', $this->damage);
		$config->set('skin', $this->skin->getSkinId());
		$config->set('command', $this->command);
		$config->set('respawn_time', $this->respawnTime);
		$config->set('default_position', Vector3X::toString($this->defaultPosition));
		$config->save();
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return float
	 */
	public function getHealth(): float
	{
		return $this->health;
	}

	/**
	 * @return float
	 */
	public function getDamage(): float
	{
		return $this->damage;
	}

	/**
	 * @return Skin
	 */
	public function getSkin(): Skin
	{
		return $this->skin;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @param float $health
	 */
	public function setHealth(float $health): void
	{
		$this->health = $health;
	}

	/**
	 * @param float $damage
	 */
	public function setDamage(float $damage): void
	{
		$this->damage = $damage;
	}

	/**
	 * @param Skin $skin
	 */
	public function setSkin(Skin $skin): void
	{
		$this->skin = $skin;
	}

	/**
	 * @return CustomPvPBot
	 */
	public function getPlugin(): CustomPvPBot
	{
		return CustomPvPBot::getInstance();
	}

	/**
	 * @return string
	 */
	public function getCommand(): string
	{
		return $this->command;
	}

	/**
	 * @param string $command
	 */
	public function setCommand(string $command): void
	{
		$this->command = $command;
	}

	/**
	 * @return int
	 */
	public function getRespawnTime(): int
	{
		return $this->respawnTime;
	}

	/**
	 * @param int $respawnTime
	 */
	public function setRespawnTime(int $respawnTime): void
	{
		$this->respawnTime = $respawnTime;
	}

	/**
	 * @return Position
	 */
	public function getDefaultPosition(): Position
	{
		return $this->defaultPosition;
	}

	/**
	 * @param Vector3 $defaultPosition
	 */
	public function setDefaultPosition(Vector3 $defaultPosition): void
	{
		$this->defaultPosition = $defaultPosition;
	}
}