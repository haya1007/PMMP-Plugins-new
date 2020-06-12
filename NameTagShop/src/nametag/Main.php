<?php

namespace nametag;

use pocketmine\plugin\PluginBase;

use pocketmine\event\block\SignChangeEvent;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use pocketmine\Player;
use pocketmine\utils\Config;

class Main extends PluginBase{
	public function onEnable(){
		$this->getLogger()->info("NameTagShopが起動しました");
		if($this->getServer()->getPluginManager()->getPlugin("FierceBattle") !== null){
			$this->fb = $this->getServer()->getPluginManager()->getPlugin("FierceBattle");
			$this->getLogger()->info("FierceBattleを読み込ました");
		}else{
			$this->getLogger()->warning("FierceBattleを読み込めませんでした");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}

		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		if(!file_exists($this->getDataFolder())) mkdir($this->getDataFolder());
		$this->shop = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
		$this->cancel = new Config($this->getDataFolder() . "cancel.yml", Config::YAML);
	}

	public function getVar($block){
		return 	(Int) $block->getX().":".(Int)$block->getY().":".$block->getZ().":".$block->getLevel()->getFolderName();
	}

	public function setSign(SignChangeEvent $event, int $price, string $tag){
		$event->setLine(0, "§bNameTagShop");
		$event->setLine(1, "§eタグ §r: ".$tag);
		$event->setLine(2, "§a値段 §r: §a".$price);
	}

	public function createWindow(Player $player, $data, int $id){
		$pk = new ModalFormRequestPacket();
		$pk->formId = $id;
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$player->dataPacket($pk);
	}
}