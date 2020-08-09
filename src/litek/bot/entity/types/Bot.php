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

namespace litek\bot\entity\types;

use pocketmine\block\Flowable;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Sword;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\Server;

class Bot extends Human
{

	/** @var int */
	public $jumpTicks = 5;
	/**@var int|mixed|string */
	public $name;
	/** @var string */
	private $target;
	/** @var int */
	private $hitTick = 0;
	/** @var float */
	private $speed = 0.4;
	/** @var int */
	private $pearlsRemaining = 16;
	/** @var int */
	private $agroCooldown = 0;
	/** @var int|mixed */
	private $attackDamage = 2;

	/**
	 * bot constructor.
	 * @param Level $level
	 * @param CompoundTag $nbt
	 * @param string $target
	 */
	public function __construct(Level $level, CompoundTag $nbt, string $target)
	{
		parent::__construct($level, $nbt);
		$this->target = $target;
		$this->setNameTag($this->getNameTag());
	}

	/**
	 * @param int $tickDiff
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1): bool
	{
		parent::entityBaseTick($tickDiff);
		if (!$this->isAlive() || $this->getTargetPlayer() === null || !$this->getTargetPlayer()->isAlive()) {
			if (!$this->closed) $this->flagForDespawn();
			return false;
		}
		$this->setNameTagAlwaysVisible(true);
		$this->setNameTagVisible(true);
		$this->setNameTag('');
		$this->setNameTag($this->getBotTag());
		$position = $this->getTargetPlayer()->asVector3();
		$x = $position->x - $this->getX();
		$z = $position->z - $this->getZ();
		if ($this->jumpTicks > 0) {
			$this->jumpTicks--;
		}
		if ($x != 0 || $z != 0) {
			$this->motion->x = $this->getSpeed() * 0.35 * ($x / (abs($x) + abs($z)));
			$this->motion->z = $this->getSpeed() * 0.35 * ($z / (abs($x) + abs($z)));
		}
		if (!$this->isOnGround()) {
			if ($this->motion->y > -$this->gravity * 4) {
				$this->motion->y = -$this->gravity * 4;
			} else {
				$this->motion->y += $this->isUnderwater() ? $this->gravity : -$this->gravity;
			}
			if ($this->isUnderwater()) {
				$this->setGenericFlag(Entity::DATA_FLAG_SWIMMING, true);
			} else {
				$this->setGenericFlag(Entity::DATA_FLAG_SWIMMING, false);
			}
		} else {
			$this->motion->y -= $this->gravity;
		}
		$this->setSprinting(true);
		if ($this->getHealth() < 5) {
			if (!$this->recentlyHit()) {
				$this->move($this->motion->x, $this->motion->y, $this->motion->z);
				if ($this->shouldJump()) {
					$this->jump();
				}
			}
			$this->attackTargetPlayer();
		} else {
			if (!$this->recentlyHit()) {
				$this->move($this->motion->x, $this->motion->y, $this->motion->z);
				if ($this->shouldJump()) {
					$this->jump();
				}
			}
			if ($this->shouldJump()) {
				$this->jump();
			}
			if ($this->getTargetPlayer() === null) {
				$this->flagForDespawn();
				return false;
			} else {
				$this->attackTargetPlayer();
			}
		}
		if ($this->distance($this->getTargetPlayer()) > 20) {
			$this->pearl();
		}
		if ($this->distance($this->getTargetPlayer()) > 8) {
			$this->setSprinting(true);
			$this->speed *= 1.3;
		} else {
			$this->setSprinting(false);
			$this->speed = 0.4;
		}
		if ($this->distance($this->getTargetPlayer()) > 0.25 && $this->distance($this->getTargetPlayer()) < 4 && $this->getTargetPlayer()->getHealth() <= 15 && $this->canThrowPearl()) {
			$this->pearl(true);
		}
		if ($this->shouldJump()) {
			$this->jump();
		}
		$this->updateMovement();
		return $this->isAlive();
	}

	/**
	 * @return Player|null
	 */
	public function getTargetPlayer(): ?Player
	{
		return Server::getInstance()->getPlayer($this->target);
	}

	public function getBotTag(): string
	{
		return $this->name . " | " . round($this->getHealth()) . " §c❤";
	}

	/**
	 * @return float
	 */
	public function getSpeed(): float
	{
		return $this->speed;
	}

	public function recentlyHit(): bool
	{
		return $this->hitTick !== null ? Server::getInstance()->getTick() - $this->hitTick <= 4 : false;
	}

	public function shouldJump()
	{
		if ($this->jumpTicks > 0) return false;
		return $this->isCollidedHorizontally ||
			($this->getFrontBlock()->getId() != 0 || $this->getFrontBlock(-1) instanceof Stair) ||
			($this->getLevel()->getBlock($this->asVector3()->add(0, -0, 5)) instanceof Slab &&
				(!$this->getFrontBlock(-0.5) instanceof Slab && $this->getFrontBlock(-0.5)->getId() != 0)) &&
			$this->getFrontBlock(1)->getId() == 0 &&
			$this->getFrontBlock(2)->getId() == 0 &&
			!$this->getFrontBlock() instanceof Flowable &&
			$this->jumpTicks == 0;
	}

	public function getFrontBlock($y = 0)
	{
		$dv = $this->getDirectionVector();
		$pos = $this->asVector3()->add($dv->x * $this->getScale(), $y + 1, $dv->z * $this->getScale())->round();
		return $this->getLevel()->getBlock($pos);
	}

	public function jump(): void
	{
		$this->motion->y = $this->gravity * $this->getJumpMultiplier();
		$this->move($this->motion->x * 1.25, $this->motion->y, $this->motion->z * 1.25);
		$this->jumpTicks = 5; //($this->getJumpMultiplier() == 4 ? 2 : 5);
	}

	public function getJumpMultiplier()
	{
		return 16;
	}

	public function attackTargetPlayer(): void
	{
		if (mt_rand(0, 100) % 4 === 0) {
			$this->lookAt($this->getTargetPlayer()->asVector3());
		}
		if ($this->jumpTicks > 0) {
			$this->jumpTicks--;
		}
		if ($this->isLookingAt($this->getTargetPlayer()->asVector3())) {
			if ($this->distance($this->getTargetPlayer()) <= 2) {
				$this->getInventory()->setHeldItemIndex(0);
				if (Server::getInstance()->getTick() >= 5) {
					$event = new EntityDamageByEntityEvent($this, $this->getTargetPlayer(), EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getInventory()->getItemInHand() instanceof Sword ? $this->getInventory()->getItemInHand()->getAttackPoints() : 0.5);
					$this->broadcastEntityEvent(4);
					if ($this->shouldJump()) {
						$this->jump();
					}
					$this->getTargetPlayer()->attack($event);
				}
			}
		}
	}

	/**
	 * @param Vector3 $target
	 * @return bool
	 */
	public function isLookingAt(Vector3 $target): bool
	{
		$horizontal = sqrt(($target->x - $this->x) ** 2 + ($target->z - $this->z) ** 2);
		$vertical = $target->y - $this->y;
		$expectedPitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down

		$xDist = $target->x - $this->x;
		$zDist = $target->z - $this->z;
		$expectedYaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if ($expectedYaw < 0) {
			$expectedYaw += 360.0;
		}

		return abs($expectedPitch - $this->getPitch()) <= 5 && abs($expectedYaw - $this->getYaw()) <= 10;
	}

	public function pearl($agro = false): void
	{
		if ($this->pearlsRemaining > 0) {
			if (!$agro) {
				$max = 5;
			} else {
				$max = 1.5;
				$this->agroCooldown = Server::getInstance()->getTick();
			}
			--$this->pearlsRemaining;
			$this->teleport($this->getTargetPlayer()->asVector3()->subtract(mt_rand(0, $max), 0, mt_rand(0, $max)));
		}
	}

	/**
	 * @return bool
	 */
	public function canThrowPearl(): bool
	{
		return $this->agroCooldown === null ? true : Server::getInstance()->getTick() - $this->agroCooldown >= 175;
	}

	public function setName(string $name)
	{
		$this->name = $name;
	}

	public function getAttackDamage()
	{
		return $this->attackDamage;
	}

	public function setAttackDamage(float $attackDamage)
	{
		$this->attackDamage = $attackDamage;
	}

	/**
	 * @param Entity $attacker
	 * @param float $damage
	 * @param float $x
	 * @param float $z
	 * @param float $base
	 */
	public function knockBack(Entity $attacker, float $damage, float $x, float $z, float $base = 0.4): void
	{
		parent::knockBack($attacker, $damage, $x, $z, 0.45);
		$this->hitTick = Server::getInstance()->getTick();
	}

	public function attack(EntityDamageEvent $source): void
	{
		parent::attack($source);
		$this->hitTick = Server::getInstance()->getTick();
	}

}