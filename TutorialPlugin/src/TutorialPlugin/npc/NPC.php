<?php

namespace TutorialPlugin\npc;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ActorEventPacket as EntityEventPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\utils\UUID;

use TutorialPlugin\Main;

class NPC{

	public function __construct(Main $main){
		$this->main = $main;
	}

	public function showNPC(Player $player, string $name, $eid, float $x, float $y, float $z, $yaw, $headYaw){
		$npcname = $name;
		$itemid = 276;
		$pk = new AddPlayerPacket();
		$pk->entityRuntimeId = $eid;
		$pk->uuid = UUID::fromRandom();
		$pk->username = $npcname;
		#$pk->position = new Position($x, $y, $z, $this->main->level);
		$pk->position = new Vector3($x, $y, $z);
	   	$pk->yaw = $yaw;
	   	$pk->headYaw = $headYaw;
	 	$pk->pitch = 0;
	       	$pk->item = Item::get($itemid,0,1);
		@$flags |= 0 << Entity::DATA_FLAG_INVISIBLE;
		@$flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
		@$flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
		@$flags |= 0 << Entity::DATA_FLAG_IMMOBILE;
		      	$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $npcname],
			Entity::DATA_FLAG_NO_AI => [Entity::DATA_TYPE_BYTE, 1],
		  	Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1],
			Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 1],//大きさ
			];
		$geometryJsonEncoded = base64_decode($this->main->skin->get("geometrydata"));
		if($geometryJsonEncoded !== ""){
			$geometryJsonEncoded = \json_encode(\json_decode($geometryJsonEncoded));
		}
		$skin = new Skin(base64_decode($this->main->skin->get("skinid")), base64_decode($this->main->skin->get("skindata")), base64_decode($this->main->skin->get("capedata")), base64_decode($this->main->skin->get("geometryname")), $geometryJsonEncoded);
		 		$xbox = mt_rand(100000, 1000000000);
		$this->main->getServer()->updatePlayerListData($pk->uuid, $pk->entityRuntimeId, $npcname, $skin, $xbox, $this->main->getServer()->getOnlinePlayers());
		$player->dataPacket($pk);
		$pk2 = new MobEquipmentPacket();
		$pk2->entityRuntimeId = $eid;
		$pk2->item = Item::get(intval($itemid),0,1);
		$pk2->inventorySlot = 0;
		$pk2->hotbarSlot = 0;
		$player->dataPacket($pk2);//Item

		//データ共有
		$this->main->fb->npc[$eid]["x"] = $x;
		$this->main->fb->npc[$eid]["y"] = $y;
		$this->main->fb->npc[$eid]["z"] = $z;


	}

	public function npcDamage(Player $player, $eid, $damage){
		$pk = new EntityEventPacket();
		$pk->entityRuntimeId = $eid;
		$pk->event = 2;
		foreach($this->main->getServer()->getOnlinePlayers() as $players){
			$players->dataPacket($pk);
		}

		$this->main->FloatDamage($player, $eid, $damage, "§c");
	}

	public function randomEid(){
		return mt_rand(1000000, 1000000000);
	}
}