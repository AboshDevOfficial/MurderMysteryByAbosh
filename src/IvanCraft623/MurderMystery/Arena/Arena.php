<?php

declare(strict_types=1);

namespace IvanCraft623\MurderMystery\Arena;

use IvanCraft623\MurderMystery\{Murder, PluginUtils, Entity\EntityManager};

use pocketmine\{Server, Player, level\Position, item\Item, entity\Effect, entity\EffectInstance, utils\Config};
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class Arena {

    public static function getArenas() : array { // Return Arena ID
        $arenas = [];
        if ($handle = opendir(Murder::getInstance()->getDataFolder() . 'Arenas/')) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    $name = str_replace('.yml', '', $entry);
                    $arenas[] = $name;
                }
            }
            closedir($handle);
        }
        return $arenas;
    }

    public static function getAvailableArenas () : array { // Return Arena ID
        $arenas = [];
        $allArenas = self::getArenas();
        if (count($allArenas) > 0) {
            foreach ($allArenas as $arena) {
                if (self::getStatus($arena) === "waiting") {
                    if (count(self::getPlayers($arena)) < self::getMaxSlots($arena)) {
                        $arenas[] = $arena;
                    }
                }
            }
        }
        return $arenas;
    }

	public static function getPlayers(string $arena) : array {
		$players = [];
		$expectedArena = Server::getInstance()->getLevelByName(self::getName($arena));
		if ($expectedArena != null) {
			foreach ($expectedArena->getPlayers() as $player) {
				if ($player->getGamemode() == Player::SURVIVAL || $player->getGamemode() == Player::ADVENTURE) {
					$players[] = $player->getName();
				}
			}
		}
		return $players;
	}

	public static function getSpecters(string $arena) : array {
        $specters = [];
		$expectedArena = Server::getInstance()->getLevelByName(self::getName($arena));
		if ($expectedArena != null) {
        	foreach ($expectedArena->getPlayers() as $player) {
            	if ($player->getGamemode() == Player::SPECTATOR) {
                	$specters[] = $player->getName();
            	}
        	}
		}
        return $specters;
    }

    public static function getMurderersAlive(string $arena) : array {
        $murderers = [];
        $arenaName = self::getName($arena);
        $expectedArena = Server::getInstance()->getLevelByName($arenaName);
        if ($expectedArena != null) {
            foreach ($expectedArena->getPlayers() as $player) {
                if (self::getRole($player, $arena) === "Murderer") {
                    if (Murder::$data["players"][$arenaName]["Murder"][$player->getName()] === "Alive") {
                        $murderers[] = $player->getName();
                    }
                }
            }
        }
        return $murderers;
    }

    public static function getInoccentsAlive(string $arena) : array {
        $inoccents = [];
        $arenaName = self::getName($arena);
        $expectedArena = Server::getInstance()->getLevelByName($arenaName);
        if ($expectedArena != null) {
            foreach ($expectedArena->getPlayers() as $player) {
                if (self::getRole($player, $arena) === "Inoccent") {
                    if (Murder::$data["players"][$arenaName]["Inoccent"][$player->getName()] === "Alive") {
                        $inoccents[] = $player->getName();
                    }
                }
            }
        }
        return $inoccents;
    }

    public static function getDetectivesAlive(string $arena) : array {
        $detectives = [];
        $arenaName = self::getName($arena);
        $expectedArena = Server::getInstance()->getLevelByName($arenaName);
        if ($expectedArena != null) {
            foreach ($expectedArena->getPlayers() as $player) {
                if (self::getRole($player, $arena) === "Detective") {
                    if (Murder::$data["players"][$arenaName]["Detective"][$player->getName()] === "Alive") {
                        $detectives[] = $player->getName();
                    }
                }
            }
        }
        return $detectives;
    }

    public static function getInnocentesAndDetectivesAlive(string $arena) : array {
        $alive = [];
        $arenaName = self::getName($arena);
        $expectedArena = Server::getInstance()->getLevelByName($arenaName);
        if ($expectedArena != null) {
            foreach ($expectedArena->getPlayers() as $player) {
                if (self::getRole($player, $arena) === "Inoccent") {
                    if (Murder::$data["players"][$arenaName]["Inoccent"][$player->getName()] === "Alive") {
                        $alive[] = $player->getName();
                    }
                } elseif (self::getRole($player, $arena) === "Detective") {
                    if (Murder::$data["players"][$arenaName]["Detective"][$player->getName()] === "Alive") {
                        $alive[] = $player->getName();
                    }
                }
            }
        }
        return $alive;
    }

    public static function getCoinSpawns(string $arena) { // Return Spawns Name
        $spawns = [];
        $config = Murder::getConfigs('Arenas/' . $arena);
        $expectedSpawns = $config->get("coinspawns");
        if ($expectedSpawns != null) {
            foreach ($expectedSpawns as $expectedSpawn => $data) {
                $spawns[] = $expectedSpawn;
            }
        }
        return $spawns;
    }

    public static function getCoinSpawnPos(string $arena, $coinSpawn) {
        $arenaLevel = Server::getInstance()->getLevelByName(Arena::getName($arena));
        $config = Murder::getConfigs('Arenas/' . $arena);
        $configAll = $config->getAll();
        $configCoin = $configAll["coinspawns"][$coinSpawn];
        $spawnX = $configCoin["X"];
        $spawnY = $configCoin["Y"];
        $spawnZ = $configCoin["Z"];
        $spawnPos = new Position($spawnX, $spawnY, $spawnZ, $arenaLevel);
        return $spawnPos;
    }

    public static function isDetectiveAlive(string $arena) {
    	if (count(self::getDetectivesAlive($arena)) < 1) {
    		return "§cDead";
    	}
    	return "§aAlive";
    }

    public static function arenaWin(string $arena, $mode, $winners = null, $murderers = null, $murdererKiller = null) {
        self::setStatus($arena, 'end');
        $arenaName = self::getName($arena);
        $max = [];
        $tops = [];
        $coins = Murder::$data['coins'][Arena::getName($arena)];
        foreach ($coins as $key => $top) {
            array_push($tops, $top);
        }
        natsort($tops);
        $players = array_reverse($tops);
        if (max($tops) != null) {
            $max = array_search($players[0], $coins);
        }
        switch ($mode) {
            case 'MurderWon':
                Server::getInstance()->broadcastMessage(
                    Murder::getPrefix() ."§aMurderer(s) has won in the arena: ". $arena ."\n".
                    "§f╭━─━─━─━─━─━≪§e✠§f≫━─━─━─━─━─━╮"."\n".
                    "§l§6Winner(s): §r§b" . implode(", ", $winners) ."\n".
                    "§l§cMurderer(s): §r§b". implode(", ", $murderers) ."\n".
                    "§e§lMost Colected Coins: §r§b" . array_search($players[0], $coins) ."\n".
                    "§f╰━─━─━─━─━─━≪§e✠§f≫━─━─━─━─━─━╯"
                );
            break;
            
            case 'InoccentsWon':
                if ($murdererKiller != null) {
                    Server::getInstance()->broadcastMessage(
                        Murder::getPrefix() ."§aInoccent(s) has won in the arena: ". $arena ."\n".
                        "§f╭━─━─━─━─━─━≪§e✠§f≫━─━─━─━─━─━╮"."\n".
                        "§l§6Winner(s): §r§b" . implode(", ", $winners) ."\n".
                        "§l§dHero: §r§b" . $murdererKiller ."\n".
                        "§e§lMost Colected Coins: §r§b" . array_search($players[0], $coins) ."\n".
                        "§f╰━─━─━─━─━─━≪§e✠§f≫━─━─━─━─━─━╯"
                    );
                } else {
                    Server::getInstance()->broadcastMessage(
                        Murder::getPrefix() ."§aInoccent(s) has won in the arena: ". $arena ."\n".
                        "§f╭━─━─━─━─━─━≪§e✠§f≫━─━─━─━─━─━╮"."\n".
                        "§l§6Winner(s): §r§b" . implode(", ", $winners) ."\n".
                        "§l§dHero: §r§bNobody" ."\n".
                        "§e§lMost Colected Coins: §r§b" . array_search($players[0], $coins) ."\n".
                        "§f╰━─━─━─━─━─━≪§e✠§f≫━─━─━─━─━─━╯"
                    );
                }
            break;

            case 'NoWinners':
                Server::getInstance()->broadcastMessage(
                    Murder::getPrefix() ."§cThere were no winners in the arena: ". $arena ."\n".
                    "§f╭━─━─━─━─━─━≪§e✠§f≫━─━─━─━─━─━╮"."\n".
                    "§l§6Winner(s): §r§bNobody" ."\n".
                    "§l§cMurderer(s): §r§f". implode(", ", $murderers) ."\n".
                    "§e§lMost Colected Coins: §r§b" . array_search($players[0], $coins) ."\n".
                    "§f╰━─━─━─━─━─━≪§e✠§f≫━─━─━─━─━─━╯"
                );
                foreach (self::getPlayers($arena) as $losser) {
                    PluginUtils::ModifyStats($losser, "LOSSES", "add", 1);
                }
            break;
        }
        if ($winners != null) {
            foreach ($winners as $winner) {
                PluginUtils::ModifyStats($winner, "WINS", "add", 1);
            }
        }
        $players = self::getPlayers($arena);
        foreach ($players as $player) {
            PluginUtils::ModifyStats($player, "GAMESPLAYED", "add", 1);
        }
    }

    public static function joinArena(Player $player, $arena = null) {
        if (!PluginUtils::verifyPlayerInDB($player->getName())) {
            PluginUtils::addNewPLayer($player->getName());
            $player->sendMessage(
                "§b§l» §r§7Hey, {$player->getName()}, is your first game!"."\n".
                "§9§l» §r§7We are adding you to the MurderMystery database to follow your progress in your games..."
            );
        }
        if ($arena === null) {
            $arena = self::getRandomArena();
            if ($arena != null) {
                $player->sendMessage("§l§a» §r§eNew found arena, you will be transferred…");
            } else {
                $player->sendMessage("§l§a» §cNo arenas available for now, try again later…");
                return;
            }
        }
        if ($arena != null) {
            $arenaLevel = Server::getInstance()->getLevelByName(Arena::getName($arena));
            $arenaName = self::getName($arena);
            $config = Murder::getConfigs('Arenas/' . $arena);
            $configAll = $config->getAll();
            $player->removeAllEffects();
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getCursorInventory()->clearAll();
            $player->setAllowFlight(false);
            $player->setFlying(false);
            $player->setGamemode(2);
            $player->setHealth(20);
            $player->setFood(20);
            //Teleport to Waiting Lobby
            $arenaLevel = Server::getInstance()->getLevelByName($arenaName);
            $lobbyX = $configAll["lobby"]["X"];
            $lobbyY = $configAll["lobby"]["Y"];
            $lobbyZ = $configAll["lobby"]["Z"];
            $lobbyPos = new Position($lobbyX, $lobbyY, $lobbyZ, $arenaLevel);
            $arenaLevel->loadChunk($lobbyPos->getFloorX(), $lobbyPos->getFloorZ());
            $player->teleport($lobbyPos);
            foreach ($arenaLevel->getPlayers() as $players) {
                $players->sendMessage("§l§a» §r§7". $player->getName() ." joined. §8[" . count(Arena::getPlayers($arena)) . "/" . Arena::getMaxSlots($arena) . "]");
            }
        } else {
            $player->sendMessage("§l§a» §cNo arenas available for now, try again later…");
        }
    }

    public static function getRandomArena() { // Return Arena ID
        $availableArenas = self::getAvailableArenas();
        if (count($availableArenas) <= 0) {
            return null;
        } else {
            foreach ($availableArenas as $arena) {
                if (count(self::getPlayers($arena)) > 0) {
                    return $arena;
                }
            }
            return $availableArenas[array_rand($availableArenas)];
        }
    }

    public static function ArenaExisting(string $id) : bool {
        if (file_exists(Murder::getInstance()->getDataFolder() . 'Arenas/Murder-' . $id . '.yml')) {
            return true;
        } else {
            return false;
        }
    }

    public static function Kill(Player $player, Player $killer = null, string $arena) {
        $playerName = $player->getName();
        $arenaName = self::getName($arena);
        $arenaLevel = Server::getInstance()->getLevelByName($arenaName);
        $player->addTitle("§cYou died!", "§bGood luck next time");
        $player->setGamemode(3);
        if ($killer != null) {
            $killerName = $killer->getName();
            if (self::getRole($killer, $arena) === "Murderer") { //If the Killer is a Murderer
                PluginUtils::ModifyStats($killerName, "KILLS", "add", 1);
            }
            if (self::getRole($killer, $arena) === "Detective" ||
                self::getRole($killer, $arena) === "Inoccent") { //If the Killer is a Detective or Inoccent
                if (self::getRole($player, $arena) === "Murderer") {
                    PluginUtils::ModifyStats($killerName, "MURDERERELIMINATIONS", "add", 1);
                }
            }
        } else {
            $config = Murder::getConfigs('Arenas/' . $arena);
            $configAll = $config->getAll();
            $lobbyspX = $configAll["lobbyspecters"]["X"];
            $lobbyspY = $configAll["lobbyspecters"]["Y"];
            $lobbyspZ = $configAll["lobbyspecters"]["Z"];
            $lobbyspPos = new Position($lobbyspX, $lobbyspY, $lobbyspZ, $arenaLevel);
            $player->teleport($lobbyspPos);
            $arenaLevel->loadChunk($lobbyspPos->getFloorX(), $lobbyspPos->getFloorZ());
        }
        if (self::getRole($player, $arena) === "Murderer") {
            Murder::$data["players"][$arenaName]["Murder"][$playerName] = "Dead";
        } elseif (self::getRole($player, $arena) === "Detective") {
            Murder::$data["players"][$arenaName]["Detective"][$playerName] = "Dead";
            EntityManager::setNPCPoliceHat($player);
        } elseif (self::getRole($player, $arena) === "Inoccent") {
            Murder::$data["players"][$arenaName]["Inoccent"][$playerName] = "Dead";
        }
        $player->removeAllEffects();
        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 40, 1, true));
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getInventory()->setItem(4, Item::get(467, 0, 1)->setCustomName("§r§l§9Play Again!"));
        PluginUtils::ModifyStats($playerName, "GAMESPLAYED", "add", 1);
        PluginUtils::ModifyStats($playerName, "LOSSES", "add", 1);
        PluginUtils::ModifyStats($playerName, "DEATHS", "add", 1);
        foreach ($arenaLevel->getPlayers() as $players) {
            PluginUtils::PlaySound($players, "game.player.hurt");
        }
        EntityManager::setNPCTomb($player);
    }

	public static function addArena(Player $player, string $arena, string $slots, string $id) {
        Server::getInstance()->loadLevel($arena);
        Server::getInstance()->getLevelByName($arena)->loadChunk(Server::getInstance()->getLevelByName($arena)->getSafeSpawn()->getFloorX(), Server::getInstance()->getLevelByName($arena)->getSafeSpawn()->getFloorZ());
        $player->teleport(Server::getInstance()->getLevelByName($arena)->getSafeSpawn(), 0, 0);
        $player->setGamemode(1);
        Murder::$data['id'] = $id;
        Murder::$data['configurator'][] = $player->getName();
        Murder::$data['coins'][$arena] = ['Steve' => 0, 'Enderman' => 0];
        PluginUtils::setZip($arena);
        Murder::$data["arenaconfigs"][Murder::$data['id']]["arena"] = $arena;
        Murder::$data["arenaconfigs"][Murder::$data['id']]["maxslots"] = $slots;
        Murder::$data["arenaconfigs"][Murder::$data['id']]["status"] = 'editing';
        Murder::$data["arenaconfigs"][Murder::$data['id']]["lobbytime"] = 40;
        Murder::$data["arenaconfigs"][Murder::$data['id']]["startingtime"] = 11;
        Murder::$data["arenaconfigs"][Murder::$data['id']]["gametime"] = 600;
        Murder::$data["arenaconfigs"][Murder::$data['id']]["endtime"] = 16;
        $player->setGamemode(1);
        $player->sendMessage(Murder::getPrefix() . '§aArena created successfully.' . "\n" . '§aYou are now in configuration mode.');
    }

    public static function saveArenaConfigs(string $id) {
        $config = Murder::getConfigs('Arenas/Murder-' . $id);
        $config->setAll(Murder::$data["arenaconfigs"][$id]);
        $config->save();
    }

    public static function tpRandomSpawn(Player $player, string $arena) {
        $arenaLevel = Server::getInstance()->getLevelByName(Arena::getName($arena));
        $spawns = [];
        $config = Murder::getConfigs('Arenas/' . $arena);
        $configAll = $config->getAll();
        $expectedSpawns = $config->get("spawns");
        if($expectedSpawns != null) {
            foreach ($expectedSpawns as $expectedSpawn => $data) {
                $spawns[] = $expectedSpawn;
            }
        }
        $newSpawn = $spawns[array_rand($spawns)];
        $spawnX = $configAll["spawns"][$newSpawn]["X"];
        $spawnY = $configAll["spawns"][$newSpawn]["Y"];
        $spawnZ = $configAll["spawns"][$newSpawn]["Z"];
        $spawnPos = new Position($spawnX, $spawnY, $spawnZ, $arenaLevel);
        $arenaLevel->loadChunk($spawnPos->getFloorX(), $spawnPos->getFloorZ());
        $player->teleport($spawnPos);
    }

    public static function setRoles(string $arena) {
        $allPlayers = self::getPlayers($arena);
        $arenaName = self::getName($arena);
        var_export($allPlayers);
        $randomPlayer = array_rand($allPlayers, 2); //Get two random players...
        $randomPlayer1 = Murder::getInstance()->getServer()->getPlayer($allPlayers[$randomPlayer[0]]);
        $randomPlayer2 = Murder::getInstance()->getServer()->getPlayer($allPlayers[$randomPlayer[1]]);
        //Set Murderer Role to Random player 1
        Murder::$data["players"][$arenaName]["Murder"][$randomPlayer1->getName()] = "Alive";
        PluginUtils::PlaySound($randomPlayer1, "armor.equip_iron");
        $randomPlayer1->addTitle("§cMurderer", "§6Eliminate all other players!");
        $randomPlayer1->sendMessage(
            "§l§c» Murderer"."\n".
            "§l§c» §r§fEliminate all players without getting caught! §7You may only hurt players with your sword."
        );
        $randomPlayer1->getInventory()->setItem(1, Item::get(267, 0, 1)->setCustomName("§r§cMurderer Sword"));
        //Set Detective Role to Random player 2
        Murder::$data["players"][$arenaName]["Detective"][$randomPlayer2->getName()] = "Alive";
        PluginUtils::PlaySound($randomPlayer2, "mob.villager.no");
        $randomPlayer2->addTitle("§bDetective", "§cFind and kill the murderer!");
        $randomPlayer2->sendMessage(
            "§l§9» Detective"."\n".
            "§l§9» §r§fEliminate the murderer using your Bow! §7Be careful, wrongful kills will leave you vulnerable."
        );
        $randomPlayer2->getInventory()->setItem(1, Item::get(261, 0, 1)->setCustomName("§r§9Detective Bow"));
        $randomPlayer2->getInventory()->setItem(2, Item::get(262, 0, 1));
        foreach ($allPlayers as $player) {
            $player = Murder::getInstance()->getServer()->getPlayer($player);
            $playerName = $player->getName();
            if (self::getRole($player, $arena) === null) {
                Murder::$data["players"][$arenaName]["Inoccent"][$playerName] = "Alive";
                PluginUtils::PlaySound($player, "mob.villager.yes");
                $player->addTitle("§aInoccent", "§bTry to survive!");
                $player->sendMessage(
                    "§l§a» Inoccent"."\n".
                    "§l§a» §r§fTry to survive! §7Collect coins to earn rewars."
                );
            }
        }
    }

    public static function getRole(Player $player, string $arena) {
        $arenaName = self::getName($arena);
        $playerName = $player->getName();
        if (isset(Murder::$data["players"][$arenaName]["Inoccent"][$playerName])) {
            return "Inoccent";
        }
        if (isset(Murder::$data["players"][$arenaName]["Detective"][$playerName])) {
            return "Detective";
        }
        if (isset(Murder::$data["players"][$arenaName]["Murder"][$playerName])) {
            return "Murderer";
        }
        return null;
    }

    public static function getName(string $arena) : string { //Return Arena Name (World Name)
        $config = Murder::getConfigs('Arenas/' . $arena);
        return $config->get('arena');
    }

    public static function getMaxSlots(string $arena) : string {
        $config = Murder::getConfigs('Arenas/' . $arena);
        return $config->get('maxslots');
    }

    public static function setStatus(string $arena, string $value) {
        $config = Murder::getConfigs('Arenas/' . $arena);
        $config->set('status', $value);
        $config->save();
    }

    public static function getStatus(string $arena) : string {
        $config = Murder::getConfigs('Arenas/' . $arena);
        return $config->get('status');
    }

    public static function setTimeWaiting(string $arena, int $value) {
        $config = Murder::getConfigs('Arenas/' . $arena);
        $config->set('lobbytime', $value);
        $config->save();
    }

    public static function getTimeWaiting(string $arena) {
        $config = Murder::getConfigs('Arenas/' . $arena);
        return $config->get('lobbytime');
    }

    public static function setTimeStarting(string $arena, int $value) {
        $config = Murder::getConfigs('Arenas/' . $arena);
        $config->set('startingtime', $value);
        $config->save();
    }

    public static function getTimeStarting(string $arena) {
        $config = Murder::getConfigs('Arenas/' . $arena);
        return $config->get('startingtime');
    }

    public static function setTimeGame(string $arena, int $value) {
        $config = Murder::getConfigs('Arenas/' . $arena);
        $config->set('gametime', $value);
        $config->save();
    }

    public static function getTimeGame(string $arena) {
        $config = Murder::getConfigs('Arenas/' . $arena);
        return $config->get('gametime');
    }

    public static function setTimeEnd(string $arena, int $value) {
        $config = Murder::getConfigs('Arenas/' . $arena);
        $config->set('endtime', $value);
        $config->save();
    }

    public static function getTimeEnd(string $arena) {
        $config = Murder::getConfigs('Arenas/' . $arena);
        return $config->get('endtime');
    }
}
