<?php

namespace haya1007;

class StopItem{
	public function __construct(Main $main){
		$this->config = $main->config;
	}

	public function getName(){
		return $this->config->get("itemName");
	}

	public function getId(){
		return $this->config->get("itemId");
	}

	public function getMeta(){
		return $this->config->get("itemMeta");
	}

	public function getTime(){
		return $this->config->get("stopTime");
	}

	public function getMaxLimit(){
		return $this->config->get("limit");
	}

	public function getProbability(){
		return $this->config->get("probability");
	}
}