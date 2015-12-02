<?php
/*
                            _     _     
            /\             | |   | |    
 __  __    /  \     _   _  | |_  | |__  
 \ \/ /   / /\ \   | | | | | __| | '_ \ 
  >  <   / ____ \  | |_| | | |_  | | | |
 /_/\_\ /_/    \_\  \__,_|  \__| |_| |_|
                                        
                                        */

namespace xFlare\xAuth;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
/*
- Stops events from running when not logged in.
- Protects password from chat.
*/
class LoginTasks implements Listener{
	public function __construct(Loader $plugin){
        $this->plugin = $plugin;
        $this->message = $this->plugin->prefix . " " . "Please authenticate firstr to play!";
        $this->disable = $this->plugin->prefix . " " . $this->plugin->getConfig()->get("disable");
    }
    public function onChat(PlayerChatEvent $event){
    	$message = $event->getMessage();
        if($this->plugin->loginmanager[$event->getPlayer()->getId()] !== true){
            $event->setCancelled(true); //Don't allow chat when not authenticated.
        }
    	if($this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getPlayer()->getId()] === true && $this->plugin->chatprotection[$event->getPlayer()->getId()] === md5($message) && $this->plugin->passBlock === true){
    		$event->setCancelled(true); //Sharing is caring, but don't share passwords!
            	$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->plugin->getConfig()->get("sharing"));
    	}
    	elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
    		$event->setCancelled(true);
    		$event->getPlayer()->sendMessage($this->disable);
    
    	}
    }
    public function onPickUp(InventoryPickupItemEvent $event){
    	if($event->getPlayer() instanceof Player && $this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getPlayer()->getId()] !== true && $this->plugin->allowPickup !== true){
            $event->setCancelled(true);
        }
        elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
    		$event->setCancelled(true);
    		$event->getPlayer()->sendMessage($this->disable);
    	}
    }
    public function onDrop(PlayerDropItemEvent $event){
        if($event->getPlayer() instanceof Player && $this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getPlayer()->getId()] !== true && $this->plugin->allowDrops !== true){
            $event->setCancelled(true);
        }
        elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
    		$event->setCancelled(true);
    		$event->getPlayer()->sendMessage($this->disable);
    	}
    }
    public function onCommand(PlayerCommandPreprocessEvent $event){
        if($this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getPlayer()->getId()] !== true && $this->plugin->allowCommand !== true && substr($event->getMessage(), 0, 1) === '/'){ 
            $event->setCancelled(true);
        }
        elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
    		$event->setCancelled(true);
    		$event->getPlayer()->sendMessage($this->disable);
    	}
    }
    public function onInteract(PlayerInteractEvent $event){
        if($this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getPlayer()->getId()] !== true){
            $event->setCancelled(true);
        }
        elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
    		$event->setCancelled(true);
    	}
    }
    public function onMove(PlayerMoveEvent $event){
        if($this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getPlayer()->getId()] !== true && $this->plugin->allowMoving !== true){
            $event->setCancelled(true);
        }
        elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
    		$event->setCancelled(true);
    	}
    }
    public function onBreak(BlockBreakEvent $event){
        if($this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getPlayer()->getId()] !== true && $this->plugin->allowBreak !== true){
            $event->setCancelled(true);
        }
        elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
    		$event->setCancelled(true);
    		$event->getPlayer()->sendMessage($this->disable);
    	}
    }
    public function onPlace(BlockPlaceEvent $event){
        if($this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getPlayer()->getId()] !== true && $this->plugin->allowPlace !== true){
            $event->setCancelled(true);
        }
        elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
    		$event->setCancelled(true);
    		$event->getPlayer()->sendMessage($this->disable);
    	}
    }
    public function onPvP(EntityDamageEvent $event){
        if($event->getEntity() instanceof Player && $this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getEntity()->getId()] !== true){
        	if($event->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent){
        		if($this->allowPvP !== true){
        			$event->setCancelled(true);
        		}
        	}
        	if($this->plugin->allowDamage !== true){
            		$event->setCancelled(true);
        	}
        }
        elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
            $event->setCancelled(true);
        }
    } 
    public function onBowShoot(EntityShootBowEvent $event){
        if($this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getEntity()->getId()] !== true && $this->plugin->allowShoot !== true && $event->getPlayer() instanceof Player){
            $event->setCancelled(true);
        }
        elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
    		$event->setCancelled(true);
    		$event->getPlayer()->sendMessage($this->disable);
    	}
    }
    public function onFoodEat(PlayerItemConsumeEvent $event){
        if($this->plugin->status === "enabled" && $this->plugin->loginmanager[$event->getPlayer()->getId()] !== true){
            $event->setCancelled(true);
        }
        elseif($this->plugin->safemode === true and $this->plugin->status !== "enabled"){
    		$event->setCancelled(true);
    		$event->getPlayer()->sendMessage($this->disable);
    	}
    }
    public function onJoin(PlayerJoinEvent $event){
        if($this->plugin->status === "enabled" && $this->plugin->join !== true){
            $event->setJoinMessage("");
        }
    }
    public function onQuit(PlayerQuitEvent $event){
        if($this->plugin->status === "enabled" && $this->plugin->quit !== true){
            $event->setQuitMessage("");
        }
    }
}

