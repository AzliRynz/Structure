<?php

declare(strict_types=1);

namespace Structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use Structure\Main;
use Structure\structure\BaseStructure;
use Structure\structure\DesertTemple;
use Structure\structure\JungleTemple;
use Structure\structure\WitchHut;
use Structure\structure\IglooStructure;
use Structure\structure\OceanRuin;
use Structure\structure\PillagerOutpost;
use Structure\structure\Stronghold;
use Structure\structure\VillageStructure;
use Structure\structure\NetherFortress;
use Structure\structure\EndCity;

class StructureManager {

    private Main $plugin;

    /** @var array<string, BaseStructure> */
    private array $structures = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->registerDefaultStructures();
    }

    private function registerDefaultStructures(): void {
        $this->register(new DesertTemple());
        $this->register(new JungleTemple());
        $this->register(new WitchHut());
        $this->register(new IglooStructure());
        $this->register(new OceanRuin());
        $this->register(new PillagerOutpost());
        $this->register(new Stronghold());
        $this->register(new VillageStructure());
        $this->register(new NetherFortress());
        $this->register(new EndCity());
    }

    public function register(BaseStructure $structure): void {
        $this->structures[strtolower($structure->getName())] = $structure;
    }

    /**
     * Spawn sebuah struktur pada koordinat tertentu di dunia.
     * Mengembalikan true jika berhasil, false jika gagal.
     */
    public function spawnStructure(string $name, World $world, Vector3 $pos): bool {
        $key = strtolower($name);
        if (!isset($this->structures[$key])) {
            return false;
        }
        $structure = $this->structures[$key];
        $structure->generate($world, $pos);
        return true;
    }

    /** @return array<string, BaseStructure> */
    public function getStructures(): array {
        return $this->structures;
    }

    public function getStructure(string $name): ?BaseStructure {
        return $this->structures[strtolower($name)] ?? null;
    }

    public function getStructureNames(): array {
        return array_keys($this->structures);
    }
}
