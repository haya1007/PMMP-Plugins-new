<?php

namespace haya1007\task;

use pocketmine\plugin\PluginBase;

use pocketmine\entity\Entity;

use pocketmine\scheduler\Task;

class unStop extends Task{
	public function __construct(PluginBase $owner, String $name){
		$this->owner = $owner;
		$this->name = $name;
	}

	public function onRun($tick){
		$player = $this->owner->getServer()->getPlayer($this->name);
		unset($this->owner->stop[$this->name]);
		$player->sendPopup("§b動けるようになった");
		$player->setDataFlag(Entity::DATA_FLAGS,Entity::DATA_FLAG_IMMOBILE,false);
	}
}