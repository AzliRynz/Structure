<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

/**
 * Nether Fortress
 * ─────────────────────────────────────────────────────────────────────────────
 * Benteng Nether dari Nether Brick dengan jembatan, ruang Blaze Spawner,
 * koridor yang panjang, dan chest berisi loot Nether.
 *
 * Ukuran: 30x15x30
 */
class NetherFortress extends BaseStructure {

    public function getName(): string        { return "NetherFortress"; }
    public function getDescription(): string { return "Benteng nether dengan jembatan, blaze spawner, dan loot nether"; }
    public function getWidth(): int          { return 30; }
    public function getHeight(): int         { return 15; }
    public function getDepth(): int          { return 30; }
    public function getAllowedBiomes(): array { return ["Nether", "SoulSandValley", "CrimsonForest"]; }
    public function hasLootRoom(): bool      { return true; }

    public function generate(World $world, Vector3 $origin): void {
        $nbrick = VanillaBlocks::NETHER_BRICKS();
        $nfence = VanillaBlocks::NETHER_BRICK_FENCE();
        $nstair = VanillaBlocks::NETHER_BRICK_STAIRS();
        $nether = VanillaBlocks::NETHERRACK();
        $soul   = VanillaBlocks::SOUL_SAND();
        $lava   = VanillaBlocks::LAVA();
        $air    = VanillaBlocks::AIR();

        // ── Jembatan utama (z=0..29, y=0..3, x=10..19) ───────────────────
        $this->fillBox($world, $origin, 10, 0, 0, 19, 0, 29, $nbrick);
        $this->fillBox($world, $origin, 10, 1, 0, 10, 3, 29, $nbrick);   // dinding kiri
        $this->fillBox($world, $origin, 19, 1, 0, 19, 3, 29, $nbrick);   // dinding kanan
        // Pagar di atas dinding
        $this->fillBox($world, $origin, 10, 4, 0, 10, 4, 29, $nfence);
        $this->fillBox($world, $origin, 19, 4, 0, 19, 4, 29, $nfence);

        // Pilar jembatan setiap 5 blok
        for ($pz = 0; $pz <= 29; $pz += 5) {
            for ($y = -4; $y <= 0; $y++) {
                $this->setBlock($world, $origin, 12, $y, $pz, $nbrick);
                $this->setBlock($world, $origin, 17, $y, $pz, $nbrick);
            }
        }

        // ── Menara 1 di ujung utara (0-9, 5-14, 0-9) ─────────────────────
        $this->hollowBox($world, $origin, 0, 0, 0, 9, 9, 9, $nbrick, $air);
        $this->fillBox($world, $origin, 0, 0, 0, 9, 0, 9, $nbrick); // lantai
        // Puncak menara
        $this->fillBox($world, $origin, 0, 10, 0, 9, 10, 9, $nbrick);
        $this->fillBox($world, $origin, -1, 10, -1, 10, 10, 10, $nfence); // pagar keliling atap
        // Blaze Spawner Room di menara 1
        $this->setBlock($world, $origin, 4, 1, 4, VanillaBlocks::MONSTER_SPAWNER());
        $this->setBlock($world, $origin, 4, 1, 3, $soul);
        $this->setBlock($world, $origin, 4, 1, 5, $soul);

        // ── Menara 2 di ujung selatan (20-29, 5-14, 20-29) ──────────────
        $this->hollowBox($world, $origin, 20, 0, 20, 29, 9, 29, $nbrick, $air);
        $this->fillBox($world, $origin, 20, 0, 20, 29, 0, 29, $nbrick);
        $this->fillBox($world, $origin, 20, 10, 20, 29, 10, 29, $nbrick);
        // Kedua Blaze Spawner
        $this->setBlock($world, $origin, 24, 1, 24, VanillaBlocks::MONSTER_SPAWNER());

        // ── Koridor silang (x=0..29, y=0, z=13..15) ─────────────────────
        $this->fillBox($world, $origin, 0, 0, 13, 29, 0, 15, $nbrick);
        $this->fillBox($world, $origin, 0, 1, 13, 0, 3, 15, $nbrick);
        $this->fillBox($world, $origin, 29, 1, 13, 29, 3, 15, $nbrick);
        // Atap koridor
        $this->fillBox($world, $origin, 0, 4, 13, 29, 4, 15, $nbrick);
        // Bukaan ke jembatan
        $this->clearBox($world, $origin, 11, 1, 13, 18, 3, 15);

        // ── Lava trap di pojok ────────────────────────────────────────────
        $this->fillBox($world, $origin, 0, -1, 0, 9, -1, 9, $lava);

        // ── Tangga ke koridor bawah ───────────────────────────────────────
        for ($si = 0; $si < 5; $si++) {
            $this->setBlock($world, $origin, 11 + $si, -$si, 13, $nstair);
        }

        // ── 3 Chest loot Nether ────────────────────────────────────────────
        $lootTable = [
            VanillaItems::BLAZE_ROD(),
            VanillaItems::NETHER_WART(),
            VanillaItems::GOLD_INGOT(),
            VanillaItems::IRON_INGOT(),
            VanillaItems::SADDLE(),
            VanillaItems::DIAMOND_HORSE_ARMOR(),
            VanillaItems::IRON_HORSE_ARMOR(),
            VanillaItems::FLINT_AND_STEEL(),
            VanillaItems::GOLDEN_SWORD(),
        ];
        $chestPos = [
            [5,  1,  7 ],
            [24, 1,  22],
            [14, 1,  14],
        ];
        foreach ($chestPos as [$cx, $cy, $cz]) {
            $cp = new Vector3(
                (int)($origin->x + $cx),
                (int)($origin->y + $cy),
                (int)($origin->z + $cz)
            );
            $world->setBlock($cp, VanillaBlocks::CHEST());
            $this->fillChest($world, $cp, $lootTable, 4, 8);
        }

        // Wart farm dekorasi di ujung koridor
        $this->fillBox($world, $origin, 21, 1, 13, 28, 1, 15, $soul);
        foreach ([[22,2,14],[25,2,13],[27,2,15]] as [$wx,$wy,$wz]) {
            $this->setBlock($world, $origin, $wx, $wy, $wz, VanillaBlocks::NETHER_WART());
        }
    }
}
