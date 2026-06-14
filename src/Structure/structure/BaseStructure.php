<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Block;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\tile\Chest;
use pocketmine\item\VanillaItems;
use pocketmine\item\Item;
use Structure\utils\StructureUtils;

abstract class BaseStructure {

    abstract public function getName(): string;
    abstract public function getDescription(): string;
    abstract public function generate(World $world, Vector3 $origin): void;

    public function getWidth(): int  { return 0; }
    public function getHeight(): int { return 0; }
    public function getDepth(): int  { return 0; }

    /** Daftar biome yang cocok (string nama biome). Kosong = semua. */
    public function getAllowedBiomes(): array { return []; }

    public function hasLootRoom(): bool { return false; }

    /**
     * Helper: set block di koordinat relatif dari origin.
     */
    protected function setBlock(World $world, Vector3 $origin, int $rx, int $ry, int $rz, Block $block): void {
        $x = (int)($origin->x + $rx);
        $y = (int)($origin->y + $ry);
        $z = (int)($origin->z + $rz);
        $world->setBlock(new Vector3($x, $y, $z), $block);
    }

    /**
     * Helper: isi chest dengan loot acak dari tabel loot.
     * @param Item[] $lootTable
     */
    protected function fillChest(World $world, Vector3 $pos, array $lootTable, int $minItems = 3, int $maxItems = 8): void {
        $tile = $world->getTile($pos);
        if (!($tile instanceof Chest)) return;

        $inventory = $tile->getInventory();
        $inventory->clearAll();

        $count = mt_rand($minItems, $maxItems);
        $slots = range(0, 26);
        shuffle($slots);
        $usedSlots = array_slice($slots, 0, $count);

        foreach ($usedSlots as $slot) {
            $item = $lootTable[array_rand($lootTable)];
            $item = clone $item;
            $item->setCount(mt_rand(1, $item->getMaxStackSize() > 16 ? 16 : $item->getMaxStackSize()));
            $inventory->setItem($slot, $item);
        }
    }

    /**
     * Helper: bangun kotak solid (filled).
     */
    protected function fillBox(World $world, Vector3 $origin, int $x1, int $y1, int $z1,
                                int $x2, int $y2, int $z2, Block $block): void {
        for ($x = $x1; $x <= $x2; $x++)
            for ($y = $y1; $y <= $y2; $y++)
                for ($z = $z1; $z <= $z2; $z++)
                    $this->setBlock($world, $origin, $x, $y, $z, $block);
    }

    /**
     * Helper: bangun tembok (hollow box) - hanya sisi luar.
     */
    protected function hollowBox(World $world, Vector3 $origin, int $x1, int $y1, int $z1,
                                  int $x2, int $y2, int $z2, Block $block, Block $fill = null): void {
        for ($x = $x1; $x <= $x2; $x++) {
            for ($y = $y1; $y <= $y2; $y++) {
                for ($z = $z1; $z <= $z2; $z++) {
                    $isWall = ($x === $x1 || $x === $x2 || $y === $y1 || $y === $y2 || $z === $z1 || $z === $z2);
                    if ($isWall) {
                        $this->setBlock($world, $origin, $x, $y, $z, $block);
                    } elseif ($fill !== null) {
                        $this->setBlock($world, $origin, $x, $y, $z, $fill);
                    }
                }
            }
        }
    }

    /**
     * Helper: gali lubang (isi dengan udara).
     */
    protected function clearBox(World $world, Vector3 $origin, int $x1, int $y1, int $z1,
                                 int $x2, int $y2, int $z2): void {
        $this->fillBox($world, $origin, $x1, $y1, $z1, $x2, $y2, $z2, VanillaBlocks::AIR());
    }
}
