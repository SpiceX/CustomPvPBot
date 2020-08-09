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

abstract class Command extends \pocketmine\command\Command
{

	/** @var SubCommand[] */
	private $subCommands;

	/**
	 * @return CustomPvPBot
	 */
	public function getPlugin(): CustomPvPBot
	{
		return CustomPvPBot::getInstance();
	}

	/**
	 * @param SubCommand $subCommand
	 */
	public function addSubCommand(SubCommand $subCommand): void
	{
		$this->subCommands[$subCommand->getName()] = $subCommand;
		foreach ($subCommand->getAliases() as $alias) {
			$this->subCommands[$alias] = $subCommand;
		}
	}

	/**
	 * @param string $name
	 *
	 * @return SubCommand|null
	 */
	public function getSubCommand(string $name): ?SubCommand
	{
		return $this->subCommands[$name] ?? null;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 */
	abstract public function execute(CommandSender $sender, string $commandLabel, array $args): void;
}