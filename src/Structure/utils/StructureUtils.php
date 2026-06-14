<?php

declare(strict_types=1);

namespace Structure\utils;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;

class StructureUtils {

    /**
     * Rotasi koordinat relatif berdasarkan arah (0=north, 1=east, 2=south, 3=west).
     * Berguna untuk merotasi struktur agar menghadap arah yang berbeda.
     *
     * @param int $rx Koordinat X relatif
     * @param int $rz Koordinat Z relatif
     * @param int $rotation 0-3
     * @return array{int, int} [$newRx, $newRz]
     */
    public static function rotateCoord(int $rx, int $rz, int $rotation): array {
        return match($rotation) {
            0 => [$rx,  $rz],   // North (tidak diputar)
            1 => [$rz, -$rx],   // East
            2 => [-$rx, -$rz],  // South
            3 => [-$rz, $rx],   // West
            default => [$rx, $rz],
        };
    }

    /**
     * Temukan Y permukaan rata-rata untuk area (x1,z1)-(x2,z2).
     * Berguna untuk meratakan tanah sebelum spawn struktur.
     */
    public static function getAverageGroundY(World $world, int $x1, int $z1, int $x2, int $z2): int {
        $total = 0;
        $count = 0;
        for ($x = $x1; $x <= $x2; $x++) {
            for ($z = $z1; $z <= $z2; $z++) {
                for ($y = 100; $y >= 40; $y--) {
                    $b = $world->getBlockAt($x, $y, $z);
                    if (!($b instanceof \pocketmine\block\Air)) {
                        $total += $y;
                        $count++;
                        break;
                    }
                }
            }
        }
        return $count > 0 ? (int)($total / $count) + 1 : 64;
    }

    /**
     * Ratakan area (bersihkan atas, isi bawah) ke Y tertentu.
     */
    public static function flattenArea(World $world, int $x1, int $z1, int $x2, int $z2, int $targetY): void {
        $grass = VanillaBlocks::GRASS();
        $dirt  = VanillaBlocks::DIRT();
        $air   = VanillaBlocks::AIR();

        for ($x = $x1; $x <= $x2; $x++) {
            for ($z = $z1; $z <= $z2; $z++) {
                // Bersihkan blok di atas targetY
                for ($y = $targetY; $y <= $targetY + 20; $y++) {
                    $world->setBlockAt($x, $y, $z, $air);
                }
                // Isi blok di bawah targetY
                for ($y = $targetY - 1; $y >= $targetY - 5; $y--) {
                    $b = $world->getBlockAt($x, $y, $z);
                    if ($b instanceof \pocketmine\block\Air) {
                        $world->setBlockAt($x, $y, $z, $dirt);
                    }
                }
                // Lapisan paling atas = grass
                $world->setBlockAt($x, $targetY - 1, $z, $grass);
            }
        }
    }

    /**
     * Buat loot table gabungan dari dua tabel dengan bobot berbeda.
     * @param array $common  Item umum
     * @param array $rare    Item langka
     * @param int   $rareWeight Kemungkinan item langka muncul (1-10, default 2)
     * @return array
     */
    public static function mergedLootTable(array $common, array $rare, int $rareWeight = 2): array {
        $table = $common;
        for ($i = 0; $i < $rareWeight; $i++) {
            foreach ($rare as $item) {
                $table[] = $item;
            }
        }
        return $table;
    }

    /**
     * Cek apakah koordinat berada di dalam bounding box struktur.
     */
    public static function isInsideBoundingBox(
        Vector3 $pos,
        Vector3 $origin,
        int $width, int $height, int $depth
    ): bool {
        return $pos->x >= $origin->x && $pos->x <= $origin->x + $width
            && $pos->y >= $origin->y && $pos->y <= $origin->y + $height
            && $pos->z >= $origin->z && $pos->z <= $origin->z + $depth;
    }
}
