<?php

declare(strict_types=1);

namespace Structure\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use Structure\Main;

class StructureCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct(
            "structure",
            "Spawn atau kelola struktur",
            "/structure <spawn|list|info> [nama] [x y z]",
            ["str"]
        );
        $this->plugin = $plugin;
        $this->setPermission("structure.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) return false;

        if (empty($args)) {
            $sender->sendMessage("§eGunakan: /structure <spawn|list|info> [nama]");
            return false;
        }

        $subCmd = strtolower($args[0]);

        switch ($subCmd) {

            // ─── /structure list ─────────────────────────────────────────
            case "list":
                $names = $this->plugin->getStructureManager()->getStructureNames();
                $sender->sendMessage("§a§l=== Daftar Struktur (" . count($names) . ") ===");
                foreach ($names as $n) {
                    $s = $this->plugin->getStructureManager()->getStructure($n);
                    $biomes = implode(", ", $s->getAllowedBiomes());
                    $sender->sendMessage("§e• §f" . $s->getName() . " §7- " . $s->getDescription());
                    $sender->sendMessage("  §7Biome: §b" . ($biomes !== "" ? $biomes : "Semua"));
                }
                return true;

            // ─── /structure info <nama> ──────────────────────────────────
            case "info":
                if (!isset($args[1])) {
                    $sender->sendMessage("§cGunakan: /structure info <nama>");
                    return false;
                }
                $s = $this->plugin->getStructureManager()->getStructure($args[1]);
                if ($s === null) {
                    $sender->sendMessage("§cStruktur '§f{$args[1]}§c' tidak ditemukan.");
                    return false;
                }
                $sender->sendMessage("§a§l=== " . $s->getName() . " ===");
                $sender->sendMessage("§7Deskripsi: §f" . $s->getDescription());
                $sender->sendMessage("§7Biome: §b" . (implode(", ", $s->getAllowedBiomes()) ?: "Semua"));
                $sender->sendMessage("§7Ukuran: §e" . $s->getWidth() . "x" . $s->getHeight() . "x" . $s->getDepth());
                $sender->sendMessage("§7Kamar loot: §d" . ($s->hasLootRoom() ? "Ya" : "Tidak"));
                return true;

            // ─── /structure spawn <nama> [x y z] ─────────────────────────
            case "spawn":
                if (!isset($args[1])) {
                    $sender->sendMessage("§cGunakan: /structure spawn <nama> [x] [y] [z]");
                    return false;
                }
                if (!($sender instanceof Player) && count($args) < 5) {
                    $sender->sendMessage("§cKamu harus menjadi pemain atau berikan koordinat x y z.");
                    return false;
                }

                if (isset($args[2], $args[3], $args[4])) {
                    $x = (float) $args[2];
                    $y = (float) $args[3];
                    $z = (float) $args[4];
                    $world = ($sender instanceof Player)
                        ? $sender->getWorld()
                        : $this->plugin->getServer()->getWorldManager()->getDefaultWorld();
                    $pos = new Vector3($x, $y, $z);
                } else {
                    /** @var Player $sender */
                    $pos = $sender->getPosition()->asVector3();
                    $world = $sender->getWorld();
                }

                $structName = $args[1];
                $sender->sendMessage("§eMembangun struktur '§f{$structName}§e'...");
                $success = $this->plugin->getStructureManager()->spawnStructure($structName, $world, $pos);

                if ($success) {
                    $sender->sendMessage("§aStruktur '§f{$structName}§a' berhasil di-spawn!");
                } else {
                    $sender->sendMessage("§cStruktur '§f{$structName}§c' tidak ditemukan. Gunakan /structure list.");
                }
                return true;

            default:
                $sender->sendMessage("§cSub-command tidak dikenal. Gunakan: spawn, list, info");
                return false;
        }
    }
}
