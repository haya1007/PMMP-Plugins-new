<?php

namespace nametag;

use pocketmine\event\Listener;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class EventListener implements Listener{
	public function __construct(Main $main){
		$this->main = $main;
	}

	public function onChange(SignChangeEvent $event){
		$player = $event->getPlayer();
		$lines = $event->getLines(); //array
		$block = $event->getBlock();
		if($lines[0] === "tagshop"){
			if($player->isOp()){ //AdminCheck
				$tag = $lines[1]; //string (タグ)	
				$price = $lines[2]; //int (値段)
				if(is_numeric($price)){
					$var = $this->main->getVar($block);
					$x = $block->getX();
					$y = $block->getY();
					$z = $block->getZ();

					/* SHOP看板の情報保存 */
					$this->main->shop->set($var, [
						"x" => $x,
						"y" => $y,
						"z" => $z,
						"level_name" => $block->getLevel()->getFolderName(),
						"price" => $price,
						"tag" => $tag
					]);

					$player->sendMessage("§aNameTagShopを作りました!!");
					$this->main->setSign($event, $price, $tag);
				}
				$this->main->shop->save(); //Config保存
			}else{
				$player->sendMessage("§c権限がありません");
				$event->setCancelled();
				return;
			}
		}elseif($lines[0] === "canceltag"){
			$var = $this->main->getVar($block);
			if($player->isOp()){ //AdminCheck
				/* Cancel看板の情報保存 */
				$this->main->cancel->set($var, [
					"x" => $block->getX(),
					"y" => $block->getY(),
					"z" => $block->getZ(),
					"level_name" => $block->getLevel()->getFolderName(),
				]);
				$player->sendMessage("§aCancelSignを作りました!!");
				$event->setLine(0, "");
				$event->setLine(1, "§cこの看板をタップすると");
				$event->setLine(2, "§cタグを消去できます");
				$this->main->cancel->save(); //Config保存
			}else{
				$player->sendMessage("§c権限がありません");
				$event->setCancelled();
				return;
			}
		}
	}

	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$var = $this->main->getVar($event->getBlock());
		if($this->main->shop->exists($var)){
			if($player->isOp()){
				$this->main->shop->remove($var);
				$this->main->shop->save();
				$player->sendMessage("§aShopを解体しました");
			}else{
				$player->sendMessage("§c権限がりません");
			}
		}elseif($this->main->cancel->exists($var)){
			if($player->isOp()){
				$this->main->cancel->remove($var);
				$this->main->cancel->save();
				$player->sendMessage("§aCancelSignを解体しました");
			}else{
				$player->sendMessage("§c権限がりません");
			}
		}
	}

	public function onTap(PlayerInteractEvent $event){
		$action = $event->getAction();
		if($action === PlayerInteractEvent::LEFT_CLICK_BLOCK || $action === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$player = $event->getPlayer();
			$name = $player->getName();
			$block = $event->getBlock();
			$var = $this->main->getVar($block);
			if($this->main->shop->exists($var)){
				$shop = $this->main->shop->getAll();
				$price = $shop[$var]["price"];
				$tag = $shop[$var]["tag"];
				$player_money = $this->main->fb->getMoney($name);
				if($player_money >= $price){
					$this->var[$name] = $var;
					$result_money = $player_money - $price;
					$data = [
							'type'    => 'modal',
							'title'   => '確認',
							'content' => "本当に購入しますか??\nあなたの所持金: ".$player_money."M\n価格: ".$price."M\n残高: ".$result_money."M\n§c※前回のタグは消えます",
							'button1' => "購入する",
							'button2' => "やめる"
					];
					$this->main->createWindow($player, $data, 5000);
				}else{
					$player->sendMessage("§cお金が足りません");
				}
			}elseif($this->main->cancel->exists($var)){
				$data = [
					'type'    => 'modal',
					'title'   => '確認',
					'content' => "本当にタグを消去しますか?",
					'button1' => "消去する",
					'button2' => "やめる"
				];
				$this->main->createWindow($player, $data, 5001);
			}
		}
	}

	public function onReceive(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		$player = $event->getPlayer();
		$name = $player->getName();
		if($pk instanceof ModalFormResponsePacket){
			$id = $pk->formId;
			$data = $pk->formData;
			$result = json_decode($data);
			if($data == "null\n"){
				return;
			}

			if($id === 5000 && $data === "true\n"){
				if(isset($this->var[$name])){
					$var = $this->var[$name];
					$shop = $this->main->shop->getAll();
					$price = $shop[$var]["price"];
					$tag = $shop[$var]["tag"];
					$this->main->fb->removeMoney($name, $price);
					$this->main->fb->config[$player->getName()]->set("tag", $tag);
					$this->main->fb->setNameTag($player);
					$player->sendMessage("§a".$price."Mを支払ってタグを購入しました");
				}else{
					$player->sendMessage("§cデータに不具合が発生しました");
				}
			}elseif($id === 5001 && $data === "true\n"){
				if($this->main->fb->config[$name]->get("tag") !== "null"){
					$this->main->fb->config[$name]->set("tag", "null");
					$this->main->fb->setNameTag($player);
					$player->sendMessage("§aタグを消去しました");
				}else{
					$player->sendMessage("§c既にタグはついていません");
				}
			}
		}
	}
}