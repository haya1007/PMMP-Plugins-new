<?php

namespace ThunderSword;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginBase;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;

use pocketmine\nbt\tag\IntTag;

class Main extends PluginBase{
	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		if(!file_exists($this->getDataFolder())){mkdir($this->getDataFolder(), 0744, true);}

		$this->saveDefaultConfig();
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

		// 雷の剣
		$this->sword = ItemFactory::getInstance()->get(283, 0); // 金の剣の取得
		$this->sword->setCustomName($this->config->get('NAME')); // 名前の変更
		$this->sword->setLore([$this->config->get('LORE')]); // アイテムの説明欄
		if($this->config->get("Unbreakable")){
   			$this->sword->setUnbreakable(); // 壊れないようにする
   		}
		$tag = $this->sword->getNamedTag(); // nbt取得
		$tag->setTag("thunder", new IntTag(1)); // 雷の剣なのかの判定用
		$tag->setTag("atk", new IntTag($this->config->get("ATK"))); // ダメージ量
		$this->sword->setNamedTag($tag); // nbtの適用
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "sword":
				$sender->getInventory()->addItem($this->sword);

				return true;
		}
	}
}