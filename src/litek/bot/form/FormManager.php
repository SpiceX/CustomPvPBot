<?php

namespace litek\bot\form;

use litek\bot\CustomPvPBot;
use litek\bot\form\elements\Button;
use litek\bot\form\elements\Dropdown;
use litek\bot\form\elements\Input;
use litek\bot\form\elements\Slider;
use litek\bot\form\elements\Toggle;
use litek\bot\form\types\CustomForm;
use litek\bot\form\types\CustomFormResponse;
use litek\bot\form\types\MenuForm;
use litek\bot\form\types\ModalForm;
use pocketmine\Player;

class FormManager
{
	/** @var CustomPvPBot */
	private $plugin;

	public function __construct(CustomPvPBot $plugin)
	{
		$this->plugin = $plugin;
	}

	public function sendBotPanel(Player $player)
	{
		$player->sendForm(new MenuForm("§l§a»§r §7Custom PvP Bot Panel §l§a«", "§7Select an option: ",
			[
				new Button("§aSummon Bot\n§7Create a new bot"),
				new Button("§aSummon from Template\n§7Use a created template"),
				new Button("§aEdit Template\n§7Edits a custom template"),
				new Button("§aDelete Template\n§7Remove template from list"),
			], function (Player $player, Button $selected): void {
				switch ($selected->getValue()) {
					case 0:
						if ($player->hasPermission("bot.summon")) {
							$this->sendSummonPanel($player);
						} else {
							$player->sendMessage("§l§c»§r §cYou are not allowed to use this.");
						}
						break;
					case 1:
						if ($player->hasPermission("bot.summon.from_template")) {
							$this->sendSummonFromTemplatePanel($player);
						} else {
							$player->sendMessage("§l§c»§r §cYou are not allowed to use this.");
						}
						break;
					case 2:
						if ($player->hasPermission("template.edit")) {
							$this->sendEditPanel($player);
						} else {
							$player->sendMessage("§l§c»§r §cYou are not allowed to use this.");
						}
						break;
					case 3:
						if ($player->hasPermission("template.delete")) {
							$this->sendDeletePanel($player);
						} else {
							$player->sendMessage("§l§c»§r §cYou are not allowed to use this.");
						}
						break;
				}
			}));
	}

	public function sendSummonPanel(Player $player)
	{
		$player->sendForm(new CustomForm("§l§a»§r §7Summon Bot §l§a«",
			[
				new Input("§7Bot name", "bot"),
				new Slider("§7Bot health", 1, 100, 1.0, 20),
				new Slider("§7Bot damage", 1.0, 5.0, 0.1),
				$this->plugin->getSkinStorage()->getSkinCount() > 0 ? new Dropdown("§7Bot skin", $this->plugin->getSkinStorage()->getSkinList()) : new Dropdown("§7Bot skin", ['Default player skin']),
				new Toggle("§fSave as template")
			],
			function (Player $player, CustomFormResponse $response): void {
				$name = $response->getInput()->getValue();
				$health = $response->getSlider()->getValue();
				$damage = $response->getSlider()->getValue();
				if ($this->plugin->getSkinStorage()->getSkinCount() === 0) {
					$skin = $player->getSkin();
				} else {
					$skin = $response->getDropdown()->getSelectedOption();
					$skin = $this->plugin->getSkinStorage()->getSkin($skin);
				}
				$bot = $this->plugin->getEntityManager()->prepareBot($player);
				$bot->setName($name);
				$bot->setMaxHealth($health);
				$bot->setHealth($health);
				$bot->setAttackDamage($damage);
				$bot->setSkin($skin);
				$bot->sendSkin();
				$save = $response->getToggle()->getValue();
				if ($player->hasPermission('template.create')) {
					if ($save) {
						$this->plugin->getTemplateManager()->createTemplate($name, $health, $damage, $skin);
					}
				} else {
					$player->sendMessage("§l§c»§r §cYou are not allowed to create or save templates.");
				}
				$player->sendMessage("§l§c»§r §eWarning, {$name} will spawn near you...");
			}
		));
	}

	public function sendEditPanel(Player $player)
	{
		$player->sendForm(new MenuForm("§l§a»§r §7Edit Template §l§a«", "§7Select a template:",
			$this->getTemplateButtons(), function (Player $player, Button $selected): void {
				$template = $selected->getText();
				$this->sendEditingTemplatePanel($player, $template);
			}));
	}

	private function getTemplateButtons(): array
	{
		$buttons = [];
		foreach ($this->plugin->getTemplateManager()->getTemplateList() as $template) {
			$buttons[] = new Button($template);
		}
		return $buttons;
	}

	public function sendEditingTemplatePanel(Player $player, string $templateName)
	{
		$player->sendForm(new CustomForm("§l§a»§r §7Editing: §a{$templateName} §l§a«",
			[
				new Input("§7Bot name", "bot"),
				new Slider("§7Bot health", 1, 100, 1.0, 20),
				new Slider("§7Bot damage", 1.0, 5.0,0.1),
				new Dropdown("§7Bot skin", $this->plugin->getSkinStorage()->getSkinList()),
			],
			function (Player $player, CustomFormResponse $response) use ($templateName): void {
				list($name, $health, $damage, $skin) = $response->getValues();
				$skin = $this->plugin->getSkinStorage()->getSkin($skin);
				$template = $this->plugin->getTemplateManager()->getTemplate($templateName);
				$template->setName($name);
				$template->setHealth($health);
				$template->setDamage($damage);
				$template->setSkin($skin);
				$player->sendMessage("§l§a»§r §7Template edited with success.");
			}
		));
	}

	public function sendDeletePanel(Player $player)
	{
		$player->sendForm(new MenuForm("§l§a»§r §7Delete Template §l§a«", "§7Select a template:",
			$this->getTemplateButtons(), function (Player $player, Button $selected): void {
				$this->sendDeleteTemplateAdvise($player, $selected->getText());
			}));
	}

	public function sendDeleteTemplateAdvise(Player $player, string $templateName)
	{
		$player->sendForm(new ModalForm("§l§a»§r §cDeleting template §e{$templateName} §l§a«",
			"§cDo you want to delete {$templateName} template?", function (Player $player, bool $response) use ($templateName): void {
				if ($response) {
					$this->plugin->getTemplateManager()->removeTemplate($templateName);
					$player->sendMessage("§l§a»§r §7Template deleted with success.");
				}
			}, "§aAccept", "§cCancel"));
	}

	public function sendSummonFromTemplatePanel(Player $player)
	{
		$player->sendForm(new MenuForm("§l§a»§r §7Edit Template §l§a«", "§7Select a template:",
			$this->getTemplateButtons(), function (Player $player, Button $selected): void {
				$template = $this->plugin->getTemplateManager()->getTemplate($selected->getText());
				$bot = $this->plugin->getEntityManager()->prepareBot($player);
				$bot->setName($template->getName());
				$bot->setMaxHealth($template->getHealth());
				$bot->setHealth($template->getHealth());
				$bot->setAttackDamage($template->getDamage());
				$bot->setSkin($template->getSkin());
				$bot->sendSkin();
				$player->sendMessage("§l§c»§r §eWarning, {$template->getName()} will spawn near you...");
			}));
	}
}