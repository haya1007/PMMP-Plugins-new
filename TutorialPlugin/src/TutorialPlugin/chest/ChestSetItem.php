<?php

namespace TutorialPlugin\chest;

use muqsit\invmenu\InvMenu;
use TutorialPlugin\Main;

class ChestSetItem{
	public function __construct(Main $main, $inv){
		$this->main = $main;
		$this->inv = $inv;
	}

	public function setItem(){
		$inv  = $this->inv;
		$option = $this->main->option;

		$inv->setItem(0, $option->book); //コマンド確認ブック
		$inv->setItem(1, $option->water); //水かき
		$inv->setItem(2, $option->fire); //火の素
		$inv->setItem(3, $option->kobito); //小人HAT
		$inv->setItem(4, $option->kyozin); //巨人の胸
		$inv->setItem(5, $option->speed); //飛べない翼
		$inv->setItem(6, $option->nawa); //縄

		$inv->setItem(27, $option->gatya['powerbow']);
		$inv->setItem(28, $option->gatya['sanso']);
		$inv->setItem(29, $option->gatya['maxsword']);
		$inv->setItem(30, $option->gatya['daiya']);
		$inv->setItem(31, $option->gatya['armor']);
		$inv->setItem(32, $option->gatya['toge']);
		$inv->setItem(33, $option->gatya['jump']);
		$inv->setItem(34, $option->gatya['flint']);
		$inv->setItem(35, $option->gatya['sugar']);
		$inv->setItem(36, $option->gatya['jagar']);
		$inv->setItem(37, $option->gatya['bigsword']);
		$inv->setItem(38, $option->gatya['sword']);
		$inv->setItem(39, $option->gatya['light']);
	}
}