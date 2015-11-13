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
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
/*
- Logins/Registers players.
- Main core functions are done here.
*/
class LoginAndRegister implements Listener{
	public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }
    public function onJoin(PlayerJoinEvent $event){
    	//Messages
    	if($this->plugin->status === "enabled"){
    		$event->getPlayer()->sendMessage("[xAuth] This server is protected by xAuth.");
    		$event->getPlayer()->sendMessage($this->plugin->getConfig()->get("join"));
    	}
    	elseif($this->plugin->safemode){
    		$event->getPlayer()->sendMessage($this->plugin->getConfig()->get("disable"));
    	}
    	if($this->plugin->status === "enabled"){
    		if($this->plugin->provider === "yml"){
    			if($this->plugin->registered->exists(strtolower($event->getPlayer()->getName()))){
    				$event->getPlayer()->sendMessage($this->plugin->getConfig()->get("already-registered"));
    				$event->getPlayer()->sendMessage($this->plugin->getConfig()->get("login"));
    				$this->plugin->loginmanager[$event->getPlayer()->getId()] = 1;
    			}
    			else{
    				$event->getPlayer()->sendMessage($this->plugin->getConfig()->get("please-register"));
    				$this->plugin->loginmanager[$event->getPlayer()->getId()] = 0;
    			}
    		}
    	}
    }
    public function onChat(PlayerChatEvent $event){
    	$message = $event->getMessage();
    	if($this->plugin->loginmanager[$event->getPlayer()->getId] === 1){
    		if($this->plugin->provider === "yml"){
    			$myuser = new Config($this->myuser . "users/" . strtolower($event->getPlayer()->getName() . ".yml"), Config::YAML);
    			if(md5($message) === $myuser->get("password"));
    				$this->plugin->loginmanager[$event->getPlayer()->getId()] = true;
    				$event->getPlayer()->sendMessage($this->plugin->getConfig()->get("logged"));
    			}
    			else{
    				$event->getPlayer()->sendMessage($this->plugin->getConfig()->get("incorrect"));
    			}
    		}
    	}
    	if($this->plugin->loginmanager[$event->getPlayer()->getId()] === 0 && !isset($this->plugin->chatprotection[$event->getPlayer()->getId()])){
    		if($this->plugin->provider === "yml"){
    			$this->plugin->chatprotection = $this->proccessPassword($message, $event->getPlayer());
    			if(isset($this->plugin->chatprotection[$event->getPlayer()->getId])){
    				$event->getPlayer()->sendMessage("Thanks! Please retype the password to make sure there where no mistakes.");
    			}
    		}
    	}
    	if($this->plugin->loginmanager[$event->getPlayer()->getId()] === 0 && isset($this->plugin->chatprotection[$event->getPlayer()->getId()])){
    		if(md5($message) === $this->plugin->chatprotection[$event->getPlayer()->getId()]){
    			$event->getPlayer()->sendMessage("You are now registered.");
    		}
    }
    private function proccessPassword($password, $player){
    	$simplepass = strtolower($password);
    	if($this->simplePassword === true && $this->plugin->status === "enabled"){
    		if($simplepass === 123456789 || $simplepass === 987654321 || $simplepass === "asdfg" || $simplepass === "password"){
    			$player->sendMessage("[xAuth] That password is too simple!");
    			$player->sendMessage("[xAuth] Make it harder by adding letters and numbers.");
    			unset($this->plugin->chatprotection[$event->getPlayer()->getId()]);
    			return;
    		}
    	}
    	if(strlen($password) > 5){
    		$player->sendMessage("[xAuth] Your password was too short!");
    		return;
    	}
    	if(strlen($password) < 15){
    		$player->sendMessage("[xAuth] Your password was too long!");
    		return;
    	}
    	$myuser = new Config($this->myuser . "users/" . strtolower($player->getName() . ".yml"), Config::YAML);
    	$myuser->set("password", md5($password));
    	$myuser->save();
    	return md5($password);
    }
}

