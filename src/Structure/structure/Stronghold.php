<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

/**
 * Stronghold
 * ─────────────────────────────────────────────────────────────────────────────
 * Benteng bawah tanah dari stone brick dengan End Portal, perpustakaan,
 * dan beberapa kamar dengan chest loot.
 *
 * Ukuran: 30x15x40
 * Fitur:
 *  - Ruang End Portal (frame enderite dengan mata ender)
 *  - Perpustakaan 2 lantai (bookshelves)
 *  - 3 chest loot
 *  - Koridor panjang dengan pilar
 *  - Kamar penjara (iron bar)
 */
class Stronghold extends BaseStructure {

    public function getName(): string        { return "Stronghold"; }
    public function getDescription(): string { return "Benteng bawah tanah dengan End Portal, perpustakaan, dan 3 chest"; }
    public function getWidth(): int          { return 30; }
    public function getHeight(): int         { return 15; }
    public function getDepth(): int          { return 40; }
    public function getAllowedBiomes(): array { return []; }
    public function hasLootRoom(): bool      { return true; }

    public function generate(World $world, Vector3 $origin): void {
        $stone  = VanillaBlocks::STONE_BRICKS();
        $mossy  = VanillaBlocks::MOSSY_STONE_BRICKS();
        $cracked= VanillaBlocks::CRACKED_STONE_BRICKS();
        $iron   = VanillaBlocks::IRON_BARS();
        $book   = VanillaBlocks::BOOKSHELF();
        $air    = VanillaBlocks::AIR();
        $torch  = VanillaBlocks::TORCH();
        $lamp   = VanillaBlocks::REDSTONE_LAMP();
        $portal = VanillaBlocks::END_PORTAL_FRAME();
        $slab   = VanillaBlocks::STONE_BRICK_SLAB();
        $stair  = VanillaBlocks::STONE_BRICK_STAIRS();
        $water  = VanillaBlocks::WATER();

        // ── KORIDOR UTAMA (y=0..4, z=0..39) ──────────────────────────────
        $this->hollowBox($world, $origin, 0, 0, 0, 29, 14, 39, $stone, $air);

        // Pilar di koridor setiap 5 blok
        for ($pz = 4; $pz <= 35; $pz += 5) {
            $this->fillBox($world, $origin, 5, 1, $pz, 5, 3, $pz, $mossy);
            $this->fillBox($world, $origin, 24, 1, $pz, 24, 3, $pz, $mossy);
            $this->setBlock($world, $origin, 5, 4, $pz, $torch);
            $this->setBlock($world, $origin, 24, 4, $pz, $torch);
        }

        // ── RUANG END PORTAL (15x9, di z=28..36) ─────────────────────────
        $this->hollowBox($world, $origin, 7, 0, 27, 22, 8, 38, $stone, $air);

        // Frame End Portal (5x5 portal, 12 frame blok mengelilingi ruang 3x3)
        // North row (z=32, x=11..17)
        $this->fillBox($world, $origin, 11, 1, 32, 17, 1, 32, $portal);
        // South row
        $this->fillBox($world, $origin, 11, 1, 36, 17, 1, 36, $portal);
        // West col
        $this->fillBox($world, $origin, 11, 1, 33, 11, 1, 35, $portal);
        // East col
        $this->fillBox($world, $origin, 17, 1, 33, 17, 1, 35, $portal);

        // Air di tengah portal (void-like, simulasi dengan air dulu)
        $this->fillBox($world, $origin, 12, 1, 33, 16, 1, 35, $water);

        // Tangga turun ke portal
        for ($si = 0; $si < 5; $si++) {
            $this->setBlock($world, $origin, 14, -$si, 27 - $si, $stair);
        }

        // Silverfish spawner di tengah (simulasi blok)
        $this->setBlock($world, $origin, 14, 0, 34, VanillaBlocks::MONSTER_SPAWNER());
        $this->setBlock($world, $origin, 14, 2, 34, $lamp);   // lampu di atas spawner

        // ── PERPUSTAKAAN (z=5..16, x=0..12) ──────────────────────────────
        $this->hollowBox($world, $origin, 1, 0, 5, 12, 9, 16, $stone, $air);

        // Bookshelves
        for ($by = 1; $by <= 7; $by += 2) {
            $this->fillBox($world, $origin, 2, $by, 6, 2, $by + 1, 14, $book);
            $this->fillBox($world, $origin, 11, $by, 6, 11, $by + 1, 14, $book);
            $this->fillBox($world, $origin, 3, $by, 6, 10, $by + 1, 6, $book);
        }

        // Lantai atas perpustakaan
        $this->fillBox($world, $origin, 2, 5, 6, 11, 5, 14, $stone);
        // Tangga ke lantai atas buku
        for ($si = 0; $si < 5; $si++) {
            $this->setBlock($world, $origin, 11, $si + 1, 15 - $si, $stair);
        }

        // Meja lectern
        $this->setBlock($world, $origin, 6, 1, 10, VanillaBlocks::LECTERN());
        $this->setBlock($world, $origin, 6, 6, 10, VanillaBlocks::LECTERN());

        // ── KAMAR PENJARA (z=18..23) ──────────────────────────────────────
        $this->hollowBox($world, $origin, 14, 0, 18, 28, 5, 24, $stone, $air);
        // Sel dengan iron bars
        for ($cell = 0; $cell < 3; $cell++) {
            $bx = 16 + $cell * 4;
            $this->hollowBox($world, $origin, $bx, 1, 19, $bx + 2, 4, 22, $iron);
            $this->clearBox($world, $origin, $bx, 1, 19, $bx, 2, 19); // pintu sel
        }
        $this->setBlock($world, $origin, 20, 0, 21, VanillaBlocks::MONSTER_SPAWNER()); // spawner

        // ── 3 CHEST LOOT ─────────────────────────────────────────────────
        $lootTables = [
            // Chest 1 di kamar portal
            [
                VanillaItems::ENDER_PEARL(),
                VanillaItems::IRON_INGOT(),
                VanillaItems::GOLD_INGOT(),
                VanillaItems::BREAD(),
                VanillaItems::APPLE(),
                VanillaItems::IRON_CHESTPLATE(),
            ],
            // Chest 2 di perpustakaan
            [
                VanillaItems::ENCHANTED_BOOK(),
                VanillaItems::COMPASS(),
                VanillaItems::PAPER(),
                VanillaItems::BOOK(),
                VanillaItems::EMERALD(),
            ],
            // Chest 3 di kamar penjara
            [
                VanillaItems::IRON_SWORD(),
                VanillaItems::IRON_HELMET(),
                VanillaItems::ROTTEN_FLESH(),
                VanillaItems::BONE(),
                VanillaItems::COAL(),
            ],
        ];
        $chestPositions = [
            [(int)($origin->x + 20), (int)($origin->y + 1), (int)($origin->z + 29)],
            [(int)($origin->x + 7),  (int)($origin->y + 1), (int)($origin->z + 12)],
            [(int)($origin->x + 27), (int)($origin->y + 1), (int)($origin->z + 21)],
        ];
        foreach ($chestPositions as $i => [$cx, $cy, $cz]) {
            $cp = new Vector3($cx, $cy, $cz);
            $world->setBlock($cp, VanillaBlocks::CHEST());
            $this->fillChest($world, $cp, $lootTables[$i], 3, 8);
        }
    }
}
