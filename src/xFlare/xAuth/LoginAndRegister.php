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
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
/*
- Logins/Registers players.
- Main core functions are done here.
*/
class LoginAndRegister implements Listener{
	public function __construct(Loader $plugin){
        $this->plugin = $plugin;
        $this->messageLoggedIn = $this->plugin->getConfig()->get("logged");
        $this->messageJoin = $this->plugin->getConfig()->get("join");
        $this->messageIP = $this->plugin->getConfig()->get("ipauthed");
        $this->messageRegisterPlease = $this->plugin->getConfig()->get("please-register");
        $this->messageAlreadyRegistered = $this->plugin->getConfig()->get("already-registered");
        $this->messageIncorrect = $this->plugin->getConfig()->get("incorrect");
        $this->messageKick = $this->plugin->getConfig()->get("kick");
        $this->messageLogin = $this->plugin->getConfig()->get("login");
        $this->messageSimple = $this->plugin->getConfig()->get("simple");
        $this->messageShort = $this->plugin->getConfig()->get("short");
        $this->messageLong = $this->plugin->getConfig()->get("long");
        $this->messageError = $this->plugin->getConfig()->get("error");
        $this->messageDisabled = $this->plugin->getConfig()->get("disable");
        $this->messageRegistered = $this->plugin->getConfig()->get("registered");
        $this->messageWanted = $this->plugin->getConfig()->get("wanted");
        $this->messageNoSuccess = $this->plugin->getConfig()->get("no-success");
        $this->session = $this->plugin->getConfig()->get("close-session");
        $this->autocleanup = $this->plugin->getConfig()->get("auto-cleanups");
        $this->typeEmail = $this->plugin->getConfig()->get("type-email");
        $this->doneEmail = $this->plugin->getConfig()->get("email-done");
        $this->failEmail = $this->plugin->getConfig()->get("email-fail");
        
    }
    public function onQuit(PlayerQuitEvent $event){
    	$this->clearSession($event->getPlayer());
    }
    public function onKick(PlayerKickEvent $event){
    	if($event->getPlayer() === null){ //If a plugin stoped this event like VIPSlots.
    		$this->clearSession($event->getPlayer());
    	}
    }
    public function onJoin(PlayerJoinEvent $event){
    	if($this->plugin->status === "enabled"){
    		$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageJoin);
        	if($this->plugin->usernamestatus === true){
            		$name = $event->getPlayer()->getName();
            		$event->getPlayer()->setNameTag("[Processing..] $name");
        	}
    		if($this->plugin->provider === "yml"){
    			$myuser = new Config($this->plugin->getDataFolder() . "players/" . strtolower($event->getPlayer()->getName() . ".yml"), Config::YAML);
    			if($myuser->get("registered") === true){
    				if($this->autoclean){
    					$this->indexing->set($event->getPlayer()->getName());
    					$this->indexing->save();
    				}
                	$event->getPlayer()->setNameTag("[Not-Logged-In] $name");
	    			$this->plugin->loginmanager[$event->getPlayer()->getId()] = 1;
	    			if($this->plugin->ipAuth){
	    				if($myuser->get("ip") === $event->getPlayer()->getAddress()){
	    					$this->loginPlayer($player, $myuser)
                    		$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageIP);
                    		return;
	    				}
	    			}
	    			if($this->email){
    					$this->getPlayer()->sendMessage($this->typeEmail);
    				}
	    			$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageAlreadyRegistered);
    				$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageLogin);
    			}
    			else{
    				$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageRegisterPlease);
                	$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageWanted);
                	$event->getPlayer()->setNameTag("[Not-Registered] $name");
    				$this->plugin->loginmanager[$event->getPlayer()->getId()] = 0;
    			}
    		}
    		if($this->plugin->provider === "mysql"){
    			//MYsql
    		}
    	}
    }
    public function loginPlayer($player, $myuser){
    	$this->plugin->loginmanager[$player->getId()] = true;
        $this->plugin->chatprotection[$player->getId()] = md5($myuser->get("password"));
        $event->getPlayer()->setNameTag($player);
    }
    public function onChat(PlayerChatEvent $event){
    	$message = $event->getMessage();
    	if($this->plugin->loginmanager[$event->getPlayer()->getId()] === 1){
    		if($this->plugin->provider === "yml"){
    			$myuser = new Config($this->plugin->getDataFolder() . "players/" . strtolower($event->getPlayer()->getName() . ".yml"), Config::YAML);
    			if($myuser->get("email") === null && $this->email && $this->isRealEmail($event->getMessage())){
    				$myuser->set("email", $event->getMessage());
    				$myuser->save();
    				$event->getPlayer()->sendMessage($this->doneEmail);
    				return;
    			}
    			else{
    				$event->getPlayer()->sendMessage($this->failEmail);
    				return;
    			}
    			if(md5($message) === $myuser->get("password")){
    				$this->plugin->loginmanager[$event->getPlayer()->getId()] = true;
                		$this->plugin->chatprotection[$event->getPlayer()->getId()] = md5($message);
                    		$event->getPlayer()->setNameTag($event->getPlayer());
                    		if($myuser->get("ip") !== $event->getPlayer()->getAddress()){ //Ip updates.
                    			$myuser->set("ip", $event->getPlayer()->getAddress());
                    			$myuser->save();
                    		}
    				$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageLoggedIn);
    			}
    			else{
    				$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageIncorrect);
    				$this->protectForces($event->getPlayer());
    			}
    		}
    		return;
    	}
    	if($this->plugin->loginmanager[$event->getPlayer()->getId()] === 0 && !isset($this->plugin->chatprotection[$event->getPlayer()->getId()])){
    		if($this->plugin->provider === "yml"){
    			$this->plugin->chatprotection[$event->getPlayer()->getId()] = $this->proccessPassword($message, $event->getPlayer());
    			if(isset($this->plugin->chatprotection[$event->getPlayer()->getId()])){
    				$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->plugin->getConfig()->get("success"));
    			}
    		}
    		return;
    	}
    	if($this->plugin->loginmanager[$event->getPlayer()->getId()] === 0 && isset($this->plugin->chatprotection[$event->getPlayer()->getId()])){
    		if(md5($message) === $this->plugin->chatprotection[$event->getPlayer()->getId()]){
    			$myuser = new Config($this->plugin->getDataFolder() . "players/" . strtolower($event->getPlayer()->getName() . ".yml"), Config::YAML);
    			$myuser->set("password", $this->plugin->chatprotection[$event->getPlayer()->getId()]);
    			$myuser->set("registered", true);
    			$myuser->save();
    			if($myuser->get("password") === md5($message)){
    				$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageRegistered);
    				$this->plugin->loginmanager[$event->getPlayer()->getId()] = true;
    			}
    			else{
    				$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageError); //This should not happen anyways.
    			}
    		}
    		else{
    			$event->getPlayer()->sendMessage($this->plugin->prefix . " " . $this->messageNoSuccess);
    			unset($this->plugin->chatprotection[$event->getPlayer()->getId()]);
    		}
        }
    }
    private function proccessPassword($password, $player){
    	if(strlen($password) < $this->plugin->short){
    		$player->sendMessage($this->plugin->prefix . " " . $this->messageShort);
    		return;
    	}
    	elseif(strlen($password) > $this->plugin->max){
    		$player->sendMessage($this->plugin->prefix . " " . $this->messageLong);
    		return;
    	}
    	$simplepass = strtolower($password);
    	if($this->plugin->simplepassword === true && $this->plugin->status === "enabled"){
    		if($simplepass === 123456789 || $simplepass === 987654321 || $simplepass === "asdfg" || $simplepass === "password" || preg_match('/[A-Za-z]/', $simplepass) && preg_match('/[0-9]/', $simplepass)){
    			$player->sendMessage($this->plugin->prefix . " " . $this->messageSimple);
    			unset($this->plugin->chatprotection[$player->getId()]);
    			return;
    		}
    	}
    	$myuser = new Config($this->plugin->getDataFolder() . "players/" . strtolower($player->getName() . ".yml"), Config::YAML);
    	$myuser->set("password", md5($password));
    	$myuser->set("ip", $player->getAddress());
    	$myuser->set("version", $this->plugin->version); //For combatability in later updates.
    	$myuser->set("registered", true);
    	$myuser->set("date", time());
   // 	$myuser->set("email", null);
    	$this->plugin->ips->set(strtolower($player->getName()));
    	$this->plugin->ips->save();
    	$myuser->save();
    	return md5($password);
    }
    public function clearSession($player){
    	if($this->session){
    		unset($this->plugin->loginmanager[$player->getId()]);
    		unset($this->plugin->kicklogger[$player->getId()]);
    		unset($this->plugin->playerticks[$player->getId()]);
    		unset($this->plugin->chatprotection[$player->getId()]);
    	}
    	
    }
    private function protectForces($player){
    	if($this->plugin->protectForce){
    		if(!isset($this->plugin->kicklogger[$player->getId()])){ //Start protection.
    			$this->plugin->kicklogger[$player->getId()] = 1;
    		}
    		else{
    			$currentAttempts = $this->plugin->kicklogger[$player->getId()];
    			$currentAttempts++;
    			if($currentAttempts > $this->plugin->maxAttempts){
    				$player->kick($this->plugin->prefix . " " . $this->messageKick);
    				$this->clearSession($player);
    				return;
    			}
    			$this->plugin->kicklogger[$player->getId()] = $currentAttempts;
    			return $currentAttempts;
    		}
    	}
    }
    private function isRealEmail($email){ //Tries it's best to make sure email is real.
    	if(stristr($email, '@') && stristr($email, '.com') || stristr($email, '.net') || stristr($email, '.co') || stristr($email, '.tk') || stristr($email, 'me')){
    		if(strlen($email) > 4){
    			return true;
    		}
    	}
    	return false;
    }
}
