<?php


namespace litek\bot\session;


use pocketmine\Player;

class SessionFactory
{
    /** @var Session[] */
    private static $sessions = [];

    /** @param Player $player */
    public static function createSession(Player $player): void
    {
        self::$sessions[$player->getName()] = new Session($player);
    }

    /**
     * @param Player $player
     * @return Session|null
     */
    public static function getSession(Player $player): ?Session
    {
        return self::$sessions[$player->getName()] ?? null;
    }

    /**
     * @param Player $player
     */
    public static function removeSession(Player $player): void
    {
        if (isset(self::$sessions[$player->getName()])) {
            unset(self::$sessions[$player->getName()]);
        }
    }

}