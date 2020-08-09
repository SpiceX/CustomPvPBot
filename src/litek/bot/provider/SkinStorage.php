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

namespace litek\bot\provider;

use litek\bot\CustomPvPBot;
use pocketmine\entity\Skin;

class SkinStorage
{
	/** @var CustomPvPBot */
	private $plugin;

	/** @var Skin[] */
	private $skins = [];

	/** @var int */
	private $skinCount = 0;

	public function __construct(CustomPvPBot $plugin)
	{
		$this->plugin = $plugin;
		if (($loaded = $this->loadSkins()) !== 0){
			$plugin->getLogger()->info("§l§a»§r §aFound {$loaded} skins.");
		}
	}

	public function loadSkins(): int
	{
		foreach (glob($this->plugin->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . "*.png") as $skin) {
			$skinName = basename($skin, ".png");
			$this->skins[$skinName] = new Skin($skinName, base64_decode($this->getBytes($skin)));
			$this->skinCount++;
		}
		return $this->skinCount;
	}

	private function getBytes(string $path)
	{
		$img = @imagecreatefrompng($path);
		$skin_bytes = '';
		for ($y = 0, $yMax = @imagesy($img); $y < $yMax; $y++) {
			for ($x = 0, $xMax = @imagesx($img); $x < $xMax; $x++) {
				$colored = @imagecolorat($img, $x, $y);
				$a = ((~(($colored >> 24))) << 1) & 0xff;
				$r = ($colored >> 16) & 0xff;
				$g = ($colored >> 8) & 0xff;
				$b = $colored & 0xff;
				$skin_bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		@imagedestroy($img);
		return base64_encode($skin_bytes);
	}

	public function getSkinList(): array
	{
		$skinList = [];
		foreach ($this->skins as $skin => $object) {
			$skinList[] = $skin;
		}
		return $skinList;
	}

	/**
	 * @param string $skinName
	 * @return Skin|null
	 */
	public function getSkin(string $skinName): ?Skin
	{
		if (isset($this->skins[$skinName])) {
			return $this->skins[$skinName];
		}
		return null;
	}

	public function getSkinCount(){
		return $this->skinCount;
	}
}