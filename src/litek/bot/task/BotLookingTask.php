<?php

namespace litek\bot\task;

use litek\bot\CustomPvPBot;
use litek\bot\entity\types\Bot;
use litek\bot\session\SessionFactory;
use litek\bot\util\InteractionHelper;
use pocketmine\entity\Living;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class BotLookingTask extends Task
{

    /**
     * @var CustomPvPBot
     */
    private $plugin;

    private $isInteractiveButtonCorrectionSet;

    public function __construct(CustomPvPBot $plugin)
    {
        $this->plugin = $plugin;
        $this->isInteractiveButtonCorrectionSet = true;
    }

    /**
     * Called when the task is executed
     *
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $session = SessionFactory::getSession($player);
            if ($player->isClosed() || !$player->isOnline() | !$player->spawned) {
                continue;
            }
            $entity = InteractionHelper::getEntityPlayerLookingAt($player, 10, $this->isInteractiveButtonCorrectionSet);
            if ($entity instanceof Bot && $entity->getTargetPlayer() instanceof Player && $entity->getTargetPlayer()->getGamemode() === Player::SURVIVAL) {
                $entity->playerLooksAt($player);
                $session->setBot($entity);
            }
        }
    }


}