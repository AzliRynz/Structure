<?php

declare(strict_types=1);

namespace Structure\listener;

use pocketmine\event\Listener;
use pocketmine\event\world\ChunkPopulateEvent;
use pocketmine\math\Vector3;
use Structure\Main;

/**
 * WorldListener
 * ─────────────────────────────────────────────────────────────────────────────
 * Mendengarkan event generasi chunk. Jika mode auto-spawn aktif di config,
 * struktur akan di-spawn secara acak saat chunk baru diisi.
 *
 * Probabilitas default per struktur bisa diset di config.yml.
 */
class WorldListener implements Listener {

    private Main $plugin;

    /** @var array Cache config untuk performa */
    private array $autoSpawnConfig;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->autoSpawnConfig = $plugin->getConfig()->get("auto-spawn", []);
    }

    /**
     * Event ini dipanggil setiap kali chunk selesai di-populate (diisi).
     * Kita gunakan ini untuk auto-spawn struktur secara acak.
     */
    public function onChunkPopulate(ChunkPopulateEvent $event): void {
        // Jika auto-spawn tidak aktif di config, skip
        if (!($this->autoSpawnConfig["enabled"] ?? false)) {
            return;
        }

        $world    = $event->getWorld();
        $chunkX   = $event->getChunk()->getX();
        $chunkZ   = $event->getChunk()->getZ();

        // Seed deterministik berdasarkan koordinat chunk (agar konsisten saat reload)
        $seed  = abs(($chunkX * 341873128712) ^ ($chunkZ * 132897987541));
        $rand  = $seed % 10000;

        $structures = $this->autoSpawnConfig["structures"] ?? [];
        $cumulative = 0;

        foreach ($structures as $structName => $cfg) {
            $chance = (int)($cfg["chance"] ?? 0);  // per 10.000 chunk
            $cumulative += $chance;

            if ($rand < $cumulative) {
                // Cari Y yang tepat (ground level) di tengah chunk
                $blockX = ($chunkX * 16) + 8;
                $blockZ = ($chunkZ * 16) + 8;
                $groundY = $this->findGroundY($world, $blockX, $blockZ);

                if ($groundY !== null) {
                    $pos = new Vector3($blockX, $groundY, $blockZ);
                    $this->plugin->getStructureManager()->spawnStructure($structName, $world, $pos);
                    $this->plugin->getLogger()->debug(
                        "Auto-spawn '$structName' di chunk [$chunkX, $chunkZ] pos [$blockX, $groundY, $blockZ]"
                    );
                }
                break; // hanya 1 struktur per chunk
            }
        }
    }

    /**
     * Cari Y permukaan tanah (Y tertinggi yang bukan udara).
     */
    private function findGroundY(\pocketmine\world\World $world, int $x, int $z): ?int {
        for ($y = 100; $y >= 40; $y--) {
            $block = $world->getBlockAt($x, $y, $z);
            if (!($block instanceof \pocketmine\block\Air)) {
                return $y + 1;
            }
        }
        return null;
    }
}
