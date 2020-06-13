<?php

namespace haya1007;

use pocketmine\event\Listener;

use pocketmine\Player;
use pocketmine\entity\Entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use haya1007\task\unStop;

class EventListener implements Listener{
	public function __construct(Main $main){
		$this->main = $main;
	}

	public function onDamage(EntityDamageEvent $event){
		if ($event instanceof EntityDamageByEntityEvent){
			$player = $event->getDamager();
			$entity = $event->getEntity();

			if($player instanceof Player and $entity instanceof Player){
				$name = $entity->getName();
				$hand = $player->getInventory()->getItemInHand();
				if(!isset($this->main->stop[$name]) and $this->main->check($this->main->getItem(), $hand)){

					$rand = mt_rand(1, $this->main->stopItem->getProbability());

					if($rand === 1){

						$entity->setDataFlag(Entity::DATA_FLAGS,Entity::DATA_FLAG_IMMOBILE,true);
						$this->main->stop[$name] = true;
						$this->main->getScheduler()->scheduleDelayedTask(new unStop($this->main, $name), 20*3);

						if($hand->getNamedTagEntry("break") != null){
							$this->main->update_item($player, $hand);
						}

						$player->sendTip("§l§b動きを止めた！§r");
						$entity->sendTip("§l§b動きを止められた!§r");
					}else{

						$player->sendTip("§l§c失敗!§r");
					}
				}
			}
		}
	}
}