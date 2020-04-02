<?php

namespace TutorialPlugin\chest;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\inventory\transaction\action\SlotChangeAction;

use muqsit\invmenu\InvMenu;
use TutorialPlugin\Main;

class ChestCustom{
	public function __construct(Main $main){
		$this->main = $main;
		$this->chest = $main->getChest();
		$this->inventory = $this->chest->getInventory();
	}

	public function ChestCustom(string $name, bool $readOnly = true){
		if($readOnly){
			$this->chest->readonly();
		}
		$this->chest->setName($name);

		$this->chest->setListener(
			function(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : void{
				$inv = $player->getInventory();
				$item_name = $itemClicked->getName();
				if($inv->canAddItem($itemClicked)){
					$inv->addItem($itemClicked);
					$player->sendMessage("§a".$item_name."§rをインベントリに追加しました");
				}else{
					$player->sendMessage("§cインベントリがいっぱいです");
				}
			}
		);


		$setItem = new ChestSetItem($this->main, $this->inventory);
		$setItem->setItem(); 
		return $this->chest;
	}

	public function set(int $number, Item $item){
		$this->inventory->setItem($number, $item);
	}
}