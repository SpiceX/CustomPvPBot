<?php

namespace litek\bot\task;

use Exception;
use litek\bot\CustomPvPBot;
use litek\bot\entity\types\Bot;
use litek\bot\math\Vector3X;
use pocketmine\entity\InvalidSkinException;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

class BotRespawnTask extends Task
{

    /** @var Bot */
    private $bot;

    /** @var int */
    private $seconds;

    /**
     * BotRespawnTask constructor.
     * @param Bot $bot
     */
    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
        $this->seconds = $bot->getRespawnTime();
    }

    public function onRun(int $currentTick): void
    {
        var_dump("TAREA EJECUTANDOSE");
        if ($this->seconds === null || $this->bot === null) {
            $this->getPlugin()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        if ($this->seconds === 0) {
            $template = $this->getPlugin()->getTemplateManager()->getTemplate($this->bot->name);
            if ($template === null) {
                $this->getPlugin()->getScheduler()->cancelTask($this->getTaskId());
                return;
            }
            try {
                $abusedPlayer = $this->getAbusingPlayer();
                $config = new Config(CustomPvPBot::getInstance()->getDataFolder() . 'templates' . "/{$this->bot->name}.json", Config::JSON);
                $position = $config->get('default_position');
                if ($abusedPlayer !== null && $position !== false) {
                    $bot = $this->getPlugin()->getEntityManager()->prepareBot($abusedPlayer, Vector3X::toObject($position));
                    $bot->teleport($this->bot->getDefaultPosition());
                    $command = $template->getCommand();
                    $bot->setName($template->getName());
                    $bot->setMaxHealth($template->getHealth());
                    $bot->setHealth($template->getHealth());
                    $bot->setAttackDamage($template->getDamage());
                    $bot->setCommand($command);
                    $bot->setSkin($template->getSkin());
                    $bot->setRespawnTime($template->getRespawnTime());
                    $bot->sendSkin();
                    $bot->spawnToAll();
                    $this->getPlugin()->getScheduler()->cancelTask($this->getTaskId());
                    return;
                }
            } catch (InvalidSkinException $exception) {
                $this->getPlugin()->getScheduler()->cancelTask($this->getTaskId());
                return;
            } catch (Exception $exception) {
                $this->getPlugin()->getScheduler()->cancelTask($this->getTaskId());
                return;
            }
            $this->getPlugin()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        $this->seconds--;
    }

    /**
     * @return CustomPvPBot
     */
    public function getPlugin(): CustomPvPBot
    {
        return CustomPvPBot::getInstance();
    }

    /**
     * @return Player|null
     */
    private function getAbusingPlayer(): ?Player
    {
        $players = $this->getPlugin()->getServer()->getOnlinePlayers();
        if (count($players) > 0) {
            return $players[array_rand($players)];
        }
        return null;
    }

}