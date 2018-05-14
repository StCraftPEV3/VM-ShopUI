  <?php

namespace GuiShop;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\{Item, ItemBlock};
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TF;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\event\server\DataPacketReceiveEvent;
use GuiShop\Modals\elements\{Dropdown, Input, Button, Label, Slider, StepSlider, Toggle};
use GuiShop\Modals\network\{GuiDataPickItemPacket, ModalFormRequestPacket, ModalFormResponsePacket, ServerSettingsRequestPacket, ServerSettingsResponsePacket};
use GuiShop\Modals\windows\{CustomForm, ModalWindow, SimpleForm};
use pocketmine\command\{Command, CommandSender, ConsoleCommandSender, CommandExecutor};

use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener {
  public $shop;
  public $item;

  //documentation for setting up the items
  /*
  "Item name" => [item_id, item_damage, buy_price, sell_price]
  */
public $Blocks = [
    "ICON" => ["Blocks",2,0],
    "Oak Wood" => [17,0,30,0],
    "Birch Wood" => [17,2,30,0],
    "Spruce Wood" => [17,1,30,0],
    "Dark Oak Wood" => [162,1,30,0],
	"Cobblestone" => [4,0,10,0],
	"Obsidian" => [49,0,500,0],
	"Bedrock" => [7,0,1500,0],
	"Sand " => [12,0,15,70],
    "Sandstone " => [24,0,15,0],
	"Nether Rack" => [87,0,15,0],
    "Glass" => [20,0,50,0],
    "Glowstone" => [89,0,100,0],
    "Sea Lantern" => [169,0,100,0],
	"Grass" => [2,0,20,0],
	"Dirt" => [3,0,10,0],
    "Stone" => [1,0,20,0],
    "Planks" => [5,0,20,0],
    "Prismarine" => [168,0,30,0],
    "End Stone" => [121,0,30,0],
    "Glass" => [20,0,50,0],
    "Purpur Blocks" => [201,0,50,0]
  ];

  public $Tools = [
    "ICON" => ["Tools",278,0],
    "Diamond Pickaxe" => [278,0,500,0],
    "Diamond Shovel" => [277,0,500,0],
    "Diamond Axe" => [279,0,500,0],
    "Diamond Hoe" => [293,0,500,0],
    "Diamond Sword" => [276,0,750,0],
    "Bow" => [261,0,400,0],
    "Arrow" => [262,0,300,0]
  ];

  public $Armor = [
    "ICON" => ["Armor",311,0],
    "Diamond Helmet" => [310,0,1000,0],
    "Diamond Chestplate" => [311,0,2500,0],
    "Diamond Leggings" => [312,0,1500,0],
    "Diamond Boots" => [313,0,1000,0]
  ];

  public $Farming = [
    "ICON" => ["Farming",293,0],
    "Pumpkin" => [86,0,40,0],
    "Melon" => [360,13,10,0],
    "Carrot" => [391,0,90,0],
    "Potato" => [392,0,100,0],
    "Sugarcane" => [338,0,90,0],
    "Wheat" => [296,6,80,0],
    "Pumpkin Seed" => [361,0,50,0],
    "Melon Seed" => [362,0,40,0],
    "Seed" => [295,0,80,0]
  ];

  public $Miscellaneous = [
    "ICON" => ["Miscellaneous",368,0],
	"Furnace" => [61,0,20,0],
    "Crafting Table" => [58,0,20,0],
	"Ender Chest " => [130,0,1000,0],
    "Enderpearl" => [368,0,1000,0],
    "Bone" => [352,0,50,0],
    "Book & Quill" => [386,0,100,0],
    "Elytra" => [444,0,1000,0],
    "Boats" => [333,0,1000,0],
    "Totem of Undying" => [450,0,1000,0]
  ];

  public $Raiding = [
    "ICON" => ["Raiding",46,0],
    "Flint & Steel" => [259,0,100,0],
    "Torch" => [50,0,5,0],
	"Packed Ice " => [174,0,500,0],
    "Water" => [9,0,50,0],
    "Lava" => [10,0,50,0],
    "Redstone" => [331,0,50,0],
    "Chest" => [54,0,100,0],
    "TNT" => [46,0,10000,0]
  ];

  public $Potions = [
    "ICON" => ["Potions",373,0],
    "Strength" => [373,33,1000,0],
    "Regeneration" => [373,28,1000,0],
    "Speed" => [373,16,1000,0],
    "Fire Resistance" => [373,13,1000,0],
    "Poison (SPLASH)" => [438,27,1000,0],
    "Weakness (SPLASH)" => [438,35,1000,0],
    "Slowness (SPLASH)" => [438,17,1000,0]
  ];

  public $MobDrop = [
    "ICON" => ["MobDrop",369,0],
    "Cooked Pork Chop" => [320,0,999999999,800],
    "Feather" => [288,0,999999999,100],
    "Cooked Chicken" => [366,0,999999999,900],
    "Iron ingot" => [265,0,999999999,1200],
    "Iron Blocks" => [42,0,999999999,3400],
    "Poppy" => [38,0,999999999,5000],
    "Bone" => [352,0,999999999,1200],
    "Arrow" => [262,0,999999999,3000],
    "Steak" => [364,0,999999999,2900],
    "Leather" => [334,0,999999999,1700],
    "BlazeRods" => [369,0,999999999,8000],
    "BlazePowder" => [377,0,999999999,6500],
    "Bow" => [261,0,300,300]
  ];

  public $Spawners = [
    "ICON" => ["Spawners",52,0],
    "Spawner" => [52,0,3000,0],
    "Spawner Egg Pig" => [383,12,30000,0],
    "Spawner Egg Chicken" => [383,10,60000,0],
    "Spawner Egg Skeleton" => [383,34,90000,0],
    "Spawner Egg Cow" => [383,11,120000,0],
    "Spawner Egg Blaze" => [383,43,150000,0],
    "Spawner Egg Iron Golem" => [383,20,180000,0]
  ];

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    PacketPool::registerPacket(new GuiDataPickItemPacket());
		PacketPool::registerPacket(new ModalFormRequestPacket());
		PacketPool::registerPacket(new ModalFormResponsePacket());
		PacketPool::registerPacket(new ServerSettingsRequestPacket());
		PacketPool::registerPacket(new ServerSettingsResponsePacket());
    $this->item = [$this->Spawners, $this->Raiding, $this->Farming, $this->Armor, $this->Tools, $this->Blocks, $this->Miscellaneous, $this->Potions, $this->MobDrop];
  }

  public function sendMainShop(Player $player){
    $ui = new SimpleForm("§c§lStCraft§6PE §bShop-GUI","       §7Purchase and Sell items Here!");
    foreach($this->item as $category){
      if(isset($category["ICON"])){
        $rawitemdata = $category["ICON"];
        $button = new Button($rawitemdata[0]);
        $button->addImage('url', "http://avengetech.me/items/".$rawitemdata[1]."-".$rawitemdata[2].".png");
        $ui->addButton($button);
      }
    }
    $pk = new ModalFormRequestPacket();
    $pk->formId = 110;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sendShop(Player $player, $id){
    $ui = new SimpleForm("§c§lStCraft§6PE §bShop-GUI","       §7Purchase and Sell items Here!");
    $ids = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $id){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            $button = new Button($name);
            $button->addImage('url', "http://avengetech.me/items/".$item[0]."-".$item[1].".png");
            $ui->addButton($button);
          }
        }
      }
    }
    $pk = new ModalFormRequestPacket();
    $pk->formId = 111;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sendConfirm(Player $player, $id){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $id){
              $this->item[$player->getName()] = $id;
              $iname = $name;
              $cost = $item[2];
              $sell = $item[3];
              break;
            }
          }
          $idi++;
        }
      }
    }

    $ui = new CustomForm($iname);
    $slider = new Slider("Amount ",1,64,1);
    $toggle = new Toggle("Selling");
    if($sell == 0) $sell = "0";
    $label = new Label(TF::GREEN."Buy: $".TF::GREEN.$cost.TF::RED."\nSell: $".TF::RED.$sell);
    $ui->addElement($label);
    $ui->addElement($toggle);
    $ui->addElement($slider);
    $pk = new ModalFormRequestPacket();
    $pk->formId = 112;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sell(Player $player, $data, $amount){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $this->item[$player->getName()]){
              $iname = $name;
              $id = $item[0];
              $damage = $item[1];
              $cost = $item[2]*$amount;
              $sell = $item[3]*$amount;
              if($sell == 0){
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "This is not sellable!");
                return true;
              }
              if($player->getInventory()->contains(Item::get($id,$damage,$amount))){
                $player->getInventory()->removeItem(Item::get($id,$damage,$amount));
                EconomyAPI::getInstance()->addMoney($player, $sell);
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::GREEN . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "You have sold $amount $iname for $$sell");
              }else{
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "You do not have $amount $iname!");
              }
              unset($this->item[$player->getName()]);
              unset($this->shop[$player->getName()]);
              return true;
            }
          }
          $idi++;
        }
      }
    }
    return true;
  }

  public function purchase(Player $player, $data, $amount){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $this->item[$player->getName()]){
              $iname = $name;
              $id = $item[0];
              $damage = $item[1];
              $cost = $item[2]*$amount;
              $sell = $item[3]*$amount;
              if(EconomyAPI::getInstance()->myMoney($player) > $cost){
                $player->getInventory()->addItem(Item::get($id,$damage,$amount));
                EconomyAPI::getInstance()->reduceMoney($player, $cost);
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::GREEN . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "You purchased $amount $iname for $$cost");
              }else{
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "You do not have enough money to buy $amount $iname");
              }
              unset($this->item[$player->getName()]);
              unset($this->shop[$player->getName()]);
              return true;
            }
          }
          $idi++;
        }
      }
    }
    return true;
  }

  public function DataPacketReceiveEvent(DataPacketReceiveEvent $event){
    $packet = $event->getPacket();
    $player = $event->getPlayer();
    if($packet instanceof ModalFormResponsePacket){
      $id = $packet->formId;
      $data = $packet->formData;
      $data = json_decode($data);
      if($data === Null) return true;
      if($id === 110){
        $this->shop[$player->getName()] = $data;
        $this->sendShop($player, $data);
        return true;
      }
      if($id === 111){
        //$this->shop[$player->getName()] = $data;
        $this->sendConfirm($player, $data);
        return true;
      }
      if($id === 112){
        $selling = $data[1];
        $amount = $data[2];
        if($selling){
          $this->sell($player, $data, $amount);
          return true;
        }
        $this->purchase($player, $data, $amount);
        return true;
      }
    }
    return true;
  }

  public function onCommand(CommandSender $player, Command $command, string $label, array $args) : bool{
    switch(strtolower($command)){
      case "shop":
        $this->sendMainShop($player);
        return true;
    }
  }

}
