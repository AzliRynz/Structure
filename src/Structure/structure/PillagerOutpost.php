<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

/**
 * Pillager Outpost
 * ─────────────────────────────────────────────────────────────────────────────
 * Menara pengintai kayu+batu dengan 4 sudut penjaga, 2 kandang besi,
 * dan dada loot di menara atas. Ada tiang gantung (gallows) dekorasi.
 *
 * Ukuran: 9x16x9
 */
class PillagerOutpost extends BaseStructure {

    public function getName(): string        { return "PillagerOutpost"; }
    public function getDescription(): string { return "Menara penjaga pillager dengan kandang besi dan loot di atas"; }
    public function getWidth(): int          { return 9; }
    public function getHeight(): int         { return 16; }
    public function getDepth(): int          { return 9; }
    public function getAllowedBiomes(): array { return ["Plains", "Desert", "Taiga", "Savanna"]; }
    public function hasLootRoom(): bool      { return true; }

    public function generate(World $world, Vector3 $origin): void {
        $oak   = VanillaBlocks::OAK_PLANKS();
        $log   = VanillaBlocks::OAK_LOG();
        $stone = VanillaBlocks::COBBLESTONE();
        $dark  = VanillaBlocks::DARK_OAK_PLANKS();
        $fence = VanillaBlocks::OAK_FENCE();
        $iron  = VanillaBlocks::IRON_BARS();
        $air   = VanillaBlocks::AIR();
        $slab  = VanillaBlocks::OAK_SLAB();
        $dark2 = VanillaBlocks::DARK_OAK_LOG();
        $ladder= VanillaBlocks::LADDER();

        // ── Fondasi batu ──────────────────────────────────────────────────
        $this->fillBox($world, $origin, 0, 0, 0, 8, 0, 8, $stone);

        // ── Menara utama (pondasi 5x5 di tengah) ─────────────────────────
        // log 4 sudut
        $cornerLog = [[1,1],[1,7],[7,1],[7,7]];
        for ($y = 1; $y <= 15; $y++) {
            foreach ($cornerLog as [$lx, $lz]) {
                $this->setBlock($world, $origin, $lx, $y, $lz, $log);
            }
        }

        // Lantai setiap 4 blok
        foreach ([0, 4, 8, 12] as $floorY) {
            $this->fillBox($world, $origin, 1, $floorY, 1, 7, $floorY, 7, $oak);
            $this->clearBox($world, $origin, 2, $floorY + 1, 2, 6, $floorY + 3, 6);
        }

        // Dinding (balok kayu) 2 lapis lantai pertama
        $this->hollowBox($world, $origin, 1, 1, 1, 7, 4, 7, $oak, $air);

        // Jendela kecil di sisi
        $winPositions = [[1,2,4],[7,2,4],[4,2,1],[4,2,7]];
        foreach ($winPositions as [$wx,$wy,$wz]) {
            $this->clearBox($world, $origin, $wx, $wy, $wz, $wx, $wy+1, $wz);
        }

        // ── Atap menara (y=13..15) ────────────────────────────────────────
        $this->fillBox($world, $origin, 0, 13, 0, 8, 13, 8, $dark);
        $this->fillBox($world, $origin, 1, 14, 1, 7, 14, 7, $dark);
        $this->fillBox($world, $origin, 2, 15, 2, 6, 15, 6, $dark);
        // Pagar di tepi atap
        $this->fillBox($world, $origin, 0, 14, 0, 8, 14, 0, $fence);
        $this->fillBox($world, $origin, 0, 14, 8, 8, 14, 8, $fence);
        $this->fillBox($world, $origin, 0, 14, 0, 0, 14, 8, $fence);
        $this->fillBox($world, $origin, 8, 14, 0, 8, 14, 8, $fence);

        // ── Ladder ke atas ────────────────────────────────────────────────
        for ($y = 1; $y <= 13; $y++) {
            $this->setBlock($world, $origin, 2, $y, 2, $ladder);
        }

        // ── Chest loot di lantai atas ─────────────────────────────────────
        $chestPos = new Vector3(
            (int)($origin->x + 5), (int)($origin->y + 13), (int)($origin->z + 5)
        );
        $world->setBlock($chestPos, VanillaBlocks::CHEST());
        $lootTable = [
            VanillaItems::CROSSBOW(),
            VanillaItems::DARK_OAK_LOG(),
            VanillaItems::IRON_INGOT(),
            VanillaItems::TRIPWIRE_HOOK(),
            VanillaItems::STRING(),
            VanillaItems::EMERALD(),
            VanillaItems::BOTTLE_O_ENCHANTING(),
        ];
        $this->fillChest($world, $chestPos, $lootTable, 3, 7);

        // ── Kandang besi di tanah (sekitar menara) ────────────────────────
        // Kandang 1 (barat laut)
        $this->hollowBox($world, $origin, -4, 0, -4, -1, 3, -1, $iron);
        $this->clearBox($world, $origin, -3, 0, -4, -2, 2, -4); // pintu
        $this->fillBox($world, $origin, -4, 0, -4, -1, 0, -1, $stone); // lantai

        // Kandang 2 (timur laut)
        $this->hollowBox($world, $origin, 10, 0, -4, 13, 3, -1, $iron);
        $this->clearBox($world, $origin, 11, 0, -4, 12, 2, -4);
        $this->fillBox($world, $origin, 10, 0, -4, 13, 0, -1, $stone);

        // ── Gallows (tiang gantungan) dekorasi ────────────────────────────
        $this->setBlock($world, $origin, 4, 1, -3, $dark2);
        $this->setBlock($world, $origin, 4, 2, -3, $dark2);
        $this->setBlock($world, $origin, 4, 3, -3, $dark2);
        $this->setBlock($world, $origin, 4, 4, -3, $dark2);
        $this->setBlock($world, $origin, 4, 4, -4, $dark2); // lengan horizontal
        $this->setBlock($world, $origin, 4, 4, -5, $dark2);
        $this->setBlock($world, $origin, 4, 3, -5, VanillaBlocks::CHAIN()); // rantai
    }
}
