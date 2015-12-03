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
- Spiders! What's this class you may ask? Spiders
= Creep and crawl your player folder to auto delete old player profiles if enabled.
*/
class DataSpider implements Listener{
	public function __construct(Loader $plugin){
        $this->plugin = $plugin;
  }
  public function createSpider(){
    if($this->plugin->getConfig("maxaccounts")){ //Creates a spider.
    //	$this->sendSpiderToFolders();
  }
  private function sendSpiderToFolder(){ //Sends spiders to clean up stuff.
    $file = $this->plugin->getDataFolder() . "index.txt";
    $indexing = fgets(fopen($this->plugin->getDataFolder() . "index.txt", 'r'));
    $spider = $this->getServer()->getPlayer($indexing);
    if(file_exists(new Config($this->plugin->getDataFolder() . "players/" . strtolower($spider->getName() . ".yml"), Config::YAML))){
    	$myuser = new Config($this->plugin->getDataFolder() . "players/" . strtolower($event->getPlayer()->getName() . ".yml"), Config::YAML);
    	$date = $myuser->get("date");
    	if($date > 0){
    		unlink(new Config($this->plugin->getDataFolder() . "players/" . strtolower($spider->getName() . ".yml"), Config::YAML));
    		$file->remove($spider->getName());
    		$file->save();
    		if($this->owner->debug){
    			array_push($this->owner->mainlogger, "Spider> Deleted $indexing from players folder.");
    		}
    		return true;
    	}
    	return false;
    }
  }
}