<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

/**
 * Jungle Temple
 * ─────────────────────────────────────────────────────────────────────────────
 * Candi dari mossy cobblestone & stone brick di hutan. Dua lantai dengan
 * teka-teki lever (puzzle), 2 dispenser jebakan panah, dan 2 chest loot.
 *
 * Ukuran: 12x13x15
 * Fitur:
 *  - Dinding mossy cobblestone
 *  - Teka-teki 3 lever yang harus diaktifkan urut (simulasi)
 *  - Dispenser berisi panah tersembunyi di dinding
 *  - Tripwire jebakan di koridor
 *  - 2 chest tersembunyi (1 di balik dinding, 1 di belakang altar)
 */
class JungleTemple extends BaseStructure {

    public function getName(): string        { return "JungleTemple"; }
    public function getDescription(): string { return "Candi hutan dengan puzzle lever, jebakan panah, dan 2 chest tersembunyi"; }
    public function getWidth(): int          { return 12; }
    public function getHeight(): int         { return 13; }
    public function getDepth(): int          { return 15; }
    public function getAllowedBiomes(): array { return ["Jungle"]; }
    public function hasLootRoom(): bool      { return true; }

    public function generate(World $world, Vector3 $origin): void {
        $mossy  = VanillaBlocks::MOSSY_COBBLESTONE();
        $stone  = VanillaBlocks::STONE_BRICKS();
        $cracked= VanillaBlocks::CRACKED_STONE_BRICKS();
        $mossSB = VanillaBlocks::MOSSY_STONE_BRICKS();
        $air    = VanillaBlocks::AIR();
        $vine   = VanillaBlocks::VINES();
        $cobble = VanillaBlocks::COBBLESTONE();

        // ── Struktur utama 2 lantai ───────────────────────────────────────
        // Lantai 1 (y=0..6) & Lantai 2 (y=7..13)
        $this->hollowBox($world, $origin, 0, 0, 0, 11, 12, 14, $mossy, $air);

        // Atap datar
        $this->fillBox($world, $origin, 0, 12, 0, 11, 12, 14, $stone);

        // Kolom interior (mossy stone bricks)
        foreach ([[2, 2], [9, 2], [2, 11], [9, 11]] as [$cx, $cz]) {
            for ($y = 1; $y <= 11; $y++) {
                $this->setBlock($world, $origin, $cx, $y, $cz, $mossSB);
            }
        }

        // Lantai dalam
        $this->fillBox($world, $origin, 1, 0, 1, 10, 0, 13, $cobble);
        // Lantai atas
        $this->fillBox($world, $origin, 1, 6, 1, 10, 6, 13, $stone);

        // ── Pintu masuk (sisi selatan, z=0) ──────────────────────────────
        $this->clearBox($world, $origin, 4, 1, 0, 7, 5, 0);  // Pintu besar

        // ── Tangga ke lantai 2 (sisi barat) ──────────────────────────────
        for ($i = 0; $i < 6; $i++) {
            $this->setBlock($world, $origin, 1, $i + 1, 12 - $i, VanillaBlocks::COBBLESTONE_STAIRS());
        }

        // ── Teka-teki Lever (puzzle room di sisi timur lantai 1) ──────────
        // 3 lever di dinding timur (x=10)
        for ($li = 0; $li < 3; $li++) {
            $this->setBlock($world, $origin, 10, 2 + $li, 8, VanillaBlocks::LEVER());
        }
        // Redstone wiring (di dalam dinding, simulasi)
        $this->setBlock($world, $origin, 10, 1, 8, VanillaBlocks::REDSTONE_WIRE());
        // Target: pintu rahasia (x=10, y=1..3, z=11..12)
        $this->clearBox($world, $origin, 10, 1, 11, 10, 3, 12); // dibuka jika puzzle selesai

        // ── Dispenser jebakan panah ───────────────────────────────────────
        // Dispenser 1 di dinding utara lantai 1
        $disp1 = VanillaBlocks::DISPENSER();
        $this->setBlock($world, $origin, 1, 2, 6, $disp1);
        // Tripwire di tengah koridor
        $this->setBlock($world, $origin, 5, 1, 6, VanillaBlocks::TRIPWIRE());
        $this->setBlock($world, $origin, 5, 1, 7, VanillaBlocks::TRIPWIRE_HOOK());
        // Dispenser 2 di lantai atas
        $this->setBlock($world, $origin, 10, 8, 6, $disp1);

        // Isi dispenser dengan panah (simulasi — tile entity)
        // (Catatan: isi dispenser perlu TileEntity, ini hanya placeholder blok)

        // ── Chest loot ───────────────────────────────────────────────────
        $lootTable = [
            VanillaItems::DIAMOND(),
            VanillaItems::EMERALD(),
            VanillaItems::GOLD_INGOT(),
            VanillaItems::IRON_INGOT(),
            VanillaItems::ENCHANTED_BOOK(),
            VanillaItems::BAMBOO(),
            VanillaItems::SADDLE(),
            VanillaItems::BONE(),
        ];

        // Chest 1 — tersembunyi di balik dinding puzzle
        $chest1Pos = new Vector3(
            (int)($origin->x + 10), (int)($origin->y + 2), (int)($origin->z + 13)
        );
        $world->setBlock($chest1Pos, VanillaBlocks::CHEST());
        $this->fillChest($world, $chest1Pos, $lootTable, 3, 7);

        // Chest 2 — di altar lantai 2
        $chest2Pos = new Vector3(
            (int)($origin->x + 5), (int)($origin->y + 7), (int)($origin->z + 12)
        );
        $world->setBlock($chest2Pos, VanillaBlocks::CHEST());
        $this->fillChest($world, $chest2Pos, $lootTable, 3, 7);

        // ── Dekorasi: vines di dinding ────────────────────────────────────
        $vinePositions = [
            [0, 5, 3], [0, 5, 7], [11, 4, 5], [11, 8, 10],
            [3, 11, 0], [7, 10, 14]
        ];
        foreach ($vinePositions as [$vx, $vy, $vz]) {
            $this->setBlock($world, $origin, $vx, $vy, $vz, $vine);
        }

        // Retakan & mossy detail di dinding
        $crackPositions = [
            [1, 3, 4], [10, 7, 8], [5, 11, 0], [8, 2, 14]
        ];
        foreach ($crackPositions as [$cx, $cy, $cz]) {
            $this->setBlock($world, $origin, $cx, $cy, $cz, $cracked);
            $this->setBlock($world, $origin, $cx + 1, $cy, $cz, $mossSB);
        }
    }
}
