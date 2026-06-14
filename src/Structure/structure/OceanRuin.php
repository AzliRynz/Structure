<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

/**
 * Ocean Ruin (Cold/Warm variant)
 * ─────────────────────────────────────────────────────────────────────────────
 * Reruntuhan batu di dasar laut dengan chest loot yang tersembunyi di pasir.
 * Ada 2 varian: cold (stone brick) & warm (sandstone) — ini versi cold.
 *
 * Ukuran: 10x8x11
 */
class OceanRuin extends BaseStructure {

    public function getName(): string        { return "OceanRuin"; }
    public function getDescription(): string { return "Reruntuhan batu bawah laut dengan chest tersembunyi dan kelp"; }
    public function getWidth(): int          { return 10; }
    public function getHeight(): int         { return 8; }
    public function getDepth(): int          { return 11; }
    public function getAllowedBiomes(): array { return ["Ocean", "ColdOcean", "WarmOcean"]; }
    public function hasLootRoom(): bool      { return true; }

    public function generate(World $world, Vector3 $origin): void {
        $stone  = VanillaBlocks::STONE_BRICKS();
        $mossy  = VanillaBlocks::MOSSY_STONE_BRICKS();
        $cracked= VanillaBlocks::CRACKED_STONE_BRICKS();
        $cobble = VanillaBlocks::COBBLESTONE();
        $sand   = VanillaBlocks::SAND();
        $gravel = VanillaBlocks::GRAVEL();
        $water  = VanillaBlocks::WATER();
        $seaLantern = VanillaBlocks::SEA_LANTERN();
        $kelp   = VanillaBlocks::KELP();
        $air    = VanillaBlocks::AIR();

        // ── Dasar reruntuhan ──────────────────────────────────────────────
        $this->fillBox($world, $origin, 0, 0, 0, 9, 0, 10, $sand);

        // Pondasi batu yang sebagian hancur
        $this->fillBox($world, $origin, 1, 1, 1, 8, 1, 9, $cobble);

        // ── Dinding sebagian (tidak penuh, mensimulasikan reruntuhan) ─────
        // Dinding barat
        $this->fillBox($world, $origin, 0, 1, 0, 0, 7, 10, $stone);
        // Retakan (beberapa blok diganti mossy/cracked/air)
        $this->setBlock($world, $origin, 0, 3, 3, $mossy);
        $this->setBlock($world, $origin, 0, 5, 5, $cracked);
        $this->setBlock($world, $origin, 0, 6, 7, $air);  // lubang
        $this->setBlock($world, $origin, 0, 4, 8, $air);

        // Dinding utara (sebagian)
        $this->fillBox($world, $origin, 0, 1, 0, 9, 5, 0, $stone);
        $this->setBlock($world, $origin, 4, 3, 0, $air);  // lubang
        $this->setBlock($world, $origin, 5, 4, 0, $cracked);

        // Pilar (kolom) di sudut kanan
        for ($y = 1; $y <= 8; $y++) {
            $this->setBlock($world, $origin, 9, $y, 0, $mossy);
        }
        for ($y = 1; $y <= 5; $y++) {
            $this->setBlock($world, $origin, 9, $y, 10, $stone);
        }

        // Lengkungan/arch (baris batu di tengah)
        $this->fillBox($world, $origin, 0, 4, 5, 9, 4, 5, $cobble);
        $this->clearBox($world, $origin, 3, 1, 5, 6, 3, 5);   // bukaan arch

        // Sea Lantern di dinding (penerangan)
        $this->setBlock($world, $origin, 0, 3, 5, $seaLantern);
        $this->setBlock($world, $origin, 9, 2, 5, $seaLantern);

        // ── Air mengisi interior ──────────────────────────────────────────
        $this->fillBox($world, $origin, 1, 1, 1, 8, 6, 9, $water);

        // ── Kelp dekorasi ─────────────────────────────────────────────────
        $kelpPos = [[2, 1, 2], [5, 1, 7], [7, 1, 3], [3, 1, 9]];
        foreach ($kelpPos as [$kx, $ky, $kz]) {
            $this->setBlock($world, $origin, $kx, $ky, $kz, $kelp);
            $this->setBlock($world, $origin, $kx, $ky + 1, $kz, $kelp);
        }

        // ── Chest loot tersembunyi di bawah pasir ─────────────────────────
        $this->setBlock($world, $origin, 5, 0, 8, $gravel);   // tutup dengan gravel
        $chestPos = new Vector3(
            (int)($origin->x + 5), (int)($origin->y - 1), (int)($origin->z + 8)
        );
        $world->setBlock($chestPos, VanillaBlocks::CHEST());
        $lootTable = [
            VanillaItems::GOLD_INGOT(),
            VanillaItems::IRON_INGOT(),
            VanillaItems::EMERALD(),
            VanillaItems::FISHING_ROD(),
            VanillaItems::LEATHER_CHESTPLATE(),
            VanillaItems::COAL(),
            VanillaItems::HEART_OF_THE_SEA(),
        ];
        $this->fillChest($world, $chestPos, $lootTable, 2, 6);
    }
}
