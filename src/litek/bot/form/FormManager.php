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
use litek\bot\math\Vector3X;
use pocketmine\entity\InvalidSkinException;
use pocketmine\Player;
use pocketmine\utils\Config;

class FormManager
{
    /** @var CustomPvPBot */
    private $plugin;

    public function __construct(CustomPvPBot $plugin)
    {
        $this->plugin = $plugin;
    }

    public function sendBotPanel(Player $player): void
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

    public function sendSummonPanel(Player $player): void
    {
        $player->sendForm(new CustomForm("§l§a»§r §7Summon Bot §l§a«",
            [
                new Input("§7Bot name", "bot"),
                new Slider("§7Bot health", 1, 100000.0, 1.0, 20),
                new Slider("§7Bot damage", 1.0, 100.0, 1.0),
                $this->plugin->getSkinStorage()->getSkinCount() > 0 ? new Dropdown("§7Bot skin", $this->plugin->getSkinStorage()->getSkinList()) : new Dropdown("§7Bot skin", ['Default player skin']),
                new Input("Command", "/command {player} [args]"),
                new Input("Respawn time (seconds)", "1200"),
                new Toggle("§fSave as template")
            ],
            function (Player $player, CustomFormResponse $response): void {
                $name = $response->getInput()->getValue();
                $health = $response->getSlider()->getValue();
                $damage = $response->getSlider()->getValue();
                if ($this->plugin->getSkinStorage()->getSkinCount() === 0) {
                    $response->getDropdown()->getSelectedOption();
                    $skin = $player->getSkin();
                } else {
                    $skin = $response->getDropdown()->getSelectedOption();
                    $skin = $this->plugin->getSkinStorage()->getSkin($skin);
                }

                try {
                    $bot = $this->plugin->getEntityManager()->prepareBot($player,$player->asPosition());
                    $command = $response->getInput()->getValue();
                    $respawnTime = $response->getInput()->getValue();
                    $bot->setName($name);
                    $bot->setMaxHealth($health);
                    $bot->setHealth($health);
                    $bot->setAttackDamage($damage);
                    $bot->setCommand($command);
                    $bot->setRespawnTime($respawnTime);
                    $bot->setDefaultPosition($player->asPosition());
                    $bot->setSkin($skin);
                    $bot->sendSkin();
                    $bot->spawnToAll();
                } catch (InvalidSkinException $exception) {
                    $player->sendMessage("§cA valid skin is needed to summon a bot, please change your skin and join again.");
                    return;
                }

                $save = $response->getToggle()->getValue();
                if ($player->hasPermission('template.create')) {
                    if ($save) {
                        if ($this->plugin->getSkinStorage()->getSkinCount() > 0){
                            $this->plugin->getTemplateManager()->createTemplate($name, $health, $damage, $skin, $command, $respawnTime, $player->asPosition());
                        } else {
                            $player->sendMessage("§cYou have not valid skins in data folder, template could not be created.");
                        }
                    }
                } else {
                    $player->sendMessage("§l§c»§r §cYou are not allowed to create or save templates.");
                }

                $player->sendMessage("§l§c»§r §eWarning, {$name} will spawn near you...");
            }
        ));
    }

    public function sendSummonFromTemplatePanel(Player $player): void
    {
        $player->sendForm(new MenuForm("§l§a»§r §7Edit Template §l§a«", "§7Select a template:",
            $this->getTemplateButtons(), function (Player $player, Button $selected): void {
                $template = $this->plugin->getTemplateManager()->getTemplate($selected->getText());
                $config = new Config($this->plugin->getDataFolder() . 'templates' . "/{$selected->getText()}.json", Config::JSON);
                $position = $config->get('default_position');
                try {
                    if ($template !== null && $position !== false) {
                        $bot = $this->plugin->getEntityManager()->prepareBot($player, Vector3X::toObject($position));
                        $bot->teleport($bot->getDefaultPosition());
                        $command = $template->getCommand();
                        $bot->setName($template->getName());
                        $bot->setMaxHealth($template->getHealth());
                        $bot->setHealth($template->getHealth());
                        $bot->setAttackDamage($template->getDamage());
                        $bot->setCommand($command);
                        $bot->setSkin($template->getSkin());
                        $bot->setRespawnTime($template->getRespawnTime());
                        $bot->setDefaultPosition($template->getDefaultPosition());
                        $bot->sendSkin();
                        $bot->spawnToAll();
                    }
                } catch (InvalidSkinException $exception) {
                    $player->sendMessage("§cA valid skin is needed to summon a bot, please change your skin and join again.");
                    return;
                }
                $player->sendMessage("§l§c»§r §eWarning, {$template->getName()} will spawn near you...");
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

    public function sendEditPanel(Player $player): void
    {
        $player->sendForm(new MenuForm("§l§a»§r §7Edit Template §l§a«", "§7Select a template:",
            $this->getTemplateButtons(), function (Player $player, Button $selected): void {
                $template = $selected->getText();
                $this->sendEditingTemplatePanel($player, $template);
            }));
    }

    public function sendEditingTemplatePanel(Player $player, string $templateName): void
    {
        $template = $this->plugin->getTemplateManager()->getTemplate($templateName);
        if ($template !== null) {
            $player->sendForm(new CustomForm("§l§a»§r §7Editing: §a{$templateName} §l§a«",
                [
                    new Input("§7Bot name", "bot", $template->getName()),
                    new Slider("§7Bot health", 1, 100000.0, 1.0, $template->getHealth()),
                    new Slider("§7Bot damage", 1.0, 100.0, 1.0, $template->getDamage()),
                    new Dropdown("§7Bot skin", $this->plugin->getSkinStorage()->getSkinList()),
                    new Input("Command", "/command {player} [args]", $template->getCommand()),
                    new Input("Respawn time (seconds)", "1200", $template->getRespawnTime()),
                ],
                function (Player $player, CustomFormResponse $response) use ($templateName): void {
                    [$name, $health, $damage, $skin, $command, $respawnTime] = $response->getValues();
                    $skin = $this->plugin->getSkinStorage()->getSkin($skin);
                    if ($skin !== null) {
                        $this->plugin->getTemplateManager()->editTemplate($templateName, $name, $health, $damage, $skin, $command, $respawnTime);
                    }
                    $player->sendMessage("§l§a»§r §7Template edited with success.");
                    $this->sendBotPanel($player);
                }
            ));
        }
    }

    public function sendDeletePanel(Player $player): void
    {
        $player->sendForm(new MenuForm("§l§a»§r §7Delete Template §l§a«", "§7Select a template:",
            $this->getTemplateButtons(), function (Player $player, Button $selected): void {
                $this->sendDeleteTemplateAdvise($player, $selected->getText());
            }));
    }

    public function sendDeleteTemplateAdvise(Player $player, string $templateName): void
    {
        $player->sendForm(new ModalForm("§l§a»§r §cDeleting template §e{$templateName} §l§a«",
            "§cDo you want to delete {$templateName} template?", function (Player $player, bool $response) use ($templateName): void {
                if ($response) {
                    $this->plugin->getTemplateManager()->removeTemplate($templateName);
                    $player->sendMessage("§l§a»§r §7Template deleted with success.");
                }
            }, "§aAccept", "§cCancel"));
    }
}
