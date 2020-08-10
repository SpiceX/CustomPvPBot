<?php


namespace litek\bot\math;


use pocketmine\math\Vector3;

class Vector3X extends Vector3
{
	public static function toString(Vector3 $vector3){
		return "{$vector3->x}:{$vector3->y}:{$vector3->z}";
	}

	public static function toObject(string $vector3){
		$splitted_string = explode(':', $vector3);
		return new Vector3((float)$splitted_string[0],(float)$splitted_string[1],(float)$splitted_string[2]);
	}
}