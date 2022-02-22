<?php

namespace inv;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;

class main extends PluginBase implements Listener{

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onBreak(BlockBreakEvent $event) : void{
		$player = $event->getPlayer();
		$drop = $event->getDrops();
		$event->setDrops([]);
		#$world = $player->getWorld()->getFolderName();
		foreach($drop as $item){
			$this->getScheduler()->scheduleDelayedTask(new sendItem($this, $event, $item), 1);
		}
	}

}

class sendItem extends Task{

	public function __construct(PluginBase $owner, $event, $item){
		$this->owner = $owner;
		$this->event = $event;
		$this->item = $item;
	}

	public function onRun() : void{
		$player = $this->event->getPlayer();
		$block = $this->event->getBlock();
		if(!$this->event->isCancelled()){
			if($player->getInventory()->canAddItem($this->item)){
				$player->getInventory()->addItem($this->item);
			}else{
				$world = $player->getWorld();
				/*$x = $block->getPosition()->x;
				$y = $block->getPosition()->y;
				$z = $block->getPosition()->z;
				$pos = new Vector3($x, $y, $z);*/
				$pos = $block->getPosition();
				$world->dropItem($pos, $this->item);
			}
		}
	}
}