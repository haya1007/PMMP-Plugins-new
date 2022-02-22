<?php

namespace ThunderSword;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginBase;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

class Main extends PluginBase{
	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		if(!file_exists($this->getDataFolder())){mkdir($this->getDataFolder(), 0744, true);}

		$this->saveDefaultConfig();
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

		$this->sword = ItemFactory::getInstance()->get(283, 0);
		if($this->config->get("Unbreakable")){
   			$this->sword->setUnbreakable();
   		}
		$this->sword->setCustomName($this->config->get('NAME'));
		$this->sword->setLore(["雷を落とす剣"]);
		$tag = new CompoundTag();
		$tag->setTag("thunder", new IntTag(1));
		$tag->setTag("atk", new IntTag($this->config->get("ATK")));
		$this->sword->setNamedTag($tag);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "sword":
				$sender->getInventory()->addItem($this->sword);
				$thunder = $this->sword->getNamedTag()->getTag("thunder");
				$atk = $this->sword->getNamedTag()->getTag("atk");
				$sender->sendMessage("thunder: ".$thunder."\natk: ".$atk."");

				return true;
		}
	}
}