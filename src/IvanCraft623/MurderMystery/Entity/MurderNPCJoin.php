<?php

namespace IvanCraft623\MurderMystery\Entity;

use pocketmine\Server;
use pocketmine\entity\Human;
use pocketmine\level\Level;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\nbt\tag\CompoundTag;

use IvanCraft623\MurderMystery\{Murder, Arena\Arena};

class MurderNPCJoin extends Human {

	/**
	 * MainEntity constructor.
	 * @param Level $level
	 * @param CompoundTag $nbt
	 */
	public function __construct(Level $level, CompoundTag $nbt) {
		parent::__construct($level, $nbt);
		$this->setNameTagAlwaysVisible(true);
		$this->setNameTagVisible(true);
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return '';
	}

	/**
	 * @param int $currentTick
	 * @return bool
	 */
	public function onUpdate(int $currentTick): bool {
		if ($this->getScale() != 1.2) {
			$this->setScale(1.2);
		}
		$this->addEffect(new EffectInstance(Effect::getEffect(Effect::JUMP), 999));
		$this->setNameTag(
			"§l§b» §7CLICK TO JOIN §l§b«" . "\n" . 
			"§l§cMurder§6Mystery §r§8[§a1.0.0§8]" . "\n" . 
			"§e" . $this->getAllPlayers() . " Online");
		return parent::onUpdate($currentTick);
	}

	/**
	 * @return int
	 */
	public function getAllPlayers() : int {
    	$players = [];
        foreach (Arena::getArenas() as $arena) {
        	if (Server::getInstance()->getLevelByName(Arena::getName($arena)) !== null) {
        	    foreach (Server::getInstance()->getLevelByName(Arena::getName($arena))->getPlayers() as $player) {
        	        array_push($players, $player->getName());
        	    }
        	}
        }
        return count($players);
    }
}