<?php

declare(strict_types=1);

namespace Structure;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Structure\command\StructureCommand;
use Structure\listener\WorldListener;
use Structure\StructureManager;

class Main extends PluginBase {

    use SingletonTrait;

    private StructureManager $structureManager;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->structureManager = new StructureManager($this);

        $this->getServer()->getCommandMap()->register("structure", new StructureCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new WorldListener($this), $this);

        $this->getLogger()->info("§aPlugin Structure berhasil diaktifkan!");
        $this->getLogger()->info("§e" . count($this->structureManager->getStructures()) . " struktur telah dimuat.");
    }

    protected function onDisable(): void {
        $this->getLogger()->info("§cPlugin Structure dinonaktifkan.");
    }

    public function getStructureManager(): StructureManager {
        return $this->structureManager;
    }
}
