<?php

namespace litek\bot\task;

use litek\bot\CustomPvPBot;
use litek\bot\entity\types\Bot;
use litek\bot\util\InteractionHelper;
use pocketmine\entity\Living;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class BotLookingTask extends Task{

	/**
	 * @var CustomPvPBot
	 */
	private $plugin;

	private $isInteractiveButtonCorrectionSet;

	public function __construct(CustomPvPBot $plugin){
		$this->plugin = $plugin;
		$this->isInteractiveButtonCorrectionSet = true;
	}

	/**
	 * Called when the task is executed
	 *
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick){
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			if($player->isClosed() or !$player->isOnline() or !$player->spawned){
				continue;
			}
			$entity = InteractionHelper::getEntityPlayerLookingAt($player, 10, $this->isInteractiveButtonCorrectionSet);
			if($entity !== null and $entity instanceof Bot and $entity->getTargetPlayer() instanceof Player){
				$entity->playerLooksAt($player);
			}
		}
	}


}