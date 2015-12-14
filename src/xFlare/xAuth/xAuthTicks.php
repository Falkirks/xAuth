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

use pocketmine\Server;
use pocketmine\scheduler\PluginTask;
/*
- Manages xAuth ticks. Many things are controlled here, but it's all optional!
*/
class xAuthTicks extends PluginTask{
    public function __construct(Loader $plugin){
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->disable = $this->owner->prefix . " " . $this->owner->getConfig()->get("hotbar-disabled");
        $this->loginhotbar = $this->owner->prefix . " " . $this->owner->getConfig()->get("hotbar-login");
        $this->registerhotbar = $this->owner->prefix . " " . $this->owner->getConfig()->get("hotbar-register");
        $this->timeout = $this->owner->prefix . " " . $this->owner->getConfig()->get("timeout");
    }
    public function onRun($currentTick){
    	# Timeout & hotbar message.
        foreach($this->owner->getServer()->getOnlinePlayers() as $p){
        	if($this->owner->hotbar){
                	if(isset($this->owner->loginmanager[$p->getId()]) && $this->owner->loginmanager[$p->getId()] === 1){
                   		$p->sendTip($this->loginhotbar);
                	}
                	elseif(isset($this->owner->loginmanager[$p->getId()]) && $this->owner->loginmanager[$p->getId()] === 0){
                   		$p->sendTip($this->registerhotbar);
                	}
            }
            if($this->owner->timeoutEnabled){
            	if(isset($this->plugin->loginmanager[$p->getId()]) && $this->owner->loginmanager[$p->getId()] !== true){
    				if(!isset($this->playerticks[$p->getId()])){
    					$this->owner->playerticks[$p->getId()] = 0;
    				}
    				$myticks = $this->owner->playerticks[$p->getId()];
    				$myticks++;
    				if($myticks * 20 > $this->owner->timeoutMax){
    					$p->kick($this->timeout);
    				}
    				$this->owner->playerticks[$p->getId()] = $myticks;
    			}
        }
    	# Logger.
    	if(!isset($this->owner->mainlogger[$this->owner->loggercount])){
    		return;
    	}
        $message = $this->owner->mainlogger[$this->owner->loggercount];
    	$this->owner->loggercount++;
    	if($this->owner->status === "enabled"){
      		$prefix = "§7[§axAuth§7]";
    	}
    	elseif($this->owner->status === "failed"){
      		$prefix = "§7[§cFailure§7]";
    	}
    	elseif($this->owner->status === null){
      		$prefix = "§7[§ePreloadError§7]";
    	}
    	$exception = "$prefix $message";
	    $this->owner->getServer()->getLogger()->info($exception);
	    if($this->owner->logger){
		    $file = $this->plugin->getDataFolder() . "xauthlogs.log";
    	  	file_put_contents($file, $exception);
	    }
	    if($this->owner->loggercount > $this->owner->getConfig()->get("dump-logger")){ //Dump logger cache.
		  $this->owner->mainlogger = [];
		  $this->owner->loggercount = 0;
	    }  
    }
}
