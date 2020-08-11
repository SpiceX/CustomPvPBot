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
use litek\bot\math\Vector3X;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use litek\bot\provider\elements\Template;
use pocketmine\entity\Skin;
use pocketmine\utils\Config;

class TemplateManager
{
    /** @var CustomPvPBot */
    private $plugin;

    /** @var Template[] */
    private $templates = [];

    public function __construct(CustomPvPBot $plugin)
    {
        $this->plugin = $plugin;
        if (($loaded = $this->loadTemplates()) !== 0) {
            $plugin->getLogger()->info("§l§a»§r §aFound {$loaded} templates.");
        }
    }

    public function loadTemplates(): int
    {
        $templateCount = 0;
        foreach (glob($this->plugin->getDataFolder() . "templates" . DIRECTORY_SEPARATOR . "*.json") as $template) {
            $templateName = basename($template, ".json");
            $templateConfig = new Config($template, Config::JSON);
            $this->templates[$templateName] = new Template(
                $templateConfig->get('name'),
                $templateConfig->get('health'),
                $templateConfig->get('damage'),
                $this->plugin->getSkinStorage()->getSkin($templateConfig->get('skin')),
                $templateConfig->get('command'),
                $templateConfig->get('respawn_time'),
                Vector3X::toObject($templateConfig->get('default_position'))
            );
            $templateCount++;
        }
        return $templateCount;
    }

    public function getTemplate(string $template): ?Template
    {
        if (isset($this->templates[$template])) {
            return $this->templates[$template];
        }
        return null;
    }

    public function removeTemplate(string $template): void
    {
        if (isset($this->templates[$template])) {
            unset($this->templates[$template]);
        }
        @unlink($this->plugin->getDataFolder() . 'templates' . "/$template.json");
    }

    public function saveAll(): void
    {
        foreach ($this->templates as $template => $object) {
            $object->save();
        }
    }

    public function getTemplateList(): array
    {
        $templateList = [];
        foreach ($this->templates as $template => $object) {
            $templateList[] = $template;
        }
        return $templateList;
    }

    public function createTemplate(string $name, float $health, float $damage, Skin $skin, string $command, int $respawnTime, Position $defaultPosition): void
    {
        if (isset($this->templates[$name])) {
            unset($this->templates[$name]);
        }
        $this->templates[$name] = new Template($name, $health, $damage, $skin, $command, $respawnTime, $defaultPosition);
        $this->templates[$name]->save();
    }

    public function editTemplate(string $template, string $name, float $health, float $damage, Skin $skin, string $command, int $respawnTime): void
    {
        $old = $this->templates[$template];
        $new = (clone $old);
        $new->setName($name);
        $new->setHealth($health);
        $new->setDamage($damage);
        $new->setSkin($skin);
        $new->setCommand($command);
        $new->setRespawnTime($respawnTime);
        $this->removeTemplate($old->getName());
        $this->templates[$new->getName()] = $new;
    }

}