<?php

declare(strict_types=1);

namespace IvanCraft623\MurderMystery\Entity;


use pocketmine\{Server, Player};
use pocketmine\entity\{Human, Monster, EntityIds};
use pocketmine\entity\{Effect, EffectInstance};
use pocketmine\item\Item;

use IvanCraft623\MurderMystery\{Murder, PluginUtils, Arena\Arena};

class MurderPoliceHat extends Human {

	public function getName() : string {
		return "";
	}

	public function onUpdate(int $currentTick) : bool {
		//Scale
		$this->setScale(0.625);
		//Rotate
		$this->yaw+=2.25;
		$this->move($this->motion->x, $this->motion->y, $this->motion->z);
		$this->updateMovement();
		//Effect
		$this->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 999));
		return parent::onUpdate($currentTick);
	}

	public function onCollideWithPlayer(Player $player) : void {
		foreach (Arena::getArenas() as $arena) {
			$arenaName = Arena::getName($arena);
			$arenaLevel = Server::getInstance()->getLevelByName($arenaName);
			if ($player->getLevel()->getFolderName() == $arenaName) {
				if (Arena::getStatus($arena) == 'ingame') {
					if ($player->hasEffect(Effect::BLINDNESS)) return;
					if (Arena::getRole($player, $arena) === "Inoccent") {
						unset(Murder::$data["players"][$arenaName]["Inoccent"][$player->getName()]);
						Murder::$data["players"][$arenaName]["Detective"][$player->getName()] = "Alive";
						$player->getInventory()->clearAll();
						$player->getArmorInventory()->clearAll();
						$player->getCursorInventory()->clearAll();
						$player->getInventory()->setItem(1, Item::get(261, 0, 1)->setCustomName("§r§9Detective Bow"));
						$player->getInventory()->setItem(2, Item::get(262, 0, 1));
						$player->sendMessage(
							"§l§9» §bYou have become the Detective"."\n".
							"§l§9» §r§fEliminate the murderer using your Bow! §7Be careful, wrongful kills will leave you vulnerable."
						);
						foreach ($arenaLevel->getPlayers() as $players) {
							$players->sendMessage("§l§9» §bA new Detective has arrived...");
						}
						PluginUtils::PlaySound($player, "mob.zombie.unfect");
						$this->flagForDespawn();
					}
				}
			}
		}
	}
}