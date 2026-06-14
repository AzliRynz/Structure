<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

/**
 * Witch Hut (Swamp Hut)
 * ─────────────────────────────────────────────────────────────────────────────
 * Rumah penyihir dari oak kayu di atas panggung, persis seperti vanilla.
 * Ada meja brewing, cauldron, dan kucing hitam (simulasi spawn).
 *
 * Ukuran: 7x7x9
 */
class WitchHut extends BaseStructure {

    public function getName(): string        { return "WitchHut"; }
    public function getDescription(): string { return "Rumah penyihir di rawa dengan brewing stand dan cauldron"; }
    public function getWidth(): int          { return 7; }
    public function getHeight(): int         { return 7; }
    public function getDepth(): int          { return 9; }
    public function getAllowedBiomes(): array { return ["Swamp"]; }
    public function hasLootRoom(): bool      { return false; }

    public function generate(World $world, Vector3 $origin): void {
        $oak   = VanillaBlocks::OAK_PLANKS();
        $log   = VanillaBlocks::OAK_LOG();
        $fence = VanillaBlocks::OAK_FENCE();
        $slab  = VanillaBlocks::OAK_SLAB();
        $air   = VanillaBlocks::AIR();

        // ── Tiang panggung (4 sudut) ──────────────────────────────────────
        $polePositions = [[0, 0], [6, 0], [0, 8], [6, 8]];
        foreach ($polePositions as [$px, $pz]) {
            for ($y = -3; $y <= 0; $y++) {
                $this->setBlock($world, $origin, $px, $y, $pz, $log);
            }
        }

        // ── Lantai (y=0) ──────────────────────────────────────────────────
        $this->fillBox($world, $origin, 0, 0, 0, 6, 0, 8, $oak);

        // ── Dinding (y=1..4) ──────────────────────────────────────────────
        $this->hollowBox($world, $origin, 0, 1, 0, 6, 4, 8, $oak, $air);

        // Pintu (bukaan 2 blok, sisi depan z=0)
        $this->clearBox($world, $origin, 2, 1, 0, 3, 3, 0);

        // ── Atap segitiga (y=5..7) ───────────────────────────────────────
        // Lapisan 5
        $this->fillBox($world, $origin, -1, 5, -1, 7, 5, 9, $slab);
        // Lapisan 6 (lebih kecil)
        $this->fillBox($world, $origin, 0, 6, 0, 6, 6, 8, $oak);
        // Puncak
        $this->fillBox($world, $origin, 2, 7, 0, 4, 7, 8, $log);

        // ── Interior: Brewing Stand ───────────────────────────────────────
        $this->setBlock($world, $origin, 3, 1, 5, VanillaBlocks::BREWING_STAND());
        // Cauldron
        $this->setBlock($world, $origin, 1, 1, 6, VanillaBlocks::CAULDRON());
        // Crafting Table
        $this->setBlock($world, $origin, 5, 1, 6, VanillaBlocks::CRAFTING_TABLE());
        // Flower Pot di jendela
        $this->setBlock($world, $origin, 1, 2, 1, VanillaBlocks::FLOWER_POT());
        // Pagar sebagai "rak" dekorasi
        $this->setBlock($world, $origin, 4, 1, 2, $fence);
        $this->setBlock($world, $origin, 5, 1, 2, $fence);

        // ── Dekorasi eksterior ────────────────────────────────────────────
        // Teras dengan pagar
        $this->fillBox($world, $origin, 0, 0, -2, 6, 0, -1, $oak);  // lantai teras
        $this->setBlock($world, $origin, 0, 1, -2, $fence);
        $this->setBlock($world, $origin, 6, 1, -2, $fence);
        $this->setBlock($world, $origin, 0, 1, -1, $fence);
        $this->setBlock($world, $origin, 6, 1, -1, $fence);
    }
}
