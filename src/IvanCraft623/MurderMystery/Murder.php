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

namespace IvanCraft623\MurderMystery;

use IvanCraft623\MurderMystery\{API\ScoreAPI, Arena\Arena, Entity\MurderNPCJoin, Entity\MurderLeadboard, Entity\MurderTomb, Entity\MurderPoliceHat, Entity\MurderCoin, Command\MurderCommand, Tasks\GameScheduler, Tasks\NPCRotation};

use pocketmine\{Server, Player, plugin\PluginBase, entity\Entity, utils\Config};

class Murder extends PluginBase {

	public $db = [];

	public static $instance;

	public static $score;

	public static $data = [
		'prefix' => '§b[§l§cMurder§r§b] §r',
		'id' => '',
		'vote' => [],
		'skins' => [],
		'players' => [],
		'coins' => [],
		'configurator' => [],
		'arenaconfigs' => [],
		'interactDelay' => [],
		'coinDelay' => [],
		'giveArow' => [],
		'dataCode' => [],
	];

	public function onLoad() : void {
		self::$instance = $this;
		self::$score = new ScoreAPI($this);
	}

	public function onEnable() : void {
		$this->loadDatabase();
		$this->saveResources();
		foreach (Arena::getArenas() as $arena) {
			if (count(Arena::getArenas()) >= 0) {
				if (Arena::getStatus($arena) != "disabled") {
					self::getReloadArena($arena);
					ResetMap::resetZip(Arena::getName($arena));
				}
			}
		}
		$this->loadEntitys();
		$this->loadCommands();
		$this->loadEvents();
		$this->loadTasks();
		$this->getLogger()->info('§aMurderMystery loaded succesfully');
	}

	public static function getInstance() : self {
		return self::$instance;
	}

	public static function getPrefix() : string {
		return self::$data['prefix'];
	}
	public static function getScore() : ScoreAPI {
		return self::$score;
	}

	public static function getConfigs(string $value) : config {
		return new Config(self::getInstance()->getDataFolder() . "{$value}.yml", Config::YAML);
	}

	public static function getReloadArena(string $arena) {
		$config = self::getConfigs('Arenas/' . $arena);
		self::$data['coins'][Arena::getName($arena)] = ['Steve' => 0, 'Enderman' => 0];
		if (Arena::getStatus($arena) !== "waitingcode") {
			$config->set('status', 'waiting');
		}
		$config->set('lobbytime', 40);
		$config->set('startingtime', 11);
		$config->set('gametime', 300);
		$config->set('endtime', 16);
		$config->save();
	}

	public function saveResources() : void {
		$folder = $this->getDataFolder();
		foreach([$folder, $folder . 'Arenas', $folder . 'Backups'] as $dir) {
			if (!is_dir($dir)) {
				@mkdir($dir);
			}
		}
		$this->saveResource('Entities/Geometries/TombGeometry.json');
		$this->saveResource('Entities/Geometries/PoliceHatGeometry.json');
		$this->saveResource('Entities/Geometries/CoinGeometry.json');
		$this->saveResource('Entities/Skins/Tomb.png');
		$this->saveResource('Entities/Skins/PoliceHat.png');
		$this->saveResource('Entities/Skins/Coin.png');
	}

	public function loadDatabase() : void {
		$this->db = new \SQLite3($this->getDataFolder() . "Murder.db");
		# Stats Database
		$this->db->exec('CREATE TABLE IF NOT EXISTS MurderStats (
			player TEXT NOT NULL,
			gamesPlayed INT NOT NULL,
			wins INT NOT NULL,
			losses INT NOT NULL,
			kills INT NOT NULL,
			deaths INT NOT NULL,
			murdererEliminations INT NOT NULL,
			UNIQUE(player)
		)');
		# Codes Database
		$this->db->exec('CREATE TABLE IF NOT EXISTS MurderCodes (
			code TEXT NOT NULL,
			arena TEXT NOT NULL,
			creator TEXT NOT NULL,
			UNIQUE(code)
		)');
		# MurderRanks Database //TODO
	}

	public function loadEntitys() : void {
		$values = [MurderNPCJoin::class, MurderLeadboard::class, MurderTomb::class, MurderPoliceHat::class, MurderCoin::class];
		foreach ($values as $entitys) {
			Entity::registerEntity($entitys, true);
		}
		unset ($values);
	}

	public function loadCommands() : void {
		$values = [new MurderCommand($this)];
		foreach ($values as $commands) {
			$this->getServer()->getCommandMap()->register('_cmd', $commands);
		}
		unset($values);
	}

	public function loadEvents() : void {
		$values = [new EventListener($this)];
		foreach ($values as $events) {
			$this->getServer()->getPluginManager()->registerEvents($events, $this);
		}
		unset($values);
	}

	public function loadTasks() : void {
		$this->getScheduler()->scheduleRepeatingTask(new GameScheduler($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new NPCRotation($this), 5);
	}
}
