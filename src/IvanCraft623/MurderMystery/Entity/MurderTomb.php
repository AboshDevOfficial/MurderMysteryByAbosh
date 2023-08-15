<?php

declare(strict_types=1);

namespace IvanCraft623\MurderMystery\Entity;

use pocketmine\entity\{Monster, EntityIds};
use pocketmine\entity\Human;

class MurderTomb extends Human {

	public function getName() : string {
		return "";
	}

	public function onUpdate(int $currentTick) : bool {
		//Scale
		$this->setScale(0.5);
		return parent::onUpdate($currentTick);
	}
}