<?php

namespace litek\bot\session;

use litek\bot\entity\types\Bot;
use pocketmine\Player;

class Session
{
    /** @var Player */
    private $player;
    /** @var Bot */
    public $bot;

    /**
     * PlayerSession constructor.
     * @param Player $player
     */
    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return Bot
     */
    public function getBot(): Bot
    {
        return $this->bot;
    }

    /**
     * @param Bot $bot
     */
    public function setBot(Bot $bot): void
    {
        $this->bot = $bot;
    }
}