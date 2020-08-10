<?php


namespace litek\bot\util;

use DivisionByZeroError;
use InvalidStateException;
use litek\bot\entity\types\Bot;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\math\VoxelRayTrace;
use pocketmine\Player;

class InteractionHelper
{
	public static function getEntityPlayerLookingAt(Player $player, int $maxDistance, bool $useCorrection = false){
		if($player->isClosed() or !$player->isOnline() or !$player->spawned){
			return null;
		}
		/**
		 * @var Entity
		 */
		$entity = null;

		if($player->temporalVector !== null){
			$nearbyEntities = $player->getLevel()->getNearbyEntities($player->boundingBox->expandedCopy($maxDistance, $maxDistance, $maxDistance), $player);
			foreach ($nearbyEntities as $nearbyEntity) {
				if ($nearbyEntity instanceof Bot){
					return $nearbyEntity;
				}
			}
			try{
				foreach(VoxelRayTrace::inDirection($player->add(0, $player->eyeHeight, 0), $player->getDirectionVector(), $maxDistance) as $vector3){

					$block = $player->level->getBlockAt($vector3->x, $vector3->y, $vector3->z);
					$entity = self::getEntityAtPosition($nearbyEntities, $block->x, $block->y, $block->z, $useCorrection);
					if($entity !== null and $entity instanceof Living){
						break;
					}
				}
			}catch(InvalidStateException $e){
				// nothing to log here!
			}catch(DivisionByZeroError $e){
				// pass
			}
		}

		return $entity;
	}

	private static function getEntityAtPosition(array $nearbyEntities, int $x, int $y, int $z, bool $useCorrection){
		foreach($nearbyEntities as $nearbyEntity){
			if($nearbyEntity->getFloorX() === $x and $nearbyEntity->getFloorY() === $y and $nearbyEntity->getFloorZ() === $z){
				return $nearbyEntity;
			}else if($useCorrection){ // when using correction, we search not only in front also 1 block up/down/left/right etc. pp
				return self::getCorrectedEntity($nearbyEntity, $x, $y, $z);
			}
		}
		return null;
	}

	private static function getCorrectedEntity(Entity $entity, int $x, int $y, int $z){
		$entityX = $entity->getFloorX();
		$entityY = $entity->getFloorY();
		$entityZ = $entity->getFloorZ();

		for($searchX = ($x - 1); $searchX <= ($x + 1); $searchX++){
			for($searchY = ($y - 1); $searchY <= ($y + 1); $searchY++){
				for($searchZ = ($z - 1); $searchZ <= ($z + 1); $searchZ++){
					if($entityX === $searchX and $entityY === $searchY and $entityZ === $searchZ){
						return $entity;
					}
				}
			}
		}
		return null;
	}

}