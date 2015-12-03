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
/*
- Spiders! What's this class you may ask? Spiders creep and crawl into
- your player folder to auto-delete old player profiles if enabled.
*/
class DataSpider implements Listener{
	public function __construct(Loader $plugin){
        $this->plugin = $plugin;
  }
  public function createSpider(){
    if($this->plugin->getConfig("auto-cleanup")){ //Creates a spider.
    	$this->sendSpiderToFolder();
    }
  }
  /*
  - API Key:
  - Spider deleted profile = true;
  - Spider did not delete profile or player is online = false;
  */
  private function sendSpiderToFolder(){ //Sends spiders to clean up stuff.
    $indexing = fgets(fopen($this->plugin->getDataFolder() . "index.txt", 'r'));
    $myuser = new Config($this->plugin->getDataFolder() . "players/" . strtolower($indexing . ".yml"), Config::YAML);
    $date = $myuser->get("date");
    # Check if registered.
    if($this->getServer()->getPlayer($indexing) !== null){
    	return false;
    }
    elseif($myuser->get("registered") !== true){
    	unlink(new Config($this->plugin->getDataFolder() . "players/" . strtolower($indexing . ".yml"), Config::YAML));	
    	return true;
    }
    # Check date of registered (To delete)
    elseif($date > 0){
    	unlink(new Config($this->plugin->getDataFolder() . "players/" . strtolower($indexing->getName() . ".yml"), Config::YAML));
    	$file = $this->plugin->getDataFolder() . "index.txt";
    	$file->remove($indexing);
    	$file->save();
    	array_push($this->plugin->mainlogger, "xAuthSpider> Deleted $indexing from players folder.");
    	return true;
    }
    return false;
  }
}
