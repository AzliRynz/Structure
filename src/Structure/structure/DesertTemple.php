<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\VanillaItems;

/**
 * Desert Temple (Pyramid)
 * ─────────────────────────────────────────────────────────────────────────────
 * Struktur piramid besar dari sandstone, dengan 4 kamar loot tersembunyi
 * di bawah altar TNT, persis seperti di Minecraft vanilla.
 *
 * Ukuran: 21x15x21 (lebar x tinggi x dalam)
 * Fitur:
 *  - Piramid berlapis sandstone & chiseled sandstone
 *  - Pola warna orange & blue terracotta di lantai
 *  - Ruang bawah tanah dengan 4 dada (chest) loot
 *  - Pressure plate TNT trap (9 TNT blok)
 *  - Tangga ke bawah di sisi piramid
 */
class DesertTemple extends BaseStructure {

    public function getName(): string        { return "DesertTemple"; }
    public function getDescription(): string { return "Piramid gurun dengan 4 kamar harta dan jebakan TNT"; }
    public function getWidth(): int          { return 21; }
    public function getHeight(): int         { return 15; }
    public function getDepth(): int          { return 21; }
    public function getAllowedBiomes(): array { return ["Desert"]; }
    public function hasLootRoom(): bool      { return true; }

    public function generate(World $world, Vector3 $origin): void {
        $sand   = VanillaBlocks::SANDSTONE();
        $chisel = VanillaBlocks::CHISELED_SANDSTONE();
        $smooth = VanillaBlocks::SMOOTH_SANDSTONE();
        $air    = VanillaBlocks::AIR();
        $orange = VanillaBlocks::TERRACOTTA()->setColor(DyeColor::ORANGE());
        $blue   = VanillaBlocks::TERRACOTTA()->setColor(DyeColor::BLUE());
        $stairs = VanillaBlocks::SANDSTONE_STAIRS();

        // ── Layer by layer dari bawah ke atas ────────────────────────────
        // Dasar piramid (layer 0) — ukuran 21x21
        $this->fillBox($world, $origin, 0, 0, 0, 20, 0, 20, $sand);

        // Bangunan piramid berlapis (mengecil ke atas setiap 2 blok)
        for ($layer = 0; $layer <= 9; $layer++) {
            $offset = $layer;
            $y      = $layer + 1;
            if ($offset >= 10) break;
            $this->hollowBox($world, $origin,
                $offset, $y, $offset,
                20 - $offset, $y, 20 - $offset,
                $sand
            );
        }

        // Interior kosong (ruang dalam piramid)
        $this->clearBox($world, $origin, 1, 1, 1, 19, 10, 19);

        // Dinding luar tebal 2 blok, tinggi 15
        for ($y = 1; $y <= 14; $y++) {
            $this->fillBox($world, $origin, 0, $y, 0, 1, $y, 20, $sand);
            $this->fillBox($world, $origin, 19, $y, 0, 20, $y, 20, $sand);
            $this->fillBox($world, $origin, 0, $y, 0, 20, $y, 1, $sand);
            $this->fillBox($world, $origin, 0, $y, 19, 20, $y, 20, $sand);
        }

        // Puncak chiseled sandstone
        $this->setBlock($world, $origin, 10, 11, 10, $chisel);

        // ── Pintu masuk (bukaan 3 lebar, 4 tinggi) di sisi utara ─────────
        $this->clearBox($world, $origin, 9, 1, 0, 11, 4, 1);

        // Pola lantai: orange & blue terracotta membentuk salib
        // Tengah biru (posisi pressure plate bawah)
        $this->setBlock($world, $origin, 10, 0, 10, $blue);
        // 4 sudut kamar loot
        foreach ([
            [2, 0, 2], [18, 0, 2], [2, 0, 18], [18, 0, 18]
        ] as [$cx, $cy, $cz]) {
            $this->setBlock($world, $origin, $cx, $cy, $cz, $orange);
        }

        // ── Ruang bawah tanah (cellar) ────────────────────────────────────
        // Gali shaft ke bawah dari tengah
        $this->clearBox($world, $origin, 9, -9, 9, 11, -1, 11);

        // Lantai cellar
        $this->fillBox($world, $origin, 7, -11, 7, 13, -11, 13, $smooth);
        // Dinding cellar
        $this->hollowBox($world, $origin, 7, -10, 7, 13, -1, 13, $sand, $air);

        // ── Jebakan TNT (9 TNT tersusun 3x3 di bawah pressure plate) ─────
        $this->fillBox($world, $origin, 9, -6, 9, 11, -4, 11, VanillaBlocks::TNT());
        // Pressure plate di atas TNT (level cellar floor + 1)
        $this->setBlock($world, $origin, 10, -3, 10, VanillaBlocks::STONE_PRESSURE_PLATE());

        // ── 4 Chest loot di sudut cellar ─────────────────────────────────
        $chestPositions = [
            [8, -10, 8], [12, -10, 8], [8, -10, 12], [12, -10, 12]
        ];
        $lootTable = [
            VanillaItems::DIAMOND(),
            VanillaItems::EMERALD(),
            VanillaItems::GOLD_INGOT(),
            VanillaItems::IRON_INGOT(),
            VanillaItems::ROTTEN_FLESH(),
            VanillaItems::BONE(),
            VanillaItems::SADDLE(),
            VanillaItems::ENCHANTED_GOLDEN_APPLE(),
        ];
        foreach ($chestPositions as [$cx, $cy, $cz]) {
            $chestPos = new Vector3(
                (int)($origin->x + $cx),
                (int)($origin->y + $cy),
                (int)($origin->z + $cz)
            );
            $world->setBlock($chestPos, VanillaBlocks::CHEST());
            $this->fillChest($world, $chestPos, $lootTable, 4, 9);
        }

        // ── Dekorasi: pilar chiseled di sudut dalam ───────────────────────
        foreach ([[2, 2], [18, 2], [2, 18], [18, 18]] as [$px, $pz]) {
            for ($y = 1; $y <= 8; $y++) {
                $this->setBlock($world, $origin, $px, $y, $pz, $chisel);
            }
        }
    }
}
