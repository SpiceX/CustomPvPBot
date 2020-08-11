<?php


namespace litek\bot\math;


use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;

class Vector3X extends Vector3
{
	public static function toString(Position $position): string
    {
		return "{$position->x}:{$position->y}:{$position->z}:{$position->getLevel()->getFolderName()}";
	}

	public static function toObject(string $position): Position
    {
		$splitted_string = explode(':', $position);
		return new Position((float)$splitted_string[0],(float)$splitted_string[1],(float)$splitted_string[2], Server::getInstance()->getLevelByName($splitted_string[3]));
	}
}