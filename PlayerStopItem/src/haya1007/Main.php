<?php

namespace haya1007;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;

use pocketmine\Player;
use pocketmine\item\Item;

use pocketmine\nbt\tag\IntTag;

class Main extends PluginBase{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		$this->saveDefaultConfig();
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

		$this->stopItem = new StopItem($this);

		Item::addCreativeItem($this->getItem());
	}

	public function getItem(){
		$limit = $this->stopItem->getMaxLimit();

		$item = Item::get($this->stopItem->getId(), $this->stopItem->getMeta());

		$item->setCustomName($this->stopItem->getName());

		if($limit > 0){

			$item->setNamedTagEntry(new IntTag("break", $limit));

			$item->setLore(["殴った相手の動きを一定確率で止める、しかし". $limit ."回使うと壊れる",
							"あと". $limit ."回使えます"]);
		} else {
			$item->setLore(["殴った相手の動きを一定確率で止める"]);	
		}

		return $item;
	}

	public function update_item(Player $player, Item $item){
		$break = $item->getNamedTagEntry("break")->getValue() - 1;
		if($break == 0){

			$player->getInventory()->removeItem($item);
			$player->sendMessage("§c壊れちゃった！！");
		} else {

			$item->setNamedTagEntry(new IntTag("break", $break));

			$item->setLore(["殴った相手の動きを一定確率で止める、しかし". $this->stopItem->getMaxLimit() ."回使うと壊れる",
							"あと". $break ."回使えます"]);
			$player->getInventory()->setItemInHand($item);
		}
	}

	public function check($nbt, $item){
		$nbt_name = $nbt->getName();
		$item_name = $item->getName();
		return ($nbt_name === $item_name) ? true : false;
	}
}