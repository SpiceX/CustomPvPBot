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

namespace litek\bot\command;

use litek\bot\command\types\UserCommand;
use litek\bot\command\utils\Command;
use litek\bot\CustomPvPBot;

class CommandManager
{

	/** @var CustomPvPBot */
	private $plugin;

	/**
	 * CommandManager constructor.
	 *
	 * @param CustomPvPBot $plugin
	 */
	public function __construct(CustomPvPBot $plugin)
	{
		$this->plugin = $plugin;
		$this->registerCommand(new UserCommand($plugin));
	}

	/**
	 * @param Command $command
	 */
	public function registerCommand(Command $command): void
	{
		$commandMap = $this->plugin->getServer()->getCommandMap();
		$existingCommand = $commandMap->getCommand($command->getName());
		if ($existingCommand !== null) {
			$commandMap->unregister($existingCommand);
		}
		$commandMap->register($command->getName(), $command);
	}

}