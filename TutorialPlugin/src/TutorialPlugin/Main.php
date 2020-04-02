<?php

namespace TutorialPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\entity\Entity;

use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket as RemoveEntityPacket;
use pocketmine\scheduler\Task;

use pocketmine\utils\Config;
use pocketmine\utils\UUID;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;

use TutorialPlugin\chest\ChestCustom;
use TutorialPlugin\npc\NPC;

class Main extends PluginBase{

	public function onEnable(){
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		$this->getServer()->loadLevel('tutorial');
		$this->chest = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$this->level = $this->getServer()->getLevelByName("tutorial");
		if($this->getServer()->getPluginManager()->getPlugin("FierceBattle") != null){
      	  	$this->fb = $this->getServer()->getPluginManager()->getPlugin("FierceBattle");
      	  	$this->getLogger()->info("FierceBattleを検出しました");
      			}else{
      	  	$this->getLogger()->warning("FierceBattleが見つかりませんでした");
      	 	$this->getServer()->getPluginManager()->disablePlugin($this);
    		}
		if($this->getServer()->getPluginManager()->getPlugin("option") != null){
      	  		$this->option = $this->getServer()->getPluginManager()->getPlugin("option");
      	  		$this->getLogger()->info("optionを検出しました");
      		}else{
      	  		$this->getLogger()->warning("optionが見つかりませんでした");
      	  		$this->getServer()->getPluginManager()->disablePlugin($this);
    		}

    		$this->saveDefaultConfig();
		
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$this->skin = new Config($this->getDataFolder() . "skinData.yml", Config::YAML);
		$this->getScheduler()->scheduleDelayedTask(new custom($this), 20);

    		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		$npc = new NPC($this);
		$this->npc_one = $npc->randomEid();
		$this->npc_two = $npc->randomEid();
		$this->npc_three = $npc->randomEid();
		$this->npc_four = $npc->randomEid();
		$this->npc_five = $npc->randomEid();
		$this->npc_six = $npc->randomEid();
		$this->npc_seven = $npc->randomEid();
	}

	public function getChest(){
		return $this->chest;
	}

	public function sendChest(Player $player){
		$this->chest->send($player);
	}

	public function checkWorld(Level $level){
		return ($level === $this->level) ? true : false;
	}

	public function FloatDamage($player, $eid, $damage, $color){
		if(isset($this->fb->npc[$eid])){
			$x = $this->fb->npc[$eid]["x"];
			$y = $this->fb->npc[$eid]["y"];
			$z = $this->fb->npc[$eid]["z"];
			$move = 0;
			$plusY = 0;
			if(isset($color)){
				$name = "§l".$color."".$damage."";
			}else{
				$name = "§l".$damage."";
			}
			#$name = "§l".$color."".$damage."";
			$eid = mt_rand(10000000,100000000000);
			$pk = new AddPlayerPacket();
			$pk->entityRuntimeId = $eid;
			$pk->username = $name;
			$pk->uuid = UUID::fromRandom();
			$pkx = $x - 1 + mt_rand(1,20) / 10;
			$pky = ($y - 1 + mt_rand(1, 5) / 10) + 1.62 + $plusY;
			$pkz = $z - 1 + mt_rand(1,20) / 10;
			$pk->position = new Vector3($pkx, $pky, $pkz);
			$pk->motion = new Vector3(-0.2 + mt_rand(1,3) / 10, 0.2, -0.2 + mt_rand(1,3) / 10);
			$pk->yaw = 0;
			$pk->pitch = 0;
			$pk->item = Item::get(0);
			@$flags |= 0 << Entity::DATA_FLAG_INVISIBLE;
			@$flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
			@$flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
			@$flags |= 0 << Entity::DATA_FLAG_IMMOBILE;
			$pk->metadata = [
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $name],
				Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1],
 				Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],//大きさ
				  	];
			$player->dataPacket($pk);
			$this->getScheduler()->scheduleDelayedTask(new FloatDelete($this, $player, $eid), 10);
		}
	}
}

class custom extends Task{
    /**
     * @var PluginBase
     */
    private $owner;

    public function __construct(PluginBase $owner){
		$this->owner = $owner;
	}

	public function onRun($tick){
		$custom = new ChestCustom($this->owner);
		$custom->ChestCustom($this->owner->config->get('chestName'), $this->owner->config->get('chestReadOnly'));
	}
}

class FloatDelete extends Task{

    /**
     * @var PluginBase
     */
    private $owner;
    /**
     * @var Player
     */
    private $player;
    /**
     * @var int
     */
    private $eid;

    function __construct(PluginBase $owner, Player $player , int $eid){
		$this->owner = $owner;
		$this->player = $player;
		$this->eid = $eid;
	}

	function onRun($tick){
		$pk = new RemoveEntityPacket();
		$pk->entityUniqueId = $this->eid;
		foreach($this->owner->getServer()->getOnlinePlayers() as $players){
			$players->dataPacket($pk);
		}
	}
}
