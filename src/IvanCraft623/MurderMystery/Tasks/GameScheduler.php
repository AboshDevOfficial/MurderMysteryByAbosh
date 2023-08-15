<?php

declare(strict_types=1);

namespace IvanCraft623\MurderMystery\Tasks;

use IvanCraft623\MurderMystery\{Murder, PluginUtils, ResetMap, API\ScoreAPI, Arena\Arena, Code\CodeManager, Entity\EntityManager, Entity\MurderPoliceHat, Entity\MurderTomb, Entity\MurderCoin};

use pocketmine\{Server, Player, level\Level, item\Item, level\Position, entity\Effect, entity\Skin, math\Vector3, scheduler\Task, utils\TextFormat as Color};

class GameScheduler extends Task {

	public function onRun(int $currentTick) : void {
		unset(Murder::$data["hitDelay"]);
		if (count(Arena::getArenas()) > 0) {
			foreach (Arena::getArenas() as $arena) {
				$arenaLevel = Server::getInstance()->getLevelByName(Arena::getName($arena));
				$timelobby = Arena::getTimeWaiting($arena);
				$timestarting = Arena::getTimeStarting($arena);
				$timegame = Arena::getTimeGame($arena);
				$timeend = Arena::getTimeEnd($arena);
				if ($arenaLevel instanceof Level) {
					if (Arena::getStatus($arena) == 'waiting' || Arena::getStatus($arena) == 'waitingcode') {
						foreach ($arenaLevel->getPlayers() as $player) {
							$player->getInventory()->setItem(2, Item::get(345, 0, 1)->setCustomName("§r§aStart\n§r§fClick to select"));
							$player->getInventory()->setItem(6, Item::get(355, 14, 1)->setCustomName("§r§cLeave\n§r§fClick to select"));
						}
						if (count(Arena::getPlayers($arena)) < 3) {
							foreach ($arenaLevel->getPlayers() as $player) {
								$arenaLevel->setTime(0);
								$arenaLevel->stopTime();
								Murder::getReloadArena($arena);
								$player->sendTip("§cMore players are needed to start counting…");
							}
						} else {
							$timelobby--;
							Arena::setTimeWaiting($arena, $timelobby);
							foreach ($arenaLevel->getPlayers() as $player) {
								if (count(Arena::getPlayers($arena)) == Arena::getMaxSlots($arena)) {
									$player->sendMessage("§l§a» §r§eThe arena has reached its maximum capacity, starting the game…");
									Arena::setStatus($arena, 'starting');
									$player->getInventory()->clearAll();
									$player->getArmorInventory()->clearAll();
									$player->getCursorInventory()->clearAll();
								}
								if ($timelobby >= 6 && $timelobby <= 40) {
									$player->sendTip('§aStarting game in §l' . $timelobby);
								} else if ($timelobby >= 1 && $timelobby <= 5) {
									$player->sendTip('§aStarting game in §l§c' . $timelobby);
									PluginUtils::PlaySound($player, "random.click");
								}
								if ($timelobby == 0) {
									Arena::setStatus($arena, 'starting');
									$player->getInventory()->clearAll();
									$player->getArmorInventory()->clearAll();
									$player->getCursorInventory()->clearAll();
								}
							}
						}
					} elseif (Arena::getStatus($arena) == 'starting') {
						$timestarting--;
						Arena::setTimeStarting($arena, $timestarting);
						foreach ($arenaLevel->getPlayers() as $player) {
							if ($timestarting >= 0 && $timestarting <= 10) {
								if (count(Arena::getPlayers($arena)) < 3) {
									Murder::getReloadArena($arena);
									if (CodeManager::getCodeOfArena($arena) != null) {
										Arena::setStatus($arena, "waitingcode");
									}
									$player->setNameTagAlwaysVisible();
									$player->sendMessage("§l§c» §r§bCounting cancelled because are needed more players…");
									$config = Murder::getConfigs('Arenas/' . $arena);
									$configAll = $config->getAll();
									$lobbyX = $configAll["lobby"]["X"];
									$lobbyY = $configAll["lobby"]["Y"];
									$lobbyZ = $configAll["lobby"]["Z"];
									$lobbyPos = new Position($lobbyX, $lobbyY, $lobbyZ, $arenaLevel);
									$arenaLevel->loadChunk($lobbyPos->getFloorX(), $lobbyPos->getFloorZ());
									$player->teleport($lobbyPos);
								}
								if ($timestarting == 10) {
									$player->setNameTagAlwaysVisible(false);
									$player->sendTip("§eGame start §l»§r §e▌▌▌▌▌▌▌▌▌▌ §f10");
									Arena::tpRandomSpawn($player, $arena);
								} elseif ($timestarting == 9) {
									$player->sendTip("§eGame start §l»§r §e▌▌▌▌▌▌▌▌▌§7▌ §f9");
								} elseif ($timestarting == 8) {
									$player->sendTip("§eGame start §l»§r §e▌▌▌▌▌▌▌▌§7▌▌ §f8");
								} elseif ($timestarting == 7) {
									$player->sendTip("§eGame start §l»§r §e▌▌▌▌▌▌▌§7▌▌▌ §f7");
								} elseif ($timestarting == 6) {
									$player->sendTip("§eGame start §l»§r §e▌▌▌▌▌▌§7▌▌▌▌ §f6");
								} elseif ($timestarting == 5) {
									$player->sendTip("§eGame start §l»§r §e▌▌▌▌▌§7▌▌▌▌▌ §f5");
								} elseif ($timestarting == 4) {
									$player->sendTip("§eGame start §l»§r §e▌▌▌▌§7▌▌▌▌▌▌ §f4");
								} elseif ($timestarting == 3) {
									$player->sendTip("§eGame start §l»§r§c ▌▌▌§7▌▌▌▌▌▌▌ §f3");
									PluginUtils::PlaySound($player, "random.toast", 1, 1.5);
								} elseif ($timestarting == 2) {
									$player->sendTip("§eGame start §l»§r§c ▌▌§7▌▌▌▌▌▌▌▌ §f2");
									PluginUtils::PlaySound($player, "random.toast", 1, 1.5);
								} elseif ($timestarting == 1) {
									$player->sendTip("§eGame start §l»§r§c ▌§7▌▌▌▌▌▌▌▌▌ §f1");
									PluginUtils::PlaySound($player, "random.toast", 1, 1.5);
								}
							}
						}
						if ($timestarting == 0) {
							Arena::setStatus($arena, 'ingame');
							Arena::setRoles($arena);
							foreach (Arena::getCoinSpawns($arena) as $coin) {
								$coinPos = Arena::getCoinSpawnPos($arena, $coin);
								EntityManager::setNPCCoin($coinPos, $arenaLevel, $coin);
							}
						}
					} else if (Arena::getStatus($arena) == 'ingame') {
						$timegame--;
						Arena::setTimeGame($arena, $timegame);
						foreach ($arenaLevel->getPlayers() as $player) {
							if (!isset(Murder::$data["coins"][Arena::getName($arena)][$player->getName()])) {
								Murder::$data["coins"][Arena::getName($arena)][$player->getName()] = 0;
							}
							$from = 0;
							$api = Murder::getScore();
							$api->new($player, $player->getName(), '§l§eMURDER MYSTERY');
							if (Arena::getRole($player, $arena) != null) {
								$setlines = [
									"§7" . date("d/m/Y"),
									Color::RED . "   ",
									"§fRole: §a" . Arena::getRole($player, $arena),
									"§fCoins: §a" . Murder::$data["coins"][Arena::getName($arena)][$player->getName()],
									Color::YELLOW . "   ",
									"§fInnocents left: §a" . count(Arena::getInnocentesAndDetectivesAlive($arena)),
									"§fSpecters: §a" . count(Arena::getSpecters($arena)),
									"§fTime left: §a" . PluginUtils::getTimeParty($timegame),
									Color::WHITE . "   ",
									"§fDetective: " . Arena::isDetectiveAlive($arena),
									Color::GREEN . "   ",
									"§fMap: §a" . Arena::getName($arena),
									Color::BLUE . "   ",
									"§eendergames.ddns.net"
								];
							} else {
								if ($player->getGamemode() == Player::SURVIVAL || $player->getGamemode() == Player::ADVENTURE) {
									$player->sendMessage("§l§c» §r§bYou can't be here ... Redirecting you to the lobby");
									$player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
									$player->setGamemode(2);
								}
								$setlines = [
									"§7" . date("d/m/Y"),
									Color::RED . "   ",
									"§fCoins: §a" . Murder::$data["coins"][Arena::getName($arena)][$player->getName()],
									Color::YELLOW . "   ",
									"§fInnocents left: §a" . count(Arena::getInnocentesAndDetectivesAlive($arena)),
									"§fSpecters: §a" . count(Arena::getSpecters($arena)),
									"§fTime left: §a" . PluginUtils::getTimeParty($timegame),
									Color::WHITE . "   ",
									"§fDetective: " . Arena::isDetectiveAlive($arena),
									Color::GREEN . "   ",
									"§fMap: §a" . Arena::getName($arena),
									Color::BLUE . "   ",
									"§eendergames.ddns.net"
								];
							}
							foreach ($setlines as $lines) {
								if ($from < 15) {
									$from++;
									$api->setLine($player, $from, $lines);
									$api->getObjectiveName($player);
								}
							}
						}
						foreach (Murder::$data["coinDelay"] as $arenaName => $coins) {
							foreach ($coins as $coin => $spawnCoinTime) {
								if ($arenaName === Arena::getName($arena)) {
									if (time() >= $spawnCoinTime) {
										unset(Murder::$data["coinDelay"][$arenaName][$coin]);
										if ($coin != "Murder.Coin") {
											$coinPos = Arena::getCoinSpawnPos($arena, $coin);
											EntityManager::setNPCCoin($coinPos, $arenaLevel, $coin);
										}
									}
								}
							}
						}
						foreach (Murder::$data["giveArow"] as $arenaName => $playersName) {
							foreach ($playersName as $playerName => $giveArowTime) {
								$player = Murder::getInstance()->getServer()->getPlayer($playerName);
								if ($arenaName === Arena::getName($arena)) {
									if ($player->getLevel()->getFolderName() == $arenaName) {
										if (Arena::getRole($player, $arena) === "Detective") {
											if (Murder::$data["players"][$arenaName]["Detective"][$playerName] === "Alive") {
												if (time() >= $giveArowTime) {
													unset(Murder::$data["giveArow"][$arenaName][$playerName]);
													if ($player->getInventory()->isSlotEmpty(2)) {
														$player->getInventory()->setItem(2, Item::get(262, 0, 1));
													} else {
														$player->getInventory()->setItem(3, Item::get(262, 0, 1));
													}
												}
											}
										}
									}
								}
							}
						}
						if ($timegame == 0) {
							$murderers = Arena::getMurderersAlive($arena);
							Arena::arenaWin($arena, "NoWinners", null, $murderers);
							$api = Murder::getScore();
							$api->remove($player);
						}
					} else if (Arena::getStatus($arena) == 'end') {
						$timeend--;
						Arena::setTimeEnd($arena, $timeend);
						if ($timeend == 7) {
							$arenaCode = CodeManager::getCodeOfArena($arena);
							if ($arenaCode != null) {
								CodeManager::removeCodeFromDB($arenaCode);
							}
							foreach ($arenaLevel->getPlayers() as $player) {
								$player->setNameTagAlwaysVisible();
								$player->getInventory()->clearAll();
								$player->getArmorInventory()->clearAll();
								$player->getCursorInventory()->clearAll();
								$player->sendMessage("§l§a» §r§7Looking for an available game…");
								Arena::joinArena($player);
							}
						} else if ($timeend >= 1 && $timeend <= 3) {
							foreach ($arenaLevel->getPlayers() as $player) {
								$player->sendTip("§eReseting game in ". $timeend ." seconds.");
							}
						} else if ($timeend == 0) {
							foreach ($arenaLevel->getPlayers() as $player) {
								$player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
								$player->getInventory()->clearAll();
								$player->getCursorInventory()->clearAll();
								$player->getArmorInventory()->clearAll();
								$player->setImmobile(false);
								$player->setAllowFlight(false);
								$player->setFlying(false);
								$player->removeAllEffects();
								$player->setGamemode(2);
								$player->setHealth(20);
								$player->setFood(20);   
							}
							ResetMap::resetZip(Arena::getName($arena));
							Murder::getReloadArena($arena);
							unset(Murder::$data["players"][Arena::getName($arena)]);
							unset(Murder::$data["coins"][Arena::getName($arena)]);
							unset(Murder::$data["giveArow"][Arena::getName($arena)]);
							unset(Murder::$data["coinDelay"][Arena::getName($arena)]);
						}
					}
				}
			}
		}
	}
}
