<?php

namespace TutorialPlugin;

use pocketmine\event\Listener;

use pocketmine\Player;
use pocketmine\level\Position;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\block\Block;
use pocketmine\item\Item;

use TutorialPlugin\npc\NPC;

class EventListener implements Listener{
	public function __construct(Main $main){
		$this->main = $main;
	}

	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		if($this->main->checkWorld($event->getPlayer()->getLevel())){

			if($event->getAction() !== PlayerInteractEvent::LEFT_CLICK_BLOCK && $event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;

			if($event->getBlock()->getId() === 123){
				$this->main->sendChest($player);
			}elseif($event->getBlock()->getId() === 19 && $event->getBlock()->getDamage() === 0){

				$position = new Position(223.5, 5, 256.5, $this->main->level);
				$player->teleport($position);

				$player->sendMessage(">§a===============§r<");
				$player->sendMessage("§bここは射撃訓練場(的な)ところだよ!!");
				$player->sendMessage("§bサーバー内に存在するアイテムや武器の性能を見れるよ!!");
				$player->sendMessage(">§a===============§r<");

			}elseif($event->getBlock()->getId() === 19 && $event->getBlock()->getDamage() === 1){
				$this->main->fb->setSurvival($event->getPlayer());

				$position = new Position(128, 5, 128, $this->main->fb->level['world']);
				$player->teleport($position);

				$player->sendMessage(">§a===============§r<");
				$player->sendMessage("§eここはロビーです!!§bサーバーライフ§eを楽しんでね!!");
				$player->sendMessage("§cルール違反したら怖いからね...");
				$player->sendMessage(">§a===============§r<");

				$this->main->fb->config[$player->getName()]->set("tutorial", true);
				$this->main->fb->addMoney($player->getName(), 1000);
				$player->getInventory()->clearAll();
				$player->getArmorInventory()->setHelmet(Item::get(Item::AIR));
				$player->getArmorInventory()->setChestplate(Item::get(Item::AIR));
				$player->getArmorInventory()->setLeggings(Item::get(Item::AIR));
				$player->getArmorInventory()->setBoots(Item::get(Item::AIR));

				foreach($this->main->getServer()->getOnlinePlayers() as $player) $player->sendMessage("§l§b".$event->getPlayer()->getName()."さんがチュートリアルを終了しました!!");
			}
		}
	}

	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if(!$this->main->fb->config[$name]->get("tutorial")){
			$player->sendMessage("§l§dようこそ!!FierceBattleServerへ!!\n§e最初にチュートリアルをしてもらうよ!!\n§e看板を読みながら先に進んでね!!");
			foreach($this->main->getServer()->getOnlinePlayers() as $player) $player->sendMessage("§l§b".$name."さんが初めてサーバーにやってきました!!!!");
		}
		self::showNPC($player);
	}

	public function onReceive(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		if($pk instanceof InventoryTransactionPacket){
			$player = $event->getPlayer();
			$item = $player->getInventory()->getItemInHand();
			$eid = $pk->trData->entityRuntimeId ?? null;
			if($eid === null){
				return false;
			}
			if($eid === $this->main->npc_one || $eid === $this->main->npc_two || $eid === $this->main->npc_three || $eid === $this->main->npc_four || $eid === $this->main->npc_five || $eid === $this->main->npc_six || $eid === $this->main->npc_seven){
				$npc = new NPC($this->main);
				$damageTable = $this->main->option->getDamageTable();
				$damage = $damageTable[$item->getName()] ?? 1;
				$npc->npcDamage($player, $eid, $damage);
			}
		}
	}

	public function showNPC(Player $player){
		$npc = new NPC($this->main);
		$npc->showNPC($player, "NPC1", $this->main->npc_one, 214.5, 4.5, 252.5, 270, 340);
		$npc->showNPC($player, "NPC2", $this->main->npc_two, 214.5, 4.5, 260.5, 180, null);
		$npc->showNPC($player, "NPC3", $this->main->npc_three, 208.5, 4.5, 266.5, 180, null);
		$npc->showNPC($player, "NPC4", $this->main->npc_four, 208.5, 4.5, 246.5, 270, 340);
		$npc->showNPC($player, "NPC5", $this->main->npc_five, 202.5, 4.5, 262.5, 200, null);
		$npc->showNPC($player, "NPC6", $this->main->npc_six, 202.5, 4.5, 250.5, 260, null);
		$npc->showNPC($player, "NPC7", $this->main->npc_seven, 196.5, 4.5, 256.5, 220, null);
	}
}