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


namespace litek\bot;

use litek\bot\entity\types\Bot;
use litek\bot\task\BotRespawnTask;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\Player;

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

	public function onDeath(PlayerDeathEvent $event): void
	{
		$player = $event->getPlayer();
		if ($this->plugin->getCombatLogger()->inCombat($player)) {
			$event->setDrops([]);
			$event->setDeathMessage('');
			$e = $event->getPlayer()->getLastDamageCause();
			if ($e instanceof EntityDamageByEntityEvent) {
				$damager = $e->getDamager();
				if ($damager instanceof Bot) {
					$this->plugin->getServer()->broadcastMessage("§l§a»§r " . $player->getNameTag() . " §7lose a battle against bot §a" . $damager->name);
				}
				$bot = $e->getEntity();
				if ($damager instanceof Player && $bot instanceof Bot) {
					if (($command = $bot->getCommand()) !== null) {
						$hasCommandFormat = (bool)strpos($command, '{player}');
						if ($hasCommandFormat) {
							$this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace('{player}', $damager->getName(), $command));
						}
					}
				}
			}
		}
	}

	public function onEntityDamageByEntity(EntityDamageByEntityEvent $event)
	{
		$damager = $event->getDamager();
		$entity = $event->getEntity();
		if ($damager instanceof Bot && $entity instanceof Player) {
			if ($this->plugin->getCombatLogger()->inCombat($entity)) {
				$event->setBaseDamage($damager->getAttackDamage());
			}
		}
		if ($damager instanceof Player && $entity instanceof Bot) {
			if ($event->getFinalDamage() > $entity->getHealth()) {
				CustomPvPBot::getInstance()->getScheduler()->scheduleRepeatingTask(new BotRespawnTask($entity), 20);
				if (($command = $entity->getCommand()) !== null) {
					$hasCommandFormat = (bool)strpos($command, '{player}');
					if ($hasCommandFormat) {
						$this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace('{player}', $damager->getName(), $command));
					}
				}
			}
		}
	}

	public function onDamage(EntityDamageEvent $event): void
	{
		$player = $event->getEntity();
		if ($player instanceof Player) {
			if ($this->plugin->getCombatLogger()->inCombat($player)) {
				if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
					$event->setCancelled();
				}
			}
		}
	}
	
	public function onEntityDespawn(EntityDeathEvent $event){
		$entity = $event->getEntity();
		if ($entity instanceof Bot){

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