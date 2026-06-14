<?php

declare(strict_types=1);

namespace Structure\structure;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

/**
 * Village (Plains variant)
 * ─────────────────────────────────────────────────────────────────────────────
 * Desa kecil dengan: rumah petani, gereja kecil, pandai besi, dan sumur.
 * Menggunakan oak/cobblestone persis seperti desa dataran vanilla.
 *
 * Ukuran: 32x10x32
 */
class VillageStructure extends BaseStructure {

    public function getName(): string        { return "Village"; }
    public function getDescription(): string { return "Desa dataran dengan rumah petani, gereja, pandai besi, dan sumur"; }
    public function getWidth(): int          { return 32; }
    public function getHeight(): int         { return 10; }
    public function getDepth(): int          { return 32; }
    public function getAllowedBiomes(): array { return ["Plains", "Sunflower Plains"]; }
    public function hasLootRoom(): bool      { return true; }

    public function generate(World $world, Vector3 $origin): void {
        $oak   = VanillaBlocks::OAK_PLANKS();
        $log   = VanillaBlocks::OAK_LOG();
        $cobble= VanillaBlocks::COBBLESTONE();
        $glass = VanillaBlocks::GLASS_PANE();
        $fence = VanillaBlocks::OAK_FENCE();
        $path  = VanillaBlocks::DIRT_PATH();
        $door  = VanillaBlocks::OAK_DOOR();
        $air   = VanillaBlocks::AIR();
        $slab  = VanillaBlocks::COBBLESTONE_SLAB();
        $torch = VanillaBlocks::TORCH();
        $grass = VanillaBlocks::GRASS();

        // ── Jalan utama ───────────────────────────────────────────────────
        $this->fillBox($world, $origin, 13, 0, 0, 15, 0, 31, $path);   // vertikal
        $this->fillBox($world, $origin, 0, 0, 13, 31, 0, 15, $path);   // horizontal

        // ── Sumur (center 14,0,14) ────────────────────────────────────────
        $this->hollowBox($world, $origin, 12, 0, 12, 16, 3, 16, $cobble, $air);
        $this->fillBox($world, $origin, 13, -2, 13, 15, 0, 15, VanillaBlocks::WATER());
        // Atap sumur (pagar + kayu)
        $this->setBlock($world, $origin, 12, 4, 12, $fence);
        $this->setBlock($world, $origin, 16, 4, 12, $fence);
        $this->setBlock($world, $origin, 12, 4, 16, $fence);
        $this->setBlock($world, $origin, 16, 4, 16, $fence);
        $this->fillBox($world, $origin, 12, 5, 12, 16, 5, 12, $log);
        $this->fillBox($world, $origin, 12, 5, 16, 16, 5, 16, $log);
        $this->setBlock($world, $origin, 14, 6, 12, $log);  // tiang tengah
        $this->setBlock($world, $origin, 14, 6, 16, $log);
        $this->setBlock($world, $origin, 14, 7, 14, VanillaBlocks::CHAIN());

        // ── RUMAH PETANI (pojok barat laut, 0-9 x 0-11) ──────────────────
        $this->buildHouse($world, $origin, 0, 0, 0, 9, 0, 11, $oak, $log, $cobble, $glass);
        // Ladang
        $this->fillBox($world, $origin, 0, 0, 12, 9, 0, 20, VanillaBlocks::FARMLAND());
        foreach ([[2,0,13],[5,0,14],[8,0,16],[3,0,18],[7,0,19]] as [$wx,$wy,$wz]) {
            $this->setBlock($world, $origin, $wx, $wy + 1, $wz, VanillaBlocks::WHEAT());
        }

        // ── GEREJA KECIL (pojok timur laut, 18-31 x 0-12) ────────────────
        $this->buildChurch($world, $origin, 18, 0, 0, $cobble, $log, $oak, $glass);

        // ── PANDAI BESI / SMITHY (barat daya, 0-9 x 18-28) ───────────────
        $this->buildSmithy($world, $origin, 0, 0, 18, $cobble, $log, $oak);
        // Chest pandai besi
        $smithyChest = new Vector3(
            (int)($origin->x + 7), (int)($origin->y + 1), (int)($origin->z + 22)
        );
        $world->setBlock($smithyChest, VanillaBlocks::CHEST());
        $smithyLoot = [
            VanillaItems::IRON_INGOT(),
            VanillaItems::IRON_SWORD(),
            VanillaItems::IRON_CHESTPLATE(),
            VanillaItems::IRON_BOOTS(),
            VanillaItems::IRON_PICKAXE(),
            VanillaItems::BREAD(),
            VanillaItems::APPLE(),
            VanillaItems::OBSIDIAN(),
            VanillaItems::SADDLE(),
            VanillaItems::GOLD_INGOT(),
        ];
        $this->fillChest($world, $smithyChest, $smithyLoot, 5, 9);

        // ── RUMAH BIASA 2 (timur daya, 18-28 x 18-28) ────────────────────
        $this->buildHouse($world, $origin, 18, 0, 18, 28, 0, 28, $oak, $log, $cobble, $glass);

        // ── Torches di jalan ─────────────────────────────────────────────
        $torchPositions = [
            [10,1,7],[10,1,21],[21,1,7],[21,1,21],[14,1,5],[14,1,25],[5,1,14],[25,1,14]
        ];
        foreach ($torchPositions as [$tx,$ty,$tz]) {
            $this->setBlock($world, $origin, $tx, $ty, $tz, $torch);
        }
    }

    /** Helper: bangun rumah generik */
    private function buildHouse(World $world, Vector3 $origin,
        int $x1, int $y1, int $z1, int $x2, int $y2, int $z2,
        $plank, $log, $cobble, $glass): void
    {
        $air = VanillaBlocks::AIR();
        // Lantai
        $this->fillBox($world, $origin, $x1, $y1, $z1, $x2, $y1, $z2, $cobble);
        // Dinding
        $this->hollowBox($world, $origin, $x1, $y1+1, $z1, $x2, $y1+4, $z2, $plank, $air);
        // Log corner
        foreach ([[$x1,$z1],[$x2,$z1],[$x1,$z2],[$x2,$z2]] as [$lx,$lz]) {
            for ($y = $y1+1; $y <= $y1+4; $y++) {
                $this->setBlock($world, $origin, $lx, $y, $lz, $log);
            }
        }
        // Atap
        $this->fillBox($world, $origin, $x1-1, $y1+5, $z1-1, $x2+1, $y1+5, $z2+1, $cobble);
        // Jendela di tengah sisi
        $midX = (int)(($x1+$x2)/2); $midZ = (int)(($z1+$z2)/2);
        $this->setBlock($world, $origin, $midX, $y1+2, $z1, $glass);
        $this->setBlock($world, $origin, $midX, $y1+2, $z2, $glass);
        $this->setBlock($world, $origin, $x1, $y1+2, $midZ, $glass);
        // Pintu
        $this->clearBox($world, $origin, $midX, $y1+1, $z1, $midX, $y1+2, $z1);
    }

    /** Helper: bangun gereja */
    private function buildChurch(World $world, Vector3 $origin, int $sx, int $sy, int $sz,
        $cobble, $log, $plank, $glass): void
    {
        $air = VanillaBlocks::AIR();
        // Badan gereja
        $this->hollowBox($world, $origin, $sx, $sy, $sz, $sx+11, $sy+6, $sz+11, $cobble, $air);
        $this->fillBox($world, $origin, $sx, $sy, $sz, $sx+11, $sy, $sz+11, $cobble);
        // Menara di tengah (lebih tinggi)
        $this->hollowBox($world, $origin, $sx+4, $sy, $sz+4, $sx+7, $sy+10, $sz+7, $plank, $air);
        // Salib di menara
        $this->setBlock($world, $origin, $sx+5, $sy+11, $sz+5, $log);
        $this->setBlock($world, $origin, $sx+5, $sy+12, $sz+5, $log);
        $this->setBlock($world, $origin, $sx+4, $sy+11, $sz+5, $log);
        $this->setBlock($world, $origin, $sx+6, $sy+11, $sz+5, $log);
        // Jendela besar
        $this->setBlock($world, $origin, $sx+5, $sy+3, $sz, $glass);
        $this->setBlock($world, $origin, $sx+5, $sy+4, $sz, $glass);
        $this->setBlock($world, $origin, $sx+5, $sy+3, $sz+11, $glass);
        $this->setBlock($world, $origin, $sx+5, $sy+4, $sz+11, $glass);
        // Pintu gereja
        $this->clearBox($world, $origin, $sx+4, $sy+1, $sz, $sx+6, $sy+3, $sz);
    }

    /** Helper: bangun pandai besi */
    private function buildSmithy(World $world, Vector3 $origin, int $sx, int $sy, int $sz,
        $cobble, $log, $plank): void
    {
        $air = VanillaBlocks::AIR();
        $this->hollowBox($world, $origin, $sx, $sy, $sz, $sx+9, $sy+5, $sz+8, $cobble, $air);
        $this->fillBox($world, $origin, $sx, $sy, $sz, $sx+9, $sy, $sz+8, $cobble);
        // Atap miring (slab atap)
        $this->fillBox($world, $origin, $sx-1, $sy+6, $sz-1, $sx+10, $sy+6, $sz+9, $cobble);
        // Interior: forge
        $this->setBlock($world, $origin, $sx+4, $sy+1, $sz+4, VanillaBlocks::FURNACE());
        $this->setBlock($world, $origin, $sx+5, $sy+1, $sz+4, VanillaBlocks::FURNACE());
        $this->setBlock($world, $origin, $sx+4, $sy+1, $sz+5, VanillaBlocks::LAVA());   // lava pit
        // Anvil
        $this->setBlock($world, $origin, $sx+2, $sy+1, $sz+5, VanillaBlocks::ANVIL());
        // Pintu masuk
        $this->clearBox($world, $origin, $sx+3, $sy+1, $sz, $sx+5, $sy+3, $sz);
        // Rak dekorasi (pagar)
        $this->fillBox($world, $origin, $sx+1, $sy+1, $sz+7, $sx+3, $sy+1, $sz+7, VanillaBlocks::OAK_FENCE());
    }
}
