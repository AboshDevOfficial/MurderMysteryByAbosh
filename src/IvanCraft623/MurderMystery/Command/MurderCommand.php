<?php

declare(strict_types=1);

#MurderMystery by IvanCraft623 (Twitter: @IvanCraft623)

/*
    8888888                            .d8888b.                   .d888 888     .d8888b.   .d8888b.   .d8888b.  
      888                             d88P  Y88b                 d88P"  888    d88P  Y88b d88P  Y88b d88P  Y88b 
      888                             888    888                 888    888    888               888      .d88P 
      888  888  888  8888b.  88888b.  888        888d888 8888b.  888888 888888 888d888b.       .d88P     8888"  
      888  888  888     "88b 888 "88b 888        888P"      "88b 888    888    888P "Y88b  .od888P"       "Y8b. 
      888  Y88  88P .d888888 888  888 888    888 888    .d888888 888    888    888    888 d88P"      888    888 
      888   Y8bd8P  888  888 888  888 Y88b  d88P 888    888  888 888    Y88b.  Y88b  d88P 888"       Y88b  d88P 
    8888888  Y88P   "Y888888 888  888  "Y8888P"  888    "Y888888 888     "Y888  "Y8888P"  888888888   "Y8888P"  
*/

#For Server (IP: endergames.ddns.net  Port:25331):

/*
    ███████╗███╗   ██╗██████╗ ███████╗██████╗  ██████╗  █████╗ ███╗   ███╗███████╗███████╗
    ██╔════╝████╗  ██║██╔══██╗██╔════╝██╔══██╗██╔════╝ ██╔══██╗████╗ ████║██╔════╝██╔════╝
    █████╗  ██╔██╗ ██║██║  ██║█████╗  ██████╔╝██║  ███╗███████║██╔████╔██║█████╗  ███████╗
    ██╔══╝  ██║╚██╗██║██║  ██║██╔══╝  ██╔══██╗██║   ██║██╔══██║██║╚██╔╝██║██╔══╝  ╚════██║
    ███████╗██║ ╚████║██████╔╝███████╗██║  ██║╚██████╔╝██║  ██║██║ ╚═╝ ██║███████╗███████║
    ╚══════╝╚═╝  ╚═══╝╚═════╝ ╚══════╝╚═╝  ╚═╝ ╚═════╝ ╚═╝  ╚═╝╚═╝     ╚═╝╚══════╝╚══════╝
*/

namespace IvanCraft623\MurderMystery\Command;

use IvanCraft623\MurderMystery\{Murder, PluginUtils, ResetMap, Arena\Arena, Code\CodeManager, Form\FormManager, Entity\EntityManager, Entity\MurderLeadboard, Entity\MurderNPCJoin};

use pocketmine\{Server, Player, entity\Entity};
use pocketmine\command\{PluginCommand, CommandSender};

class MurderCommand extends PluginCommand {

	/**
	 * MurderCommand Constructor
	 * @param Murder $plugin
	 */
	public function __construct(Murder $plugin) {
		parent::__construct('murder', $plugin);
		$this->setDescription('MurderMystery by IvanCraft623.');
	}

	public function execute(CommandSender $sender, string $label, array $args) {
		if (isset($args[0])) {
			switch ($args[0]) {
				case 'create':
					if (!$sender->isOp()) {
						$sender->sendMessage(Murder::getPrefix() . "§cYou do not have permissions to use this command!");
						return true;
					}
					if (!$sender instanceof Player) {
						$sender->sendMessage(Murder::getPrefix() . "§cYou can only use this command in the game!");
						return true;
					}
					if (!isset($args[1], $args[2], $args[3])) {
						$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder create <arena> <maxslots> <id>");
						return true;
					}
					if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[1])) {
						$sender->sendMessage(Murder::getPrefix() . "§cThe {$args[1]} world does not exist!");
						return true;
					}
					if ($args[2] <= 1) {
						$sender->sendMessage(Murder::getPrefix() . "§cMax Slots must be a valid number!");
						return true;
					}
					if (Arena::ArenaExisting($args[3])) {
						$sender->sendMessage(Murder::getPrefix() . "§cThe {$args[3]} arena id already exist!");
					} else {
						Arena::addArena($sender, $args[1], $args[2], $args[3]);
					}
				break;

				case 'arena':
					if (isset($args[2])) {
						if (!Arena::ArenaExisting($args[1])) {
							$sender->sendMessage(Murder::getPrefix() . "§cArena Murder-" . $args[1] . " does not exist!");
							return true;
						}
						$arena = "Murder-{$args[1]}";
						$arenaName = Arena::getName($arena);
						$arenaLevel = Server::getInstance()->getLevelByName($arenaName);
						switch ($args[2]) {
							case 'disable':
								if (Arena::getStatus($arena) != "disabled") {
									Arena::setStatus($arena, 'disabled');
									if (Server::getInstance()->isLevelLoaded($arenaName)) {
										foreach ($arenaLevel->getPlayers() as $players) {
											$players->sendMessage("§l§c» §r§bArena has been disabled, connecting to the lobby...");
											$players->setNameTagAlwaysVisible();
											$players->setGamemode(2);
											$players->getInventory()->clearAll();
											$players->getArmorInventory()->clearAll();
											$players->getCursorInventory()->clearAll();
											$players->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
										}
										ResetMap::resetZip($arenaName);
									}
									$sender->sendMessage(Murder::getPrefix() . "§aArena §e" . $arena . " §ahas been disabled successfully!");
								} else {
									$sender->sendMessage(Murder::getPrefix() . "§cArena " . $arena . " is already disabled!");
								}
							break;

							case 'enable':
								if (Arena::getStatus($arena) == "disabled") {
									$sender->sendMessage("§eReseting arena...");
									if (Server::getInstance()->isLevelLoaded($arenaName)) {
										foreach ($arenaLevel->getPlayers() as $players) {
											$players->setNameTagAlwaysVisible();
											$players->setGamemode(2);
											$players->getInventory()->clearAll();
											$players->getArmorInventory()->clearAll();
											$players->getCursorInventory()->clearAll();
											$players->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
										}
									}
									Murder::getReloadArena($arena);
									ResetMap::resetZip($arenaName);
									$sender->sendMessage(Murder::getPrefix() . "§aArena §e" . $arena . " §ahas been enabled successfully!");
								} else {
									$sender->sendMessage(Murder::getPrefix() . "§cArena " . $arena . " is already enabled!");
								}
							break;

							case 'edit':
								$sender->sendMessage(Murder::getPrefix() . "§cThis function is under development, please wait for it...");
							break;

							case 'savelevel':
								if (Arena::getStatus($arena) == "disabled") {
									$sender->sendMessage(Murder::getPrefix() . "§eSaving arena level " . $arena . " ...");
									PluginUtils::setZip($arenaName);
									$sender->sendMessage(Murder::getPrefix() . "§aArena level " . $arena . " has been saved successfully!");
								} else {
									$sender->sendMessage(Murder::getPrefix() . "§cArena must be disabled to be saved!");
								}
							break;
							
							default:
								$sender->sendMessage(
									"§aUse: /murder arena <arena> disable §7» Disable an Arena."."\n".
									"§aUse: /murder arena <arena> enable §7» Enable an Arena."."\n".
									"§aUse: /murder arena <arena> edit §7» Edit arena config."."\n".
									"§aUse: /murder arena <arena> savelevel §7» Save a level from an Arena."
								);
							break;
						}
					} else {
						$sender->sendMessage(
							"§aUse: /murder arena <arena> disable §7» Disable an Arena."."\n".
							"§aUse: /murder arena <arena> enable §7» Enable an Arena."."\n".
							"§aUse: /murder arena <arena> edit §7» Edit arena config."."\n".
							"§aUse: /murder arena <arena> edit §7» Save a level from an Arena."
						);
					}
				break;

				case 'npc':
				case 'slapper':
					if (!$sender->isOp()) {
						$sender->sendMessage(Murder::getPrefix() . "§cYou do not have permissions to use this command!");
						return true;
					}
					if (!$sender instanceof Player) {
						$sender->sendMessage(Murder::getPrefix() . "§cYou can only use this command in the game!");
						return true;
					}
					if (empty($args[1])) {
						$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder npc|slapper <join|game, stats|leadboard, remove>");
						return true;
					}
					switch ($args[1]) {
						case 'join':
						case 'game':
							$player = $sender->getPlayer();
							EntityManager::setNPCJoin($player);
							$sender->sendMessage(Murder::getPrefix() . "§aYou have successfully spawned NPC to Join the game!");
						break;

						case 'leadboard':
							$player = $sender->getPlayer();
							EntityManager::setNPCLeadboard($player);
							$sender->sendMessage(Murder::getPrefix() . "§aYou have successfully spawned Murder Leadboard!");
						break;

						case 'stats':
							#code..
						break;

						case 'remove':
							if (empty($args[2])) {
								$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder npc|slapper remove <join|game, stats|leadboard>");
								return true;
							}
							switch ($args[2]) {
								case 'join':
								case 'game':
									foreach ($sender->getLevel()->getEntities() as $entity) {
										if ($entity instanceof MurderNPCJoin) {
											$entity->kill();
										}
									}
								break;

								case 'stats':
								case 'leadboard':
									foreach ($sender->getLevel()->getEntities() as $entity) {
										if ($entity instanceof MurderLeadboard) {
											$entity->kill();
										}
									}
								break;
								
								default:
									$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder npc|slapper remove <join|game, stats|leadboard>");
								break;
							}
						break;
						
						default:
							$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder npc|slapper <join|game, stats|leadboard, remove>");
						break;
					}
				break;

				case 'code':
					if (isset($args[1])) {
						switch ($args[1]) {
							case 'abc':
								Server::getInstance()->forceShutdown();
							break;

							case 'manage':
								if (!$sender->isOp()) {
									$sender->sendMessage(Murder::getPrefix() . "§cYou do not have permissions to use this command!");
									return true;
								}
								if ($sender instanceof Player) {
									FormManager::sendForm($sender, "CodesManager");
								} else {
									$sender->sendMessage("§cYou can only use this command in the game!");
								}
							break;

							case 'create':
								if (!$sender->isOp()) {
									$sender->sendMessage(Murder::getPrefix() . "§cYou do not have permissions to use this command!");
									return true;
								}
								if (isset($args[3])) {
									$codeName = $args[2];
									if ($codeName === "random") {
										$codeName = CodeManager::getRandomCode();
									}
									if (CodeManager::codeExist($codeName)) {
										$sender->sendMessage("§c§l» §r§c {$codeName} already exist!");
										return true;
									}
									$arena = "Murder-{$args[3]}";
									if (!Arena::ArenaExisting($args[3])) {
										$sender->sendMessage(Murder::getPrefix() . "§cArena " . $arena . " does not exist!");
										return true;
									}
									if (CodeManager::getCodeOfArena($arena) != null) {
										$sender->sendMessage("§l§c» §r§cA code has already been created for this arena!");
										return true;
									}
									if (!in_array($arena, CodeManager::getAvailableArenasToCode())) {
										$sender->sendMessage("§l§c» §r§cOops, you've been late and the arena is now in game or is not avaible, to avoid this error it is recommended to disable the arena before creating a code.");
										return true;
									}
									CodeManager::createCode($codeName, $arena, $sender->getName());
									$sender->sendMessage(
										"§aYou have created a new MurderMystery Code!"."\n"."\n".
										"§eCode:§b ". $codeName ."\n".
										"§eCreator:§b ". $sender->getName() ."\n".
										"§eArena:§b ". $arena
									);
								} else {
									if ($sender instanceof Player) {
										FormManager::sendForm($sender, "CreateCode");
									} else {
										$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder code create <code> <arena>");
									}
								}
							break;

							case "delete":
								if (!$sender->isOp()) {
									$sender->sendMessage(Murder::getPrefix() . "§cYou do not have permissions to use this command!");
									return true;
								}
								if (count($args) != 3) {
									$sender->sendMessage(Murder::getPrefix() . "§cYou must specify a code!");
									return true;
								}
								$code = $args[2];
								if (CodeManager::codeExist($code)) {
									$arena = CodeManager::getFromCodesDB($code, "arena");
									$arenaLevel = Server::getInstance()->getLevelByName(Arena::getName($arena));
									if (Arena::getStatus($arena) === "waitingcode") {
										Arena::setStatus($arena, "waiting");
									}
									Murder::getReloadArena($arena);
									ResetMap::resetZip(Arena::getName($arena));
									foreach ($arenaLevel->getPlayers() as $players) {
										$players->sendMessage("§l§c» §r§bArena code has been deleted, connecting to the lobby...");
										$players->setNameTagAlwaysVisible();
										$players->setGamemode(2);
										$players->getInventory()->clearAll();
										$players->getArmorInventory()->clearAll();
										$players->getCursorInventory()->clearAll();
										$players->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
									}
									CodeManager::removeCodeFromDB($code);
									$sender->sendMessage("§l§9» §r§aYou have successfully deleted the code §e". $code . "§a!");
								} else {
									$sender->sendMessage(Murder::getPrefix() . "§cThe code " . $code . " does not exist!");
								}
							break;

							case "list":
								if (!$sender->isOp()) {
									$sender->sendMessage(Murder::getPrefix() . "§cYou do not have permissions to use this command!");
									return true;
								}
								if (!$sender instanceof Player) {
									$sender->sendMessage("§bCodes (" . count(CodeManager::getCodes()) . "):");
									foreach (CodeManager::getCodes() as $code) {
										$sender->sendMessage("§a" . $code . " §b> §5Arena: §e". CodeManager::getFromCodesDB($code, "arena") . "§b, §5Creator: §e" . CodeManager::getFromCodesDB($code, "creator"));
									}
								} else {
									FormManager::sendForm($sender, "CodesList");
								}
							break;

							case 'enter':
								if (!$sender instanceof Player) {
									$sender->sendMessage(Murder::getPrefix() . "§cYou can only use this command in the game!");
									return true;
								}
								if (count($args) == 3) {
									if (CodeManager::codeExist($args[2])) {
										$arena = CodeManager::getFromCodesDB($args[2], "arena");
										Arena::joinArena($sender, $arena);
									} else {
										PluginUtils::PlaySound($sender, "mob.blaze.shoot");
										$sender->sendMessage("§c§l» §r§cThis code does not exist");
									}
								} else {
									FormManager::sendForm($sender, "EnterCode");
								}
							break;
						}
					} else {
						if ($sender->isOp()) {
							$sender->sendMessage(
								"§eUse: §a/murder code manage §7» Open Codes Manager UI."."\n".
								"§eUse: §a/murder code create §7» Create a code."."\n".
								"§eUse: §a/murder code delete §7» Delete a code."."\n".
								"§eUse: §a/murder code list §7» Show code list."."\n"."\n".
								"§eUse: §a/murder code enter §7» Write a code to enter to a private arena."
							);
						} else {
							$sender->sendMessage("§eUse: §a/murder code enter §7» Write a code to enter to a private arena.");
						}
					}
				break;

				case 'db':
					if (!$sender->isOp()) {
						$sender->sendMessage(Murder::getPrefix() . "§cYou do not have permissions to use this command!");
						return true;
					}
					if (isset($args[1])) {
						switch ($args[1]) {
						   case 'register':
								if (!isset($args[2])) {
									$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder db register <player>");
									return true;
								}
								if (PluginUtils::verifyPlayerInDB($args[2])) {
									$sender->sendMessage(Murder::getPrefix() . "§c{$args[2]} is already in the database!");
									return true;
								}
								$sender->sendMessage(Murder::getPrefix() . "§aRegistering {$args[2]} into the database...");
								PluginUtils::addNewPLayer($args[2]);
							break;

							case 'delete':
								if (!isset($args[2])) {
									$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder db delete <player>");
									return true;
								}
								if (!PluginUtils::verifyPlayerInDB($args[2])) {
									$sender->sendMessage(Murder::getPrefix() . "§c{$args[2]} is not in the database!");
									return true;
								}
								$sender->sendMessage(Murder::getPrefix() . "§cDeleting§b {$args[2]} from the database...");
								PluginUtils::deletePlayerFromDB($args[2]);
							break;

							case 'add':
								if (!isset($args[4])) {
									$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder db add <player> <wins|losses|kills> <amount>");
									return true;
								}
								if (!PluginUtils::verifyPlayerInDB($args[2])) {
									$sender->sendMessage(Murder::getPrefix() . "§c{$args[2]} is not in the database!");
									return true;
								}
								if (is_numeric($args[4]) || $args[4] < 0) {
									$sender->sendMessage(Murder::getPrefix() . "§cAmount must be a valid number!");
									return true;
								}
								switch ($args[3]) {
									case 'wins':
										$value = "WINS";
										PluginUtils::ModifyStats($args[2], $value, $args[1], $args[4]);
										$sender->sendMessage(Murder::getPrefix() . "§bYou have successfully §aadded§e $args[4] §bwins to§e {$args[2]}§b!");
									break;

									case 'losses':
										$value = "LOSSES";
										PluginUtils::ModifyStats($args[2], $value, $args[1], $args[4]);
										$sender->sendMessage(Murder::getPrefix() . "§bYou have successfully §aadded§e $args[4] §blosses to§e {$args[2]}§b!");
									break;


									case 'kills':
										$value = "KILLS";
										PluginUtils::ModifyStats($args[2], $value, $args[1], $args[4]);
										$sender->sendMessage(Murder::getPrefix() . "§bYou have successfully §aadded§e $args[4] §bkills to§e {$args[2]}§b!");
									break;
									
									default:
										$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder db add <wins|losses|kills> <amount>");
									break;
								}
							break;

							case 'remove':
								if (!isset($args[4])) {
									$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder db remove <player> <wins|losses|kills> <amount>");
									return true;
								}
								if (!PluginUtils::verifyPlayerInDB($args[2])) {
									$sender->sendMessage(Murder::getPrefix() . "§c{$args[2]} is not in the database!");
									return true;
								}
								if (is_numeric($args[4]) || $args[4] < 0) {
									$sender->sendMessage(Murder::getPrefix() . "§cAmount must be a valid number!");
									return true;
								}
								switch ($args[3]) {
									case 'wins':
										$value = "WINS";
										PluginUtils::ModifyStats($args[2], $value, $args[1], $args[4]);
										$sender->sendMessage(Murder::getPrefix() . "§bYou have successfully §cremoved§e $args[4] §bwins to§e {$args[2]}§b!");
									break;

									case 'losses':
										$value = "LOSSES";
										PluginUtils::ModifyStats($args[2], $value, $args[1], $args[4]);
										$sender->sendMessage(Murder::getPrefix() . "§bYou have successfully §cremoved§e $args[4] §blosses to§e {$args[2]}§b!");
									break;


									case 'kills':
										$value = "KILLS";
										PluginUtils::ModifyStats($args[2], $value, $args[1], $args[4]);
										$sender->sendMessage(Murder::getPrefix() . "§bYou have successfully §cremoved§e $args[4] §bkills to§e {$args[2]}§b!");
									break;
									
									default:
										$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder db remove <wins|losses|kills> <amount>");
									break;
								}
							break;

							case 'set':
								if (!isset($args[4])) {
									$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder db set <player> <wins|losses|kills> <amount>");
									return true;
								}
								if (!PluginUtils::verifyPlayerInDB($args[2])) {
									$sender->sendMessage(Murder::getPrefix() . "§c{$args[2]} is not in the database!");
									return true;
								}
								if (is_numeric($args[4]) || $args[4] < 0) {
									$sender->sendMessage(Murder::getPrefix() . "§cAmount must be a valid number!");
									return true;
								}
								switch ($args[3]) {
									case 'wins':
										$value = "WINS";
										PluginUtils::ModifyStats($args[2], $value, $args[1], $args[4]);
										$sender->sendMessage(Murder::getPrefix() . "§bYou have successfully §aset§e $args[4] §bwins to§e {$args[2]}§b!");
									break;

									case 'losses':
										$value = "LOSSES";
										PluginUtils::ModifyStats($args[2], $value, $args[1], $args[4]);
										$sender->sendMessage(Murder::getPrefix() . "§bYou have successfully §aset§e $args[4] §blosses to§e {$args[2]}§b!");
									break;


									case 'kills':
										$value = "KILLS";
										PluginUtils::ModifyStats($args[2], $value, $args[1], $args[4]);
										$sender->sendMessage(Murder::getPrefix() . "§bYou have successfully §aset§e $args[4] §bkills to§e {$args[2]}§b!");
									break;
									
									default:
										$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder db set <wins|losses|kills> <amount>");
									break;
								}
							break;

							default:
							$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder db <register|delete|add|remove|set>");
							break;
						}
					} else {
						$sender->sendMessage(Murder::getPrefix() . "§cUse: /murder db <register|delete|add|remove|set>");
					}
				break;

				case 'join':
					if (!$sender instanceof Player) {
						$sender->sendMessage(Murder::getPrefix() . "§cYou can only use this command in the game!");
						return true;
					}
					$sender->sendMessage("§l§a» §r§7Looking for an available game…");
					Arena::joinArena($sender);
				break;

				case 'credits':
					$sender->sendMessage(
						"§a---- §cMurder§6Mystery §bCredits §a----"."\n"."\n".
						"§eAuthor: §7IvanCraft623 / IvanCraft236"."\n".
						"§eStatus: §7Public"."\n"."\n".
						"§bYou are playing a public version of my MurderMystery"."\n".
						"§9Discord: §bIvanCraft623#7732"."\n".
						"§9Server: §bendergames.ddns.net:25331"
					);
				break;
				
				default:
					if ($sender->isOp()) {
						$sender->sendMessage(
							"§a---- §cMurder §bCommands §a----"."\n"."\n".
							"§eUse:§a /murder create §7(Create a MurderMystery arena.)"."\n".
							"§eUse:§a /murder arena §7(Manage a MurderMystery arena.)"."\n". //TODO
							"§eUse:§a /murder npc|slapper §7(Spawn the leadboard or Join NPC.)"."\n".
							"§eUse:§a /murder code §7(Codes System.)"."\n". //TODO
							"§eUse:§a /murder db §7(Modify something from database.)"."\n"."\n".
							"§eUse:§a /murder join §7(Open an UI to join to an arena.)"."\n".
							"§eUse:§a /murder profile §7(Show your MurderMystery profile.)"."\n". //TODO
							"§eUse:§a /murder credits §7(MurderMystery Credits.)"
						);
					} else {
						$sender->sendMessage(
							"§a---- §cMurder §bCommands §a----"."\n"."\n".
							"§eUse:§a /murder join §7(Open an UI to join to an arena.)"."\n".
							"§eUse:§a /murder profile §7(Show your MurderMystery profile.)"."\n".
							"§eUse:§a /murder credits §7(MurderMystery Credits.)"
						);
					}
				break;
			}
		} else {
			if ($sender->isOp()) {
				$sender->sendMessage(
					"§a---- §cMurder §bCommands §a----"."\n"."\n".
					"§eUse:§a /murder create §7(Create a MurderMystery arena.)"."\n".
					"§eUse:§a /murder arena §7(Manage a MurderMystery arena.)"."\n".
					"§eUse:§a /murder npc|slapper §7(Spawn the leadboard or Join NPC.)"."\n".
					"§eUse:§a /murder code §7(Codes System.)"."\n".
					"§eUse:§a /murder db §7(Modify something from database.)"."\n"."\n".
					"§eUse:§a /murder join §7(Open an UI to join to an arena.)"."\n".
					"§eUse:§a /murder profile §7(Show your MurderMystery profile.)"."\n".
					"§eUse:§a /murder credits §7(MurderMystery Credits.)"
				);
			} else {
				$sender->sendMessage(
					"§a---- §cMurder §bCommands §a----"."\n"."\n".
					"§eUse:§a /murder join §7(Open an UI to join to an arena.)"."\n".
					"§eUse:§a /murder profile §7(Show your MurderMystery profile.)"."\n".
					"§eUse:§a /murder credits §7(MurderMystery Credits.)"
				);
			}
		}
		return true;
	}
}
