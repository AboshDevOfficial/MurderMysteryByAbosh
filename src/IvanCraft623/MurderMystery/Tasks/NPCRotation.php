<?php

declare(strict_types=1);

namespace IvanCraft623\MurderMystery\Tasks;

use IvanCraft623\MurderMystery\{Murder, PluginUtils, Entity\MurderNPCJoin};

use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\utils\{Config};
use pocketmine\{Player, Server};
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\level\{Level, Position};
use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class NPCRotation extends Task {
	
	/**
	 * @param Int $currentTick
	 */
	public function onRun(Int $currentTick){
		$level = Murder::getInstance()->getServer()->getDefaultLevel();
		foreach ($level->getEntities() as $entity) {
			if ($entity instanceof MurderNPCJoin) {
				$this->sendMovement($entity);
			}
		}
	}
	
	public function sendMovement($entity) {
		foreach ($entity->getLevel()->getNearbyEntities($entity->getBoundingBox()->expandedCopy(15, 15, 15), $entity) as $player) {
			if($player instanceof Player){
				$xdiff = $player->x - $entity->x;
				$zdiff = $player->z - $entity->z;
				$angle = atan2($zdiff, $xdiff);
				$yaw = (($angle * 180) / M_PI) - 90;
				$ydiff = $player->y - $entity->y;
				$v = new Vector2($entity->x, $entity->z);
				$dist = $v->distance($player->x, $player->z);
				$angle = atan2($dist, $ydiff);
				$pitch = (($angle * 180) / M_PI) - 90;
				$pk = new MovePlayerPacket();
				$pk->entityRuntimeId = $entity->getId();
				$pk->position = $entity->asVector3()->add(0, $entity->getEyeHeight(), 0);
				$pk->yaw = $yaw;
				$pk->pitch = $pitch;
				$pk->headYaw = $yaw;
				$pk->onGround = $entity->onGround;
				$player->dataPacket($pk);
			}
		}
	}
}
