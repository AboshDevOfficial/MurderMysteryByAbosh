<?php

namespace IvanCraft623\MurderMystery\Entity;

use pocketmine\entity\{Human, Skin};
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;

use IvanCraft623\MurderMystery\{Murder, PluginUtils};

class MurderLeadboard extends Human {

	public function __construct(Level $level, CompoundTag $nbt) {
		parent::__construct($level, $nbt);
		$this->setSkin(new Skin('Standard_Custom', str_repeat("\x00", 8192)));
		$this->sendSkin();
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		$this->setNameTag($this->getLeaderboardText());
		return parent::entityBaseTick($tickDiff);
	}

	private function getLeaderboardText(): string {
		return PluginUtils::getMurderLeadboard();
	}
}