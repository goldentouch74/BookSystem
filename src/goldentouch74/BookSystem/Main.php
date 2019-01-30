<?php

declare(strict_types=1);

namespace goldentouch74\BookSystem;

use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\command\{
	Command, CommandSender
};

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;

class Main extends PluginBase implements Listener{

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
		$form = $formapi->createSimpleForm(function ($sender, $data){
			if($data !== null){
				$this->Confirm($sender, $data);
			}
		});

		$form->setTitle("CustomEnchants Shop");

		$form->addButton($ce->getRarityColor(10) . $this->getNameByData(0));
		$form->addButton($ce->getRarityColor(5) . $this->getNameByData(1));
		$form->addButton($ce->getRarityColor(2) . $this->getNameByData(2));
		$form->addButton($ce->getRarityColor(1) . $this->getNameByData(3));
		$form->sendToPlayer($sender);
		return true;
	}

	public function getNameByData(int $data, $id = true): string{
		if($id){
			switch($data){
				case 0:
				return "Common";
				case 1:
				return "Uncommon";
				case 2:
				return "Rare";
				case 3:
				return "Mythic";
			}
		}else{
			switch($data){
				case 0:
				return "10";
				case 1:
				return "5";
				case 2:
				return "2";
				case 3:
				return "1";
			}
		}
	}

	public function getCost(int $data): int{
		switch($data){
			case 0:
			return 400;
			case 1:
			return 800;
			case 2:
			return 2500;
			case 3:
			return 5000;
		}
	}

    public function Confirm($sender, int $dataid): void{
    	$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
    	$ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
    	$form = $formapi->createCustomForm(function ($sender, $data) use($dataid, $ce){
    		if($data !== null){
    			if($sender->getCurrentTotalXp() < $this->getCost($dataid)){
    				$sender->sendMessage(C::RED . "You don't have enough Exp!");
    				return;
    			}

    			$item = Item::get(340);
    			$nbt = $item->getNamedTag();
    			$nbt->setString("ceid", (string)$dataid);
    			$item->setCustomName($ce->getRarityColor((int)$this->getNameByData($dataid, false)) . $this->getNameByData($dataid) . C::RESET . C::YELLOW . " Book");
    			$item->setLore([C::GRAY . "Tap ground to get random enchantment"]);
    			$sender->getInventory()->addItem($item);
    			$sender->addXp(-$this->getCost($dataid));
    		}
    	});

        $form->setTitle($ce->getRarityColor((int)$this->getNameByData($dataid, false)) . $this->getNameByData($dataid));
        $form->addLabel("Cost: " . $this->getCost($dataid) . " Exp");
        $form->sendToPlayer($sender);
    }

    public function onInteract(PlayerInteractEvent $e): void{
    	$player = $e->getPlayer();
    	$item = $e->getItem();
    	$ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");

    	if($item->getId() == 340){
    		if($item->getNamedTag()->hasTag("ceid", StringTag::class)){
    			$e->setCancelled();

    			$id = $item->getNamedTag()->getString("ceid");

    			foreach($ce->enchants as $eid => $data){
    				if($data[3] == $this->getNameByData((int)$id)){
    					switch($id){
    						case 0: //Common
    						$enchs = [114, 101, 109, 601, 100, 405];
    						break;
    						case 1: //Uncommon
    						$enchs = [108, 122, 120, 309, 113, 801, 412, 408, 117, 121, 206, 202, 401, 209, 208, 603, 500, 103, 415, 402, 207, 210, 312, 504, 602, 304, 211, 800, 104, 403, 203, 406, 414, 201, 501, 502, 421, 111, 305, 115];
    						break;
    						case 2: //Rare
    						$enchs = [417, 420, 411, 311, 416, 102, 410, 409, 804, 200, 404, 313, 310, 422, 600, 503, 123, 204, 315, 400, 303, 307, 424, 802, 700, 413, 407, 423, 308, 803, 205, 805, 316];
    						break;
    						case 3: //Mythic
    						$enchs = [604, 306, 418, 119, 212, 419, 314, 118, 301];
    						break;
    					}
    					$enchanted = false;

    					if($enchanted == false){
    						$enchanted = true;
    						$info["ench"] = $enchs[array_rand($enchs)];
    						$ench = CustomEnchants::getEnchantment($info["ench"]);
    						$info["lvl"] = mt_rand(1, $ce->getEnchantMaxLevel($ench));
    						$book = Item::get(Item::ENCHANTED_BOOK);
    						$player->getInventory()->setItemInHand($ce->addEnchantment($player->getInventory()->getItemInHand(), $info["ench"], $info["lvl"], $player->hasPermission("piggycustomenchants.overridecheck") ? false : true, $player));
    						return;
    					}
    				}
    			}
    		}
    	}
    }
}
