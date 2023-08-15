<?php

declare(strict_types=1);

namespace IvanCraft623\MurderMystery;

use pocketmine\{Server, Player};

use IvanCraft623\MurderMystery\{Murder};

class ResetMap {

	public static function resetZip(string $arena) {
		if (Server::getInstance()->isLevelLoaded($arena)) {
			Server::getInstance()->unloadLevel(Server::getInstance()->getLevelByName($arena));
		}
		//Delete World
		self::removeDir(Server::getInstance()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $arena);
		//Set Zip
		$zipPath = Murder::getInstance()->getDataFolder() . 'Backups' . DIRECTORY_SEPARATOR . $arena . '.zip';
		$zipArchive = new \ZipArchive();
		$zipArchive->open($zipPath);
		$zipArchive->extractTo(Murder::getInstance()->getServer()->getDataPath() . 'worlds');
		$zipArchive->close();
		Server::getInstance()->loadLevel($arena);
		Murder::getInstance()->getLogger()->info('§a' . $arena . ' §barena has been reset successfully.');
		return true;
	}

	public static function removeDir(string $dirPath) {
		if (basename($dirPath) == "." || basename($dirPath) == "..") {
			return;
		}
		foreach (scandir($dirPath) as $item) {
			if ($item != "." || $item != "..") {
				if (is_dir($dirPath . DIRECTORY_SEPARATOR . $item)) {
					self::removeDir($dirPath . DIRECTORY_SEPARATOR . $item);
				}
				if (is_file($dirPath . DIRECTORY_SEPARATOR . $item)) {
					self::removeFile($dirPath . DIRECTORY_SEPARATOR . $item);
				}
			}
		}
		rmdir($dirPath);
	}

	public static function removeFile(string $path) {
		unlink($path);
	}
}
