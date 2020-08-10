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

namespace litek\bot;

use Exception;
use litek\bot\command\CommandManager;
use litek\bot\entity\EntityManager;
use litek\bot\form\FormManager;
use litek\bot\provider\CombatLogger;
use litek\bot\provider\SkinStorage;
use litek\bot\provider\TemplateManager;
use litek\bot\provider\YamlDataProvider;
use litek\bot\task\BotLookingTask;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class CustomPvPBot extends PluginBase implements Listener
{
	/**
	 * @var CustomPvPBot
	 */
	private static $instance;

	/** @var YamlDataProvider */
	private $yamlProvider;

	/** @var CombatLogger */
	private $combatLogger;

	/** @var TemplateManager */
	private $templateManager;

	/** @var BotListener */
	private $botListener;

	/** @var SkinStorage */
	private $skinStorage;

	/** @var FormManager */
	private $formManager;

	/** @var EntityManager */
	private $entityManager;

	/**@var CommandManager */
	private $commandManager;

	/**
	 * @return CustomPvPBot
	 */
	public static function getInstance(): CustomPvPBot
	{
		return self::$instance;
	}

	public function onEnable(): void
	{
		$this->initVariables();
		$this->getLogger()->info("§l§6»§r §aCustomPvPBot activated");
		$this->getLogger()->info("§l§6»§r §aMade by @LiTEK_");
		$this->getLogger()->info("§l§6»§r §aLicense §e" . $this->getConfig()->get('license'));
		$this->getScheduler()->scheduleRepeatingTask(new BotLookingTask($this), 20);
	}

	private function initVariables()
	{
		try {
			self::$instance = $this;
			$this->saveDefaultConfig();
			$this->skinStorage = new SkinStorage($this);
			$this->yamlProvider = new YamlDataProvider($this);
			$this->combatLogger = new CombatLogger($this);
			$this->botListener = new BotListener($this);
			$this->formManager = new FormManager($this);
			$this->entityManager = new EntityManager($this);
			$this->commandManager = new CommandManager($this);
			$this->templateManager = new TemplateManager($this);
		} catch (Exception $e) {
			$this->getLogger()->warning("All variables could not be loaded, please check to avoid errors.");
		}
	}

	public function onDisable()
	{
		$this->templateManager->saveAll();
	}

	/**
	 * @return CombatLogger
	 */
	public function getCombatLogger(): CombatLogger
	{
		return $this->combatLogger;
	}

	/**
	 * @return TemplateManager
	 */
	public function getTemplateManager(): TemplateManager
	{
		return $this->templateManager;
	}

	/**
	 * @return SkinStorage
	 */
	public function getSkinStorage(): SkinStorage
	{
		return $this->skinStorage;
	}

	/**
	 * @return FormManager
	 */
	public function getFormManager(): FormManager
	{
		return $this->formManager;
	}

	/**
	 * @return EntityManager
	 */
	public function getEntityManager(): EntityManager
	{
		return $this->entityManager;
	}

}