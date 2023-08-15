<?php

namespace IvanCraft623\MurderMystery\Form;

use IvanCraft623\MurderMystery\{Murder, PluginUtils, Arena\Arena, Code\CodeManager};
use IvanCraft623\MurderMystery\Form\{CustomForm, Form, ModalForm, SimpleForm};

use pocketmine\{Server, Player};

class FormManager {

	public static $target = [];

	public static function sendForm(Player $player, $ui) {
		switch ($ui) {
			case 'GamePanelUI':
				PluginUtils::PlaySound($player, "random.pop2");
				$form = new SimpleForm(function (Player $player, int $data = null) {
					$playerName = $player->getName();
					$result = $data;
					if ($result === null) {
						return true;
					}
					switch ($result) {
						case 0:
							$player->sendMessage("§l§a» §r§7Looking for an available game…");
							Arena::joinArena($player);
						break;

						case 1:
							self::$target[$playerName] = $playerName;
							self::sendForm($player, "ProfileUI");
						break;

						case 2:
							self::sendForm($player, "EnterCode");
						break;
					}
				});
				$form->setTitle("§l§7» §bMurderMystery Game Panel §7«");
				$form->setContent("§7Select an option");
				$form->addButton("§l§9Play\n§r§7Play MurderMystery", 0, "textures/items/iron_sword");
				$form->addButton("§l§9Profile\n§r§7Show your Profile", 0, "textures/ui/gamerpic");
				$form->addButton("§l§9Enter Code\n§r§7Join to an a private arena", 0, "textures/ui/message");
				$form->sendToPlayer($player);
			break;
			
			case 'ProfileUI':
				$form = new SimpleForm(function (Player $player, int $data = null) {
					$result = $data;
					if ($result === null) {
						return true;
					}
					switch ($result) {
						case 0:
							// XD
						break;
					}
				});
				$form->setTitle("§e" . self::$target[$player->getName()] . " MurderMystery Stats");
				$form->setContent(
					"§l§3Games Played §r§b" . PluginUtils::getFromStatsDB(self::$target[$player->getName()], "GAMESPLAYED") . "\n".
					"§l§3Victories §r§b" . PluginUtils::getFromStatsDB(self::$target[$player->getName()], "WINS") . "\n".
					"§l§3Losses §r§b" . PluginUtils::getFromStatsDB(self::$target[$player->getName()], "LOSSES") . "\n".
					"§l§3Murders §r§b" . PluginUtils::getFromStatsDB(self::$target[$player->getName()], "KILLS") . "\n".
					"§l§3Deaths §r§b" . PluginUtils::getFromStatsDB(self::$target[$player->getName()], "DEATHS") . "\n".
					"§l§3Murderer Eliminations §r§b" . PluginUtils::getFromStatsDB(self::$target[$player->getName()], "MURDERERELIMINATIONS")
				);
				$form->sendToPlayer($player);
				unset(self::$target[$player->getName()]);
			break;

			case 'EnterCode':
				PluginUtils::PlaySound($player, "random.pop2");
				$form = new CustomForm(function (Player $player, array $data = null) {
					if ($data === null) {
						return true;
					}
					if (CodeManager::codeExist($data[1])) {
						$arena = CodeManager::getFromCodesDB($data[1], "arena");
						Arena::joinArena($player, $arena);
					} else {
						PluginUtils::PlaySound($player, "mob.blaze.shoot");
						$player->sendMessage("§c§l» §r§cThis code does not exist");
					}
				});
				$form->setTitle("§l§7» §bMurderMystery Enter Code §7«");
				$form->addLabel("§fInsert the Code...");
				$form->addInput("Code:", "Code123");
				$form->sendToPlayer($player);
			break;

			case 'CodesManager':
				$form = new SimpleForm(function (Player $player, int $data = null) {
					$playerName = $player->getName();
					$result = $data;
					if ($result === null) {
						return true;
					}
					switch ($result) {
						case 0:
							self::sendForm($player, "EnterCode");
						break;

						case 1:
							self::sendForm($player, "CreateCode");
						break;
						
						case 2:
							self::sendForm($player, "CodeList");
						break;

						case 3:
							self::sendForm($player, "CodeList");
						break;
					}
				});
				$form->setTitle("§l§9Codes Manager");
				$form->setContent("§7Select an option");
				$form->addButton("§l§9Enter Code\n§r§7To jon to an a private arena", 1, "https://raw.githubusercontent.com/IvanCraft623/EnderGames-Images/main/Images/Ranks%20Manager/crown.png");
				$form->addButton("§l§9Create a Code\n§r§7To create a private arena", 1, "https://raw.githubusercontent.com/IvanCraft623/EnderGames-Images/main/Images/Minecraft-Icons/Special%20Icons/folder%20mojang.png");
				$form->addButton("§l§9Code List\n§r§7Show a list of codes", 0, "textures/items/map_filled");
				$form->addButton("§l§9Delete Code\n§r§7Detelete a code", 0, "textures/ui/icon_trash");
				$form->sendToPlayer($player);
			break;

			case 'CreateCode':
				$form = new CustomForm(function (Player $player, array $data = null) {
					if ($data === null) {
						return true;
					}
					$codeName = $data[2];
					if ($codeName === "random") {
						$codeName = CodeManager::getRandomCode();
					}
					if (CodeManager::codeExist($codeName)) {
						Murder::$data["dataCode"][$player->getName()]["Error"] = "§c§l» §r§c {$codeName} already exist!";
						self::sendForm($player, "CreateCode");
						return true;
					}
					Murder::$data["dataCode"][$player->getName()]["codeName"] = $codeName;
					self::sendForm($player, "CreateCode2");
				});
				$form->setTitle("§l§9Create MurderMystery Code");
				$form->addLabel("§7If you write random a random code will be generated...");
				if (isset(Murder::$data["dataCode"][$player->getName()]["Error"])) {
					$form->addLabel(Murder::$data["dataCode"][$player->getName()]["Error"]);
					unset(Murder::$data["dataCode"][$player->getName()]["Error"]);
				} else {
					$form->addLabel("§fInsert the Code...");
				}
				$form->addInput("Code:", "Code123");
				$form->sendToPlayer($player);
			break;

			case 'CreateCode2':
				if(!isset(Murder::$data["dataCode"][$player->getName()]["codeName"])) {//It is almost impossible for this error to happen xD
					$player->sendMessage("§c§l» §r§cOops, an unexpected error occurred: Missing code name!");
					return;
				}
				$form = new SimpleForm(function (Player $player, $data = null) {
					$arena = $data;
					if ($arena === null) {
						return true;
					}
					if (CodeManager::getCodeOfArena($arena) !== null) {
						Murder::$data["dataCode"][$player->getName()]["Error"] = "§l§c» §r§cA code has already been created for this arena!";
						self::sendForm($player, "CreateCode2");
						return true;
					}
					if (!in_array($arena, CodeManager::getAvailableArenasToCode())) {
						Murder::$data["dataCode"][$player->getName()]["Error"] = "§l§c» §r§cOops, you've been late and the arena is now in game or is not avaible, to avoid this error it is recommended to disable the arena before creating a code.";
						self::sendForm($player, "CreateCode2");
						return true;
					}
					$player->sendMessage(
						"§aYou have created a new MurderMystery Code!"."\n"."\n".
						"§eCode:§b ". Murder::$data["dataCode"][$player->getName()]["codeName"] ."\n".
						"§eCreator:§b ". $player->getName() ."\n".
						"§eArena:§b ". $arena
					);
					CodeManager::createCode(Murder::$data["dataCode"][$player->getName()]["codeName"], $arena, $player->getName());
					Arena::joinArena($player, $arena);
					unset(Murder::$data["dataCode"][$player->getName()]);
				});
				$form->setTitle("§l§9Create MurderMystery Code");
				if (isset(Murder::$data["dataCode"][$player->getName()]["Error"])) {
					$form->setContent(Murder::$data["dataCode"][$player->getName()]["Error"]);
					unset(Murder::$data["dataCode"][$player->getName()]["Error"]);
				} else {
					$form->setContent(
						"§fSelect a Arena for " . Murder::$data["dataCode"][$player->getName()]["codeName"] . " Code..." ."\n".
						"§7If you cannot find an specific arena, it is probably that it is in game and you should disable it."
					);
				}
				foreach(CodeManager::getAvailableArenasToCode() as $arena) {
					$form->addButton("§l§9" . $arena . "\n§r§5Map: §b" . Arena::getName($arena), -1, "", $arena);
				}
				$form->sendToPlayer($player);
				return $form;
			break;

			case 'CodeList':
				$form = new SimpleForm(function (Player $player, $data = null) {
					$code = $data;
					if ($code === null) {
						return true;
					}
					self::$target[$player->getName()] = $code;
					self::sendForm($player, "CodeInfo");
				});
				$form->setTitle("§l§9MurderMystery Codes List");
				$form->setContent("§bCodes (" . count(CodeManager::getCodes()) . "):");
				foreach (CodeManager::getCodes() as $code) {
					$form->addButton("§l§9" . $code . "\n§r§5Arena: §e" . CodeManager::getFromCodesDB($code, "arena"), -1, "", $code);
				}
				$form->sendToPlayer($player);
				return $form;
			break;

			case 'CodeInfo':
				if (self::$target[$player->getName()] === null) {
					$sender->sendMessage("§c§l» §r§cAn unexpected error occurred!");
				}
				$form = new SimpleForm(function (Player $player, $data = null) {
					if ($data === null) {
						return true;
					}
					Server::getInstance()->dispatchCommand($player, 'murder code delete "'.$data.'"');
				});
				$code = self::$target[$player->getName()];
				unset(self::$target[$player->getName()]);
				$arena = CodeManager::getFromCodesDB($code, "arena");
				$form->setTitle("§l§9MurderMystery Codes Info");
				$form->setContent(
					"§eCode: §l§9" . $code ."\n"."\n".
					"§r§5Creator: §l§e" . CodeManager::getFromCodesDB($code, "creator") ."\n".
					"§r§5Arena: §l§e" . $arena ."\n".
					"§r§5Map: §l§e" . Arena::getName($arena)
				);
				$form->sendToPlayer($player);
				$form->addButton("§l§cDelete Code\n§r§7Detelete the code", 0, "textures/ui/icon_trash", $code);
				$form->sendToPlayer($player);
			break;
		}
	}
}
