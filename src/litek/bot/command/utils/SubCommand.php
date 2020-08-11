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
namespace litek\bot\command\utils;


use litek\bot\CustomPvPBot;
use pocketmine\command\CommandSender;

abstract class SubCommand {

	/** @var string */
	private $name;

	/** @var string */
	private $usage;

	/** @var string[] */
	private $aliases;

	/**
	 * SubCommand constructor.
	 *
	 * @param string $name
	 * @param string|null $usage
	 * @param string[] $aliases
	 */
	public function __construct(string $name, ?string $usage = null, array $aliases = []) {
		$this->name = $name;
		$this->usage = $usage;
		$this->aliases = $aliases;
	}

	/**
	 * @return CustomPvPBot
	 */
	public function getPlugin(): CustomPvPBot {
		return CustomPvPBot::getInstance();
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string|null
	 */
	public function getUsage(): ?string {
		return $this->usage;
	}

	/**
	 * @return string[]
	 */
	public function getAliases(): array {
		return $this->aliases;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 */
	abstract public function execute(CommandSender $sender, string $commandLabel, array $args): void;
}