<?php

declare(strict_types=1);

namespace IvanCraft623\MurderMystery\Entity;

use pocketmine\{Server, Player, item\Item};
use pocketmine\entity\Human;

use IvanCraft623\MurderMystery\{Murder, PluginUtils, Arena\Arena};

class MurderCoin extends Human {

	public function getName() : string {
		return "";
	}

	public function onUpdate(int $currentTick) : bool {
		//Scale
		$this->setScale(0.5);
		//Rotate
		$this->yaw+=3.2;
		$this->move($this->motion->x, $this->motion->y, $this->motion->z);
		$this->updateMovement();
		return parent::onUpdate($currentTick);
	}

	public function onCollideWithPlayer(Player $player) : void {
		foreach (Arena::getArenas() as $arena) {
			$arenaName = Arena::getName($arena);
			$arenaLevel = Server::getInstance()->getLevelByName($arenaName);
			if ($player->getLevel()->getFolderName() == $arenaName) {
				if (Arena::getStatus($arena) == 'ingame') {
					if (Arena::getRole($player, $arena) != null) {
						Murder::$data["coinDelay"][$arenaName][$this->skin->getSkinId()] = time() + 30;
						Murder::$data["coins"][$arenaName][$player->getName()] = Murder::$data["coins"][$arenaName][$player->getName()] + 1;
						PluginUtils::PlaySound($player, "item.trident.return", 1, 1.4);
						$this->flagForDespawn();
						if (Murder::$data["coins"][$arenaName][$player->getName()] == 10) {
							if (Arena::getRole($player, $arena) != "Inoccent") return;
							$player->sendMessage("§l§a» §bYou have received a one shot bow! §cUse wisely or it could cost you your life");
							$player->getInventory()->clearAll();
							$player->getArmorInventory()->clearAll();
							$player->getCursorInventory()->clearAll();
							$player->getInventory()->setItem(1, Item::get(261, 0, 1)->setCustomName("§r§6Inoccent Bow"));
							$player->getInventory()->setItem(2, Item::get(262, 0, 1));
						}
					}
				}
			}
		}
	}
}
