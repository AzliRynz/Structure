<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

/**
 * Igloo
 * ─────────────────────────────────────────────────────────────────────────────
 * Igloo dari snow block berbentuk setengah lingkaran dengan ruang basement
 * yang berisi meja brewing, cauldron, dan 1 chest loot + tahanan zombie.
 *
 * Ukuran: 9x5x9
 */
class IglooStructure extends BaseStructure {

    public function getName(): string        { return "Igloo"; }
    public function getDescription(): string { return "Igloo salju dengan basement rahasia, brewing stand, dan chest"; }
    public function getWidth(): int          { return 9; }
    public function getHeight(): int         { return 5; }
    public function getDepth(): int          { return 9; }
    public function getAllowedBiomes(): array { return ["IcePlains", "ColdTaiga", "SnowyTundra"]; }
    public function hasLootRoom(): bool      { return true; }

    public function generate(World $world, Vector3 $origin): void {
        $snow  = VanillaBlocks::SNOW();
        $ice   = VanillaBlocks::ICE();
        $air   = VanillaBlocks::AIR();
        $stone = VanillaBlocks::STONE_BRICKS();
        $mossy = VanillaBlocks::MOSSY_STONE_BRICKS();
        $ladder= VanillaBlocks::LADDER();

        // ── Igloo (setengah bola) ─────────────────────────────────────────
        // Bangun lapisan demi lapisan (approx sphere)
        // Radius 4, center di (4,0,4)
        $cx = 4; $cy = 0; $cz = 4;
        for ($x = 0; $x <= 8; $x++) {
            for ($y = 0; $y <= 5; $y++) {
                for ($z = 0; $z <= 8; $z++) {
                    $dx = $x - $cx; $dy = $y - $cy; $dz = $z - $cz;
                    $dist = sqrt($dx*$dx + ($dy*1.5)*($dy*1.5) + $dz*$dz);
                    if ($dist >= 3.5 && $dist <= 4.2 && $y >= 0) {
                        $this->setBlock($world, $origin, $x, $y, $z, $snow);
                    } elseif ($dist < 3.5 && $y > 0) {
                        $this->setBlock($world, $origin, $x, $y, $z, $air);
                    }
                }
            }
        }

        // Lantai salju dalam
        $this->fillBox($world, $origin, 1, 0, 1, 7, 0, 7, $snow);

        // Pintu masuk (selatan, z=8)
        $this->clearBox($world, $origin, 3, 1, 7, 5, 2, 8);

        // Jendela es kecil
        $this->setBlock($world, $origin, 4, 2, 0, $ice);

        // ── Karpet & dekorasi interior ────────────────────────────────────
        $this->setBlock($world, $origin, 4, 1, 4, VanillaBlocks::RED_CARPET());
        $this->setBlock($world, $origin, 3, 1, 4, VanillaBlocks::RED_CARPET());
        $this->setBlock($world, $origin, 5, 1, 4, VanillaBlocks::RED_CARPET());
        // Tempat tidur (bed) di pojok
        $this->setBlock($world, $origin, 2, 1, 2, VanillaBlocks::RED_BED());
        // Furnace
        $this->setBlock($world, $origin, 6, 1, 2, VanillaBlocks::FURNACE());

        // ── Shaft ke basement (trapdoor + ladder) ────────────────────────
        // Lubang di lantai tengah-kanan
        $this->clearBox($world, $origin, 4, -5, 4, 4, 0, 4);
        // Trapdoor di atas lubang
        $this->setBlock($world, $origin, 4, 1, 4, VanillaBlocks::OAK_TRAPDOOR());
        // Ladder di shaft
        for ($ly = -4; $ly <= 0; $ly++) {
            $this->setBlock($world, $origin, 4, $ly, 4, $ladder);
        }

        // ── Basement ──────────────────────────────────────────────────────
        $this->hollowBox($world, $origin, 1, -8, 1, 7, -5, 7, $stone, $air);
        // Mozzy stone detail
        foreach ([[1,-6,1],[7,-7,1],[1,-5,7],[7,-5,7]] as [$bx,$by,$bz]) {
            $this->setBlock($world, $origin, $bx, $by, $bz, $mossy);
        }

        // Brewing Stand
        $this->setBlock($world, $origin, 3, -7, 3, VanillaBlocks::BREWING_STAND());
        // Cauldron berisi air
        $this->setBlock($world, $origin, 5, -7, 3, VanillaBlocks::CAULDRON());
        // Meja
        $this->setBlock($world, $origin, 3, -7, 5, VanillaBlocks::CRAFTING_TABLE());

        // Chest loot di basement
        $chestPos = new Vector3(
            (int)($origin->x + 5), (int)($origin->y - 7), (int)($origin->z + 5)
        );
        $world->setBlock($chestPos, VanillaBlocks::CHEST());
        $lootTable = [
            VanillaItems::GOLDEN_APPLE(),
            VanillaItems::IRON_INGOT(),
            VanillaItems::COAL(),
            VanillaItems::WHEAT(),
            VanillaItems::BREAD(),
            VanillaItems::EMERALD(),
        ];
        $this->fillChest($world, $chestPos, $lootTable, 3, 6);

        // Kandang tahanan (simulasi: iron bars sebagai penjara kecil)
        $this->hollowBox($world, $origin, 1, -8, 4, 3, -6, 6, VanillaBlocks::IRON_BARS());
        $this->clearBox($world, $origin, 2, -7, 5, 2, -7, 5);  // "pintu" penjara
    }
}
