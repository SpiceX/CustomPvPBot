<?php /** @noinspection NullPointerExceptionInspection */
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


namespace litek\bot;

use litek\bot\entity\types\Bot;
use litek\bot\session\SessionFactory;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\Server;

class BotListener implements Listener
{

    /**
     * @var CustomPvPBot
     */
    private $plugin;

    public function __construct(CustomPvPBot $plugin)
    {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event): void
    {
        SessionFactory::createSession($event->getPlayer());
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $session = SessionFactory::getSession($event->getPlayer());
        if ($session->bot instanceof Bot) {
            $session->bot->close();
        }
        SessionFactory::removeSession($event->getPlayer());
    }

    public function onChangeLevel(EntityLevelChangeEvent $event): void
    {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            $session = SessionFactory::getSession($player);
            if (($session->bot instanceof Bot) && $event->getTarget() !== $session->bot->getLevel()) {
                $session->bot->close();
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player->getLevel() === null){
            return;
        }
        if ($this->plugin->getCombatLogger()->inCombat($player)) {
            $event->setDrops([]);
            $event->setDeathMessage('');
            $e = $event->getPlayer()->getLastDamageCause();
            if ($e instanceof EntityDamageByEntityEvent) {
                $damager = $e->getDamager();
                if ($damager instanceof Bot) {
                    $this->plugin->getServer()->broadcastMessage("§l§a»§r " . $player->getNameTag() . " §7lose a battle against bot §a" . $damager->name);
                    return;
                }
                $bot = $e->getEntity();
                if ($damager instanceof Player && $bot instanceof Bot) {
                    if (($command = $bot->getCommand()) !== null) {
                        $hasCommandFormat = (bool)strpos($command, '{player}');
                        if ($hasCommandFormat) {
                            $damager->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
                            $damager->sendMessage("§l§a»§r§7 You have won the battle!");
                            $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace('{player}', $damager->getName(), $command));
                        }
                    }
                }
            }
        }
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $damager = $event->getDamager();
        $entity = $event->getEntity();
        if ($damager->getLevel() === null || $entity->getLevel() === null){
            return;
        }
        if ($damager instanceof Bot && $entity instanceof Player) {
            if ($this->plugin->getCombatLogger()->inCombat($entity)) {
                $event->setBaseDamage($damager->getAttackDamage());
            }
        }
        if ($damager instanceof Player && $entity instanceof Bot) {
            if ($event->getFinalDamage() > $entity->getHealth()) {
                if (($command = $entity->getCommand()) !== null) {
                    $hasCommandFormat = (bool)strpos($command, '{player}');
                    if ($hasCommandFormat) {
                        $damager->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
                        $damager->sendMessage("§l§a»§r§7 You have won the battle!");
                        $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace('{player}', $damager->getName(), $command));
                    }
                }
            }
        }
    }

    public function onDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();
        if ($player->getLevel() === null){
            return;
        }
        if ($player instanceof Player) {
            if ($this->plugin->getCombatLogger()->inCombat($player)) {
                if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                    $event->setCancelled();
                }
            }
        }
    }


    public function onExhaust(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            if ($this->plugin->getCombatLogger()->inCombat($player)) {
                $event->setCancelled();
            }
        }
    }
}