<?php

declare(strict_types=1);

namespace IvanCraft623\MurderMystery\Entity;

use pocketmine\{PLayer, Server};
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\entity\{Entity, Human, Skin};
use pocketmine\nbt\tag\{CompoundTag, ByteArrayTag, StringTag};

use IvanCraft623\MurderMystery\{Murder, Arena\Arena};
use IvanCraft623\MurderMystery\Entity\{MurderLeadboard, MurderNPCJoin, MurderNPCStats, MurderTomb, MurderCoin};

final class EntityManager {

	public static function setNPCJoin(Player $player) {
		$nbt = Entity::createBaseNBT($player, null, 2, 2);
		$nbt->setTag($player->namedtag->getTag("Skin"));
		$npc = new MurderNPCJoin($player->getLevel(), $nbt);
		$npc->setNameTag('');
		$npc->setNameTagAlwaysVisible(true);
		$npc->spawnToAll();
	}

	public static function setNPCLeadboard(Player $player) {
		$nbt = Entity::createBaseNBT($player, null, 2, 2);
		$nbt->setTag($player->namedtag->getTag("Skin"));
		$npc = new MurderLeadboard($player->getLevel(), $nbt);
		$npc->setNameTag('');
		$npc->setNameTagAlwaysVisible(true);
		$npc->spawnToAll();
	}

	public static function setNPCPoliceHat(Player $player) {
		$nbt = Entity::createBaseNBT($player, null, 2, 2);
		$dir = Murder::getInstance()->getDataFolder() . "Entities" . DIRECTORY_SEPARATOR . "Skins" . DIRECTORY_SEPARATOR . "PoliceHat.png";
		$img = @imagecreatefrompng($dir);
		$skinbytes = '';
		$values = (int)@getimagesize($dir)[1];
		for($y = 0; $y < $values; $y++) {
			for($x = 0; $x < 64; $x++) {
				$bytes = @imagecolorat($img, $x, $y);
				$a = ((~((int)($bytes >> 24))) << 1) & 0xff;
				$b = ($bytes >> 16) & 0xff;
				$c = ($bytes >> 8) & 0xff;
				$d = $bytes & 0xff;
				$skinbytes .= chr($b) . chr($c) . chr($d) . chr($a);
			}
		}
		@imagedestroy($img);
		$skinTag = new CompoundTag("Skin", [
			"Name" => new StringTag("Name", $player->getSkin()->getSkinId()),
			"Data" => new ByteArrayTag("Data", $skinbytes),
			"GeometryName" => new StringTag("GeometryName", "geometry.geometry.policehat"),
			"GeometryData" => new ByteArrayTag("GeometryData", file_get_contents(Murder::getInstance()->getDataFolder() . "Entities" . DIRECTORY_SEPARATOR . "Geometries" . DIRECTORY_SEPARATOR . "PoliceHatGeometry.json"))
		]);
		$nbt->setTag($skinTag);
		$npc = new MurderPoliceHat($player->getLevel(), $nbt);
		$npc->setNameTagAlwaysVisible(false);
		$npc->setNameTagVisible(false);
		$npc->yaw = $player->getYaw();
		$npc->spawnToAll();
	}

	public static function setNPCTomb(Player $player) {
		$nbt = Entity::createBaseNBT($player, null, 2, 2);
		$dir = Murder::getInstance()->getDataFolder() . "Entities" . DIRECTORY_SEPARATOR . "Skins" . DIRECTORY_SEPARATOR . "Tomb.png";
		$img = @imagecreatefrompng($dir);
		$skinbytes = '';
		$values = (int)@getimagesize($dir)[1];
		for($y = 0; $y < $values; $y++) {
			for($x = 0; $x < 64; $x++) {
				$bytes = @imagecolorat($img, $x, $y);
				$a = ((~((int)($bytes >> 24))) << 1) & 0xff;
				$b = ($bytes >> 16) & 0xff;
				$c = ($bytes >> 8) & 0xff;
				$d = $bytes & 0xff;
				$skinbytes .= chr($b) . chr($c) . chr($d) . chr($a);
			}
		}
		@imagedestroy($img);
		$skinTag = new CompoundTag("Skin", [
			"Name" => new StringTag("Name", $player->getSkin()->getSkinId()),
			"Data" => new ByteArrayTag("Data", $skinbytes),
			"GeometryName" => new StringTag("GeometryName", "geometry.geometry.tomb"),
			"GeometryData" => new ByteArrayTag("GeometryData", file_get_contents(Murder::getInstance()->getDataFolder() . "Entities" . DIRECTORY_SEPARATOR . "Geometries" . DIRECTORY_SEPARATOR . "TombGeometry.json"))
		]);
		$nbt->setTag($skinTag);
		$npc = new MurderTomb($player->getLevel(), $nbt);
		$npc->setNameTagAlwaysVisible(false);
		$npc->setNameTagVisible(false);
		$npc->yaw = $player->getYaw();
		$npc->spawnToAll();
	}

	public static function setNPCCoin($coinPos, $arenaLevel, $coin = null) {
		$nbt = Entity::createBaseNBT($coinPos, null, 2, 2);
		$dir = Murder::getInstance()->getDataFolder() . "Entities" . DIRECTORY_SEPARATOR . "Skins" . DIRECTORY_SEPARATOR . "Coin.png";
		$img = @imagecreatefrompng($dir);
		$skinbytes = '';
		$values = (int)@getimagesize($dir)[1];
		for($y = 0; $y < $values; $y++) {
			for($x = 0; $x < 64; $x++) {
				$bytes = @imagecolorat($img, $x, $y);
				$a = ((~((int)($bytes >> 24))) << 1) & 0xff;
				$b = ($bytes >> 16) & 0xff;
				$c = ($bytes >> 8) & 0xff;
				$d = $bytes & 0xff;
				$skinbytes .= chr($b) . chr($c) . chr($d) . chr($a);
			}
		}
		if ($coin === null) {
			$coin = "Murder.Coin";
		}
		@imagedestroy($img);
		$skinTag = new CompoundTag("Skin", [
			"Name" => new StringTag("Name", $coin),
			"Data" => new ByteArrayTag("Data", $skinbytes),
			"GeometryName" => new StringTag("GeometryName", "geometry.geometry.coin"),
			"GeometryData" => new ByteArrayTag("GeometryData", file_get_contents(Murder::getInstance()->getDataFolder() . "Entities" . DIRECTORY_SEPARATOR . "Geometries" . DIRECTORY_SEPARATOR . "CoinGeometry.json"))
		]);
		$nbt->setTag($skinTag);
		$npc = new MurderCoin($arenaLevel, $nbt);
		$npc->setNameTagAlwaysVisible(false);
		$npc->spawnToAll();
	}
}
