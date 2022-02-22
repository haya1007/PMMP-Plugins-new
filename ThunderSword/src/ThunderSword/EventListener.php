<?php

namespace ThunderSword;

use pocketmine\player\Player;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\world\particle\BlockBreakParticle;

use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class EventListener implements Listener{

	public function __construct(private Main $plugin){}

	public function onDamage(EntityDamageByEntityEvent $event){
		$damager = $event->getDamager();
		if($damager instanceof Player){
			$item_tag = $damager->getInventory()->getItemInHand()->getNamedTag();
			$entity = $event->getEntity();
			if($item_tag->getTag("thunder") !== null){
				$this->Lightning_falls($entity);
				$event->setBaseDamage($item_tag->getTag("atk")->getValue());
			}
		}
	}

	public function onTap(PlayerInteractEvent $event){
		if($this->plugin->config->get("BlockTap")){
			if($event->getAction() === 0 || $event->getAction() === 1){
				if($event->getPlayer()->getInventory()->getItemInHand()->getNamedTag()->getTag("thunder") !== null){
					$this->Lightning_falls($event->getBlock());
				}
			}
		}
	}

	public function Lightning_falls($entity) : void{
		$pos = $entity->getPosition();
		$pk = AddActorPacket::create(Entity::nextRuntimeId(),1,"minecraft:lightning_bolt",$pos->asVector3(),null, 0,0,0.0,[],[],[]);
		$pk2 = PlaySoundPacket::create("ambient.weather.thunder", $pos->getX(), $pos->getY(), $pos->getZ(), 1, 1);
		$server = $this->plugin->getServer();
		$server->broadcastPackets($server->getOnlinePlayers(), [$pk, $pk2]);
	}
}