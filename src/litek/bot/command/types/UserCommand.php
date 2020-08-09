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

namespace litek\bot\command\types;

use litek\bot\command\utils\Command;
use litek\bot\CustomPvPBot;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class UserCommand extends Command
{

	/** @var CustomPvPBot */
	private $plugin;

	public function __construct(CustomPvPBot $plugin)
	{
		parent::__construct("bot", "CustomPvPBot user command", "§l§c» §r§7/bot help", ["bot"]);
		$this->plugin = $plugin;
		$this->setPermission('bot.cmd');
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{
		if (!$this->testPermission($sender)) {
			$sender->sendMessage("§l§a»§r §cYou are not allowed to use this command!");
			return;
		}
		if ($sender instanceof Player) {
			$this->plugin->getFormManager()->sendBotPanel($sender);
		}
		return;
	}
}