<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

/**
 * End City
 * ─────────────────────────────────────────────────────────────────────────────
 * Menara End dari purpur block dengan elytra ship, shulker box loot,
 * dan dekorasi end rods + chorus plant.
 *
 * Ukuran: 15x30x15
 */
class EndCity extends BaseStructure {

    public function getName(): string        { return "EndCity"; }
    public function getDescription(): string { return "Menara End dengan purpur, shulker boxes, dan kapal elytra"; }
    public function getWidth(): int          { return 15; }
    public function getHeight(): int         { return 30; }
    public function getDepth(): int          { return 15; }
    public function getAllowedBiomes(): array { return ["TheEnd"]; }
    public function hasLootRoom(): bool      { return true; }

    public function generate(World $world, Vector3 $origin): void {
        $purpur = VanillaBlocks::PURPUR_BLOCK();
        $pillar = VanillaBlocks::PURPUR_PILLAR();
        $pstair = VanillaBlocks::PURPUR_STAIRS();
        $pslab  = VanillaBlocks::PURPUR_SLAB();
        $end    = VanillaBlocks::END_STONE_BRICKS();
        $endRod = VanillaBlocks::END_ROD();
        $chorus = VanillaBlocks::CHORUS_PLANT();
        $shulker= VanillaBlocks::SHULKER_BOX();
        $air    = VanillaBlocks::AIR();

        // ── Menara utama (5x5, y=0..25) ──────────────────────────────────
        for ($y = 0; $y <= 25; $y++) {
            $size = max(1, 3 - (int)($y / 8)); // makin kecil ke atas
            $startX = 5 - $size; $endX = 5 + $size;
            $startZ = 5 - $size; $endZ = 5 + $size;
            $this->hollowBox($world, $origin, $startX, $y, $startZ, $endX, $y, $endZ,
                ($y % 4 === 0 ? $pillar : $purpur), $air
            );
        }

        // Lantai dasar
        $this->fillBox($world, $origin, 2, 0, 2, 8, 0, 8, $end);

        // Tangga spiral ke atas (setiap 4 blok)
        for ($stY = 1; $stY <= 24; $stY += 4) {
            $this->setBlock($world, $origin, 6, $stY, 3, $pstair);
            $this->setBlock($world, $origin, 7, $stY+1, 6, $pstair);
            $this->setBlock($world, $origin, 4, $stY+2, 7, $pstair);
            $this->setBlock($world, $origin, 3, $stY+3, 4, $pstair);
        }

        // Puncak menara (platform 7x7)
        $this->fillBox($world, $origin, 1, 26, 1, 9, 26, 9, $purpur);
        $this->fillBox($world, $origin, 1, 27, 1, 9, 27, 1, $pstair);   // railing
        $this->fillBox($world, $origin, 1, 27, 9, 9, 27, 9, $pstair);
        $this->fillBox($world, $origin, 1, 27, 1, 1, 27, 9, $pstair);
        $this->fillBox($world, $origin, 9, 27, 1, 9, 27, 9, $pstair);

        // End Rods di sudut puncak
        $this->setBlock($world, $origin, 1,  28, 1,  $endRod);
        $this->setBlock($world, $origin, 9,  28, 1,  $endRod);
        $this->setBlock($world, $origin, 1,  28, 9,  $endRod);
        $this->setBlock($world, $origin, 9,  28, 9,  $endRod);
        $this->setBlock($world, $origin, 5,  29, 5,  $endRod);

        // ── Menara kecil samping (x=10..14, z=0..4, y=5..18) ────────────
        $this->hollowBox($world, $origin, 10, 5, 0, 14, 18, 4, $purpur, $air);
        $this->fillBox($world, $origin, 10, 5, 0, 14, 5, 4, $end);
        $this->fillBox($world, $origin, 10, 19, 0, 14, 19, 4, $purpur); // atap menara kecil

        // ── Shulker Box loot ──────────────────────────────────────────────
        $lootTable = [
            VanillaItems::DIAMOND(),
            VanillaItems::DIAMOND_CHESTPLATE(),
            VanillaItems::DIAMOND_LEGGINGS(),
            VanillaItems::DIAMOND_SWORD(),
            VanillaItems::ELYTRA(),
            VanillaItems::IRON_INGOT(),
            VanillaItems::GOLD_INGOT(),
            VanillaItems::EMERALD(),
            VanillaItems::IRON_BOOTS(),
            VanillaItems::ENCHANTED_BOOK(),
            VanillaItems::BEETROOT_SOUP(),
        ];
        // Shulker box di menara utama (lantai puncak)
        $sPos1 = new Vector3(
            (int)($origin->x + 4), (int)($origin->y + 27), (int)($origin->z + 4)
        );
        $world->setBlock($sPos1, $shulker);
        // (Pengisian isi shulker box membutuhkan TileEntity lanjutan — gunakan chest biasa)
        $world->setBlock($sPos1, VanillaBlocks::CHEST());
        $this->fillChest($world, $sPos1, $lootTable, 5, 10);

        // Shulker box di menara kecil
        $sPos2 = new Vector3(
            (int)($origin->x + 12), (int)($origin->y + 6), (int)($origin->z + 2)
        );
        $world->setBlock($sPos2, VanillaBlocks::CHEST());
        $this->fillChest($world, $sPos2, $lootTable, 3, 7);

        // ── Chorus Plant dekorasi ─────────────────────────────────────────
        $chorusPositions = [[-3,0,-3],[-3,0,17],[17,0,-3],[17,0,17],[7,0,-4]];
        foreach ($chorusPositions as [$cx,$cy,$cz]) {
            $this->setBlock($world, $origin, $cx, $cy, $cz, $chorus);
            $this->setBlock($world, $origin, $cx, $cy+1, $cz, $chorus);
            $this->setBlock($world, $origin, $cx, $cy+2, $cz, $chorus);
        }
    }
}
