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
use litek\bot\math\Vector3X;
use pocketmine\command\CommandSender;
use pocketmine\entity\InvalidSkinException;
use pocketmine\Player;
use pocketmine\utils\Config;

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
            if (isset($args[0]) && $args[0] === 'spawn') {
                if (isset($args[1])) {
                    $template = $this->getPlugin()->getTemplateManager()->getTemplate($args[1]);
                    try {
                        $config = new Config(CustomPvPBot::getInstance()->getDataFolder() . 'templates' . "/{$args[1]}.json", Config::JSON);
                        $position = $config->get('default_position');
                        if ($template !== null && $position !== false) {
                            $bot = $this->getPlugin()->getEntityManager()->prepareBot($sender, Vector3X::toObject($position));
                            $bot->teleport(Vector3X::toObject($position));
                            $command = $template->getCommand();
                            $bot->setName($template->getName());
                            $bot->setMaxHealth((int)$template->getHealth());
                            $bot->setHealth($template->getHealth());
                            $bot->setAttackDamage($template->getDamage());
                            $bot->setCommand($command);
                            $bot->setSkin($template->getSkin());
                            $bot->setRespawnTime($template->getRespawnTime());
                            $bot->sendSkin();
                            $bot->spawnToAll();
                            $sender->sendMessage("§a> Bot spawned successfully!");
                            return;
                        }

                        $sender->sendMessage("§c> Bot template does not exists, please create template first!");
                    } catch (InvalidSkinException $exception) {
                        return;
                    }
                    return;
                }
            }
            $this->plugin->getFormManager()->sendBotPanel($sender);
        }
    }
}