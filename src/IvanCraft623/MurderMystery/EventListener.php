<?php

declare(strict_types=1);

namespace IvanCraft623\MurderMystery;

use IvanCraft623\MurderMystery\{Murder, PluginUtils, Arena\Arena, Code\CodeManager, Entity\MurderNPCJoin, Entity\MurderLeadboard, Entity\MurderPoliceHat, Entity\MurderCoin, Entity\MurderTomb, Entity\EntityManager, Form\FormManager};

use pocketmine\{Server, Player, level\Position, entity\Effect, entity\EffectInstance};
use pocketmine\event\{Listener, inventory\InventoryPickupItemEvent, player\PlayerPreLoginEvent, player\PlayerChatEvent, player\PlayerCommandPreprocessEvent, player\PlayerQuitEvent, player\PlayerDropItemEvent, player\PlayerMoveEvent, player\PlayerItemHeldEvent, player\PlayerInteractEvent, player\PlayerExhaustEvent, block\BlockBreakEvent, block\BlockPlaceEvent, entity\EntityLevelChangeEvent, entity\EntityDamageEvent, entity\EntityDamageByChildEntityEvent, entity\EntityDamageByEntityEvent, entity\EntityShootBowEvent, entity\ProjectileHitBlockEvent, math\Vector3};

class EventListener implements Listener {

	public function onChat(PlayerChatEvent $event) {
		$player = $event->getPlayer();
		$args = explode(' ',$event->getMessage());
		if (in_array($player->getName(), Murder::$data['configurator'])) {
			$event->setCancelled(true);
			switch ($args[0]) {
				case 'setlobby':
					Murder::$data["arenaconfigs"][Murder::$data['id']]["lobby"]["X"] = $player->getX();
					Murder::$data["arenaconfigs"][Murder::$data['id']]["lobby"]["Y"] = $player->getY();
					Murder::$data["arenaconfigs"][Murder::$data['id']]["lobby"]["Z"] = $player->getZ();
					$player->sendMessage('§aLobby registered with id: ' . Murder::$data['id']);
				break;

				case 'setlobbysp':
					Murder::$data["arenaconfigs"][Murder::$data['id']]["lobbyspecters"]["X"] = $player->getX();
					Murder::$data["arenaconfigs"][Murder::$data['id']]["lobbyspecters"]["Y"] = $player->getY();
					Murder::$data["arenaconfigs"][Murder::$data['id']]["lobbyspecters"]["Z"] = $player->getZ();
					$player->sendMessage('§aSpectator lobby successfully registered to id: ' . Murder::$data['id']);
				break;

				case 'setlobbywin':
					Murder::$data["arenaconfigs"][Murder::$data['id']]["lobbywin"]["X"] = $player->getX();
					Murder::$data["arenaconfigs"][Murder::$data['id']]["lobbywin"]["Y"] = $player->getY();
					Murder::$data["arenaconfigs"][Murder::$data['id']]["lobbywin"]["Z"] = $player->getZ();
					$player->sendMessage('§aWin lobby successfully registered to id: ' . Murder::$data['id']);
				break;

				case 'setspawn':
					if (!empty($args[1])) {
						Murder::$data["arenaconfigs"][Murder::$data['id']]["spawns"]["spawn-". $args[1]]["X"] = $player->getX();
						Murder::$data["arenaconfigs"][Murder::$data['id']]["spawns"]["spawn-". $args[1]]["Y"] = $player->getY();
						Murder::$data["arenaconfigs"][Murder::$data['id']]["spawns"]["spawn-". $args[1]]["Z"] = $player->getZ();
						$player->sendMessage('§aSpawn-' . $args[1] . ' successfully registered to id: ' . Murder::$data['id']);
					} else {
						$player->sendMessage("§cUse: setspawn <spawnNumber>");
					}
				break;

				case 'setcoinspawn':
					if (!empty($args[1])) {
						Murder::$data["arenaconfigs"][Murder::$data['id']]["coinspawns"]["spawn-". $args[1]]["X"] = $player->getX();
						Murder::$data["arenaconfigs"][Murder::$data['id']]["coinspawns"]["spawn-". $args[1]]["Y"] = $player->getY();
						Murder::$data["arenaconfigs"][Murder::$data['id']]["coinspawns"]["spawn-". $args[1]]["Z"] = $player->getZ();
						$player->sendMessage('§aCoin spawn-' . $args[1] . ' successfully registered to id: ' . Murder::$data['id']);
					} else {
						$player->sendMessage("§cUse: setspawn <spawnNumber>");
					}
				break;

				case 'done':
					if (!isset(Murder::$data["arenaconfigs"][Murder::$data['id']]["lobby"])) {
						$player->sendMessage("§cMissing lobby, can not save arena!");
						return;
					} elseif (!isset(Murder::$data["arenaconfigs"][Murder::$data['id']]["lobbyspecters"])) {
						$player->sendMessage("§cMissing specters lobby, can not save arena!");
						return;
					} elseif (!isset(Murder::$data["arenaconfigs"][Murder::$data['id']]["lobbywin"])) {
						$player->sendMessage("§cMissing win lobby, can not save arena!");
						return;
					} elseif (!isset(Murder::$data["arenaconfigs"][Murder::$data['id']]["spawns"])) {
						$player->sendMessage("§cMissing spawns, can not save arena!");
						return;
					} elseif (!isset(Murder::$data["arenaconfigs"][Murder::$data['id']]["coinspawns"])) {
						$player->sendMessage("§cMissing coins spawns, can not save arena!");
						return;
					}
					$id = Murder::$data['id'];
					Arena::saveArenaConfigs($id);
					Arena::setStatus('Murder-' . Murder::$data['id'], 'waiting');
					Murder::$data['id'] = '';
					$index = array_search($player->getName(), Murder::$data['configurator']);
					if  ($index != -1)  {
						unset(Murder::$data['configurator'][$index]);
					}
					$player->sendMessage("§aInstallation mode has been completed, arena created.");
					$player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
					$player->setGamemode(2);
				break;

				default:
					$player->sendMessage(
						"§6MurderMystery Configuration Commands"."\n"."\n".
						"§ahelp: §7Help commands."."\n".
						"§asetlobby: §7Register the waiting lobby."."\n".
						"§asetlobbysp: §7Register the specters lobby."."\n".
						"§asetlobbywin: §7Register the win lobby."."\n".
						"§asetspawn <spawnNumber>: §7Set a player spawn."."\n".
						"§asetcoinspawn <spawnNumber>: §7Set a coin spawn."."\n".
						"§adone: §7Enable Arena"

					);
				break;
			}
		}
	}

	public function CancelCommands(PlayerCommandPreprocessEvent $event) {
		$player = $event->getPlayer();
		$cmd = explode(' ', strtolower($event->getMessage()));
		foreach (Arena::getArenas() as $arena) {
			if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
				if ($player->getGamemode() == 0 || $player->getGamemode() == 2 || $player->getGamemode() == 3) {
					switch ($cmd[0]) {
						case '/gamemode':
						case '/gm':
						case '/gmc':
						case '/fly':
						case '/tp':
							$event->setCancelled();
						break;
					}
					if (isset($cmd[1])) {
						if ($cmd[1] === "join") {
							$event->setCancelled();
						}
					}
				}
			}
		}
	}

	public function onInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer();
		$id = $event->getItem()->getId();
		$damage = $event->getItem()->getDamage();
		$name = $event->getItem()->getCustomName();
		foreach (Arena::getArenas() as $arena) {
			if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
				if ($player->getGamemode() == 0 || $player->getGamemode() == 2 || $player->getGamemode() == 3) {
					if (PluginUtils::verifyInteractDelay($player)) {
						$arenaCode = CodeManager::getCodeOfArena($arena);
						if ($id == 345 && $name == "§r§aStart\n§r§fClick to select") {
							if ($arenaCode != null) {
								$arenaAdmin = CodeManager::getFromCodesDB($arenaCode, "creator");
								if ($player->getName() !== $arenaAdmin) {
									$player->sendMessage(Murder::getPrefix() . "§cOnly the creator of the code can use this function!");
									PluginUtils::PlaySound($player, "mob.blaze.shoot");
									return;
								}
							} else {
								if (!$player->hasPermission('murder.start.perm')) {
									$player->sendMessage(Murder::getPrefix() . "§cYou do not have permission to start the game!");
									PluginUtils::PlaySound($player, "mob.blaze.shoot");
									return;
								}
							}
							if (count(Server::getInstance()->getLevelByName($player->getLevel()->getFolderName())->getPlayers()) < 3) {
								$player->sendMessage(Murder::getPrefix() . "§cMore players are needed to start the game!");
								PluginUtils::PlaySound($player, "mob.blaze.shoot");
								return;						
							}
							if (Arena::getTimeWaiting($arena) >= 0 && Arena::getTimeWaiting($arena) <= 5) {
								$player->sendMessage(Murder::getPrefix() . "§c¡Oops! It seems that the game is about to begin...");
								PluginUtils::PlaySound($player, "mob.blaze.shoot");
								return;
							}
							Arena::setTimeWaiting($arena, 5);
							foreach ($player->getLevel()->getPlayers() as $players) {
								$players->sendMessage("§6The game will be start in 5 seconds.");
							}
						}
						if ($id == 355 && $name == "§r§cLeave\n§r§fClick to select") {
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
							$player->sendMessage("§cYou have leave successfully the game!");
						}
						if ($player->getGamemode() == 3) {
							if ($id == 467 && $name == "§r§l§9Play Again!") {
								$player->sendMessage("§l§a» §r§7Looking for an available game…");
								Arena::joinArena($player);
							}
						}
					}
					if ($id == 345 && $name == "§r§9Nearest Player") {
						$playerName = $player->getName();
						if (Arena::getRole($player, $arena) === "Murderer") {
							if (!PluginUtils::verifyInteractDelay($player)) {
								Murder::$data["interactDelay"][$playerName] = time() + 5;
								Arena::sendNearestPlayerMessage($player);
							} else {
								PluginUtils::PlaySound($player, "mob.blaze.shoot");
								$player->sendMessage("§cYou must wait ".(Murder::$data["interactDelay"][$playerName] - time())." seconds to use the compass again...");
							}
						}
					}
				}
			}
		}
	}

	public function onDrop(PlayerDropItemEvent $event) {
		$player = $event->getPlayer();
		foreach (Arena::getArenas() as $arena) {
			if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
				$event->setCancelled();
			}
		}
	}

	public function onQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$playerName = $player->getName();
		foreach (Arena::getArenas() as $arena) {
			$arenaName = Arena::getName($arena);
			$arenaLevel = Server::getInstance()->getLevelByName($arenaName);
			if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
				if (Arena::getStatus($arena) == 'ingame') {
					if (Arena::getRole($player, $arena) === "Murderer") {
						Murder::$data["players"][$arenaName]["Murderer"][$playerName] = "Dead";
						if (count(Arena::getMurderersAlive($arena)) < 1) {
							$winners = Arena::getInnocentesAndDetectivesAlive($arena);
							Arena::arenaWin($arena, "InoccentsWon", $winners);
						}
					} elseif (Arena::getRole($player, $arena) === "Detective") {
						if (Murder::$data["players"][$arenaName]["Detective"][$playerName] === "Alive") {
							EntityManager::setNPCPoliceHat($player);
						}
						Murder::$data["players"][$arenaName]["Detective"][$playerName] = "Dead";
						if (count(Arena::getInnocentesAndDetectivesAlive($arena)) < 1) {
							$murderers = Arena::getMurderersAlive($arena);
							Arena::arenaWin($arena, "MurderWon", $murderers, $murderers);
						}
					} elseif (Arena::getRole($player, $arena) === "Inoccent") {
						Murder::$data["players"][$arenaName]["Inoccent"][$playerName] = "Dead";
						if (count(Arena::getInnocentesAndDetectivesAlive($arena)) < 1) {
							$murderers = Arena::getMurderersAlive($arena);
							Arena::arenaWin($arena, "MurderWon", $murderers, $murderers);
						}
					}
				} elseif (Arena::getStatus($arena) == 'waiting' ||
					Arena::getStatus($arena) == 'waitingcode' ||
					Arena::getStatus($arena) == 'starting') {
					foreach ($arenaLevel->getPlayers() as $players) {
						$players->sendMessage("§l§c» §r§7". $playerName ." left. §8[" . count(Arena::getPlayers($arena)) . "/" . Arena::getMaxSlots($arena) . "]");
					}
				}
				if (isset(Murder::$data["giveArow"][$arenaName][$playerName])) {
					unset(Murder::$data["giveArow"][$arenaName][$playerName]);
				}
				$player->setNameTagAlwaysVisible();
			}
		}
	}

	public function onLevelChange(EntityLevelChangeEvent $event) {
		$player = $event->getEntity();
		if (!$player instanceof Player) return;
		$playerName = $player->getName();
		foreach (Arena::getArenas() as $arena) {
			$arenaName = Arena::getName($arena);
			$arenaLevel = Server::getInstance()->getLevelByName(Arena::getName($arena));
			if ($player->getLevel()->getFolderName() == $arenaName) {
				$api = Murder::getScore();
				$api->remove($player);
				if (Arena::getStatus($arena) == 'ingame') {
					if (Arena::getRole($player, $arena) === "Murderer") {
						Murder::$data["players"][$arenaName]["Murderer"][$playerName] = "Dead";
						if (count(Arena::getMurderersAlive($arena)) < 1) {
							$winners = Arena::getInnocentesAndDetectivesAlive($arena);
							Arena::arenaWin($arena, "InoccentsWon", $winners);
						}
					} elseif (Arena::getRole($player, $arena) === "Detective") {
						if (Murder::$data["players"][$arenaName]["Detective"][$playerName] === "Alive") {
							EntityManager::setNPCPoliceHat($player);
						}
						Murder::$data["players"][$arenaName]["Detective"][$playerName] = "Dead";
						if (count(Arena::getInnocentesAndDetectivesAlive($arena)) < 1) {
							$murderers = Arena::getMurderersAlive($arena);
							Arena::arenaWin($arena, "MurderWon", $murderers, $murderers);
						}
					} elseif (Arena::getRole($player, $arena) === "Inoccent") {
						Murder::$data["players"][$arenaName]["Inoccent"][$playerName] = "Dead";
						if (count(Arena::getInnocentesAndDetectivesAlive($arena)) < 1) {
							$murderers = Arena::getMurderersAlive($arena);
							Arena::arenaWin($arena, "MurderWon", $murderers, $murderers);
						}
					}
				} elseif (Arena::getStatus($arena) == 'waiting' ||
					Arena::getStatus($arena) == 'waitingcode' ||
					Arena::getStatus($arena) == 'starting') {
					foreach ($arenaLevel->getPlayers() as $players) {
						$players->sendMessage("§l§c» §r§7". $playerName ." left. §8[" . count(Arena::getPlayers($arena)) . "/" . Arena::getMaxSlots($arena) . "]");
					}
				}
				if (isset(Murder::$data["giveArow"][$arenaName][$playerName])) {
					unset(Murder::$data["giveArow"][$arenaName][$playerName]);
				}
				$player->setNameTagAlwaysVisible();
			}
		}
	}

	public function onMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();
		foreach (Arena::getArenas() as $arena) {
			$config = Murder::getConfigs('Arenas/' . $arena);
			$lobby = $config->get('lobby');
			if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
				if (Arena::getStatus($arena) == 'waiting' ||
					Arena::getStatus($arena) == 'waitingcode' ||
					Arena::getStatus($arena) == 'starting' ||
					Arena::getStatus($arena) == 'end'
				) {
					if ($player->getY() < 3) {
						$config = Murder::getConfigs('Arenas/' . $arena);
						$configAll = $config->getAll();
						$arenaName = Arena::getName($arena);
						$arenaLevel = Server::getInstance()->getLevelByName($arenaName);
						$lobbyX = $configAll["lobby"]["X"];
						$lobbyY = $configAll["lobby"]["Y"];
						$lobbyZ = $configAll["lobby"]["Z"];
						$lobbyPos = new Position($lobbyX, $lobbyY, $lobbyZ, $arenaLevel);
						$arenaLevel->loadChunk($lobbyPos->getFloorX(), $lobbyPos->getFloorZ());
						$player->teleport($lobbyPos);
					}
				}
			}
		}
	}

	public function onDamageToNPC(EntityDamageEvent $event) {
		$npc = $event->getEntity();
		if ($event instanceof EntityDamageByEntityEvent) {
			$player = $event->getDamager();
			if ($player instanceof Player) {
				$playerName = $player->getName();
				if ($npc instanceof MurderNPCJoin) {
					if (!PluginUtils::verifyPlayerInDB($player->getName())) {
						PluginUtils::addNewPLayer($player->getName());
						$player->sendMessage(
							"§b§l» §r§7Hey, {$player->getName()}, is your first game!"."\n".
							"§9§l» §r§7We are adding you to the MurderMystery database to follow your progress in your games..."
						);
					}
					FormManager::sendForm($player, "GamePanelUI");
					$event->setCancelled();
				}
				if ($npc instanceof MurderLeadboard) {
					if (!PluginUtils::verifyPlayerInDB($playerName)) {
						PluginUtils::addNewPLayer($playerName);
						$player->sendMessage(
							"§b§l» §r§7Hey, {$playerName}, is your first game!"."\n".
							"§9§l» §r§7We are adding you to the MurderMystery database to follow your progress in your games..."
						);
					}
					$player->sendTip("§l§cMurder§7» §r§aYour wins:§b " . PluginUtils::getFromStatsDB($playerName, 'WINS'));
					$event->setCancelled();
				}
			}
		}
		if ($npc instanceof MurderPoliceHat || $npc instanceof MurderTomb || $npc instanceof MurderCoin) {
			switch ($event->getCause()) {
				case EntityDamageEvent::CAUSE_SUICIDE:
				case EntityDamageEvent::CAUSE_VOID:
					# Do nothing xD
				break;
			
				default:
					$event->setCancelled();
				break;
			}
		}
	}

	public function onDamageToPlayer(EntityDamageEvent $event) {
		$player = $event->getEntity();
		foreach (Arena::getArenas() as $arena) {
			if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
				if ($player instanceof Player) {
					if (Arena::getStatus($arena) == 'waiting' ||
						Arena::getStatus($arena) == 'waitingcode' ||
						Arena::getStatus($arena) == 'starting' ||
						Arena::getStatus($arena) == 'end'
					) {
						$event->setCancelled();
					} elseif (Arena::getStatus($arena) == 'ingame') {
						switch ($event->getCause()) {
							case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
								if ($event instanceof EntityDamageByEntityEvent) {
									$damager = $event->getDamager();
									if ($damager instanceof Player) {
										if (Arena::getRole($damager, $arena) === "Murderer") {
											$item = $damager->getInventory()->getItemInHand();
											$id = $item->getId();
											$name = $item->getCustomName();
											if ($id == 267 && $name == "§r§cMurderer Sword") {
												Murder::$data["hitDelay"][$damager->getName()] = $player->getName();
												Arena::Kill($player, $damager, $arena);
												$damager->sendMessage("§l§a» §r§aYou killed §b{$player->getName()}");
												if (count(Arena::getInnocentesAndDetectivesAlive($arena)) < 1) {
													$murderers = Arena::getMurderersAlive($arena);
													Arena::arenaWin($arena, "MurderWon", $murderers, $murderers);
												}
												$event->setCancelled();
											} else {
												$event->setCancelled();
											}
										} elseif (Arena::getRole($damager, $arena) === "Inoccent" ||
											Arena::getRole($damager, $arena) === "Detective") {
											$event->setCancelled();
										}
									}
								}
							break;

							case EntityDamageEvent::CAUSE_PROJECTILE:
								if ($event instanceof EntityDamageByChildEntityEvent) {
									$damager = $event->getDamager();
									if ($damager instanceof Player) {
										if (Arena::getRole($damager, $arena) === "Detective" ||
											Arena::getRole($damager, $arena) === "Inoccent") {
											if (Arena::getRole($player, $arena) === "Inoccent" ||
												Arena::getRole($player, $arena) === "Detective") {
												Arena::Kill($player, $damager, $arena);
												$damager->sendMessage("§l§c» §r§b{$player->getName()} §awas §cNOT §athe Murder...");
												$damager->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 20 * 30, 2, false));
												$damager->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20 * 30, 1, false));
												Murder::$data["coins"][Arena::getName($arena)][$damager->getName()] = 0;
        	    								$damager->getInventory()->clearAll();
        									    $damager->getArmorInventory()->clearAll();
            									$damager->getCursorInventory()->clearAll();
            									if (Arena::getRole($damager, $arena) === "Detective") {
            										EntityManager::setNPCPoliceHat($damager);
													unset(Murder::$data["players"][Arena::getName($arena)]["Detective"][$damager->getName()]);
													Murder::$data["players"][Arena::getName($arena)]["Inoccent"][$damager->getName()] = "Alive";
													$damager->sendMessage("§l§c» You have lost your Detective role due to negligence");
            									}
												$event->setCancelled();
												if (count(Arena::getInnocentesAndDetectivesAlive($arena)) < 1) {
													$murderers = Arena::getMurderersAlive($arena);
													Arena::arenaWin($arena, "MurderWon", $murderers, $murderers);
												}
											} elseif (Arena::getRole($player, $arena) === "Murderer") {
												Arena::Kill($player, $damager, $arena);
												$damager->sendMessage("§l§a» §r§aYou killed the Murder!");
												if (count(Arena::getMurderersAlive($arena)) < 1) {
													$winners = Arena::getInnocentesAndDetectivesAlive($arena);
													$hero = $damager->getName();
													Arena::arenaWin($arena, "InoccentsWon", $winners, null, $hero);
													$event->setCancelled();
												}
											}
										} else {
											$event->setCancelled();
										}
									}
								}
							break;

							case EntityDamageEvent::CAUSE_VOID:
								if ($event->getFinalDamage() >= $player->getHealth()) {
									Arena::Kill($player, null, $arena);
								}
							break;
							
							default:
								$event->setCancelled();
							break;
						}
					}
				}
			}
		}
	}

	public function onShootBow(EntityShootBowEvent $event) {
		$player = $event->getEntity();
		if ($player instanceof Player) {
			foreach (Arena::getArenas() as $arena) {
				if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
					$projectile = $event->getProjectile();
					$item = $player->getInventory()->getItemInHand();
					$id = $item->getId();
					$name = $item->getCustomName();
					if (Arena::getStatus($arena) == 'ingame') {
						if (Arena::getRole($player, $arena) === "Inoccent") {
							if ($id == 261 && $name == "§r§6Inoccent Bow") {
								$projectile->namedtag->setString("custom_data", "murdermystery_arrow");
								$event->setForce(3);
							}
						} elseif (Arena::getRole($player, $arena) === "Detective") {
							if ($id == 261 && $name == "§r§9Detective Bow") {
								$projectile->namedtag->setString("custom_data", "murdermystery_arrow");
								$event->setForce(3);
								Murder::$data["giveArow"][Arena::getName($arena)][$player->getName()] = time() + 5;
							}
						}
						if (!Arena::getRole($player, $arena) === "Inoccent" ||
							!Arena::getRole($player, $arena) === "Detective") {
							$event->setCancelled();
						}
					}
				}
			}
		}
	}

	public function onArrowHitBlock(ProjectileHitBlockEvent $event) {
		$arrow = $event->getEntity();
		foreach (Arena::getArenas() as $arena) {
			if ($arrow->getLevel()->getFolderName() == Arena::getName($arena)) {
				if ($arrow->namedtag->hasTag("custom_data")) {
					$value = $arrow->namedtag->getString("custom_data");
					if ($value == "murdermystery_arrow") {
						$arrow->flagForDespawn();
					}
				}
			}
		}
	}

	public function onHunger(PlayerExhaustEvent $event) {
		$player = $event->getPlayer();
		foreach (Arena::getArenas() as $arena) {
			$arenaLevel = Server::getInstance()->getLevelByName(Arena::getName($arena));
			if ($player->getLevel() == $arenaLevel) {
				$event->setCancelled();
			}
		}
	}
}
