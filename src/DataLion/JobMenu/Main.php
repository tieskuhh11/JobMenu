<?php

declare(strict_types=1);

namespace DataLion\JobMenu;

use _64FF00\PureChat\PureChat;
use DataLion\JobMenu\Controllers\jobController;
use DataLion\JobMenu\Controllers\jobuserController;
use DataLion\JobMenu\Forms\jobsForm;
use mohagames\LevelAPI\utils\LevelManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    /**
     *  SYSTEM VARIABLES
     */
    public $handle;
    public $config;
    public $purechat;
    public static $instance;







    public function onEnable(){
        $this->getServer()->getLogger()->info("Enabled");

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        Main::$instance = $this;
        $default_values = [
            "max-jobs" => 2,
            "default-job" => "Burger",
            "xp-per-job-add" => 1000
        ];
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, $default_values);

        $this->handle = new \SQLite3($this->getDataFolder()."jobdb.db");
        $this->handle->query("CREATE TABLE IF NOT EXISTS jobusers(userid INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL , jobs TEXT DEFAULT null)");

        $this->purechat = $this->getServer()->getPluginManager()->getPlugin("PureChat");












    }


    public function onDisable()
    {

        $this->getServer()->getLogger()->info("Disabled");

    }


    public static function getInstance(){
        return Main::$instance;
    }



    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch($command->getName()){
            case "jobs":
                new jobsForm($sender->getServer()->getPlayer($sender->getName()));

                return true;
            case "job":

                if(isset($args[0])){
                    switch($args[0]) {

                        case "addjob":
                            if (!isset($args[2])) {
                                $this->help($sender);
                                return true;
                            }
                            if (!isset($args[1])) {
                                $this->help($sender);
                                return true;
                            }
                            if (!jobuserController::userExist($args[1])) {
                                $sender->sendMessage("§cUser niet gevonden!");
                                return true;
                            }
                            $res = jobController::addJob($args[1], $args[2]);
                            if (is_null($res)) {
                                $sender->sendMessage("§cThis player already reached the job amount limit!");
                                return true;
                            }
                            if ($res == false) {
                                $sender->sendMessage("§cThis player already owns this job!");
                                return true;
                            }

                            $sender->sendMessage("§aPlayer received job, this player can toggle this job by doing /job");
                            return true;







                        case "removejob":
                            if(!isset($args[1])){
                                $this->help($sender);
                                return true;
                            }
                            if(!isset($args[2])){
                                $this->help($sender);
                                return true;
                            }
                            if(!jobuserController::userExist($args[1])){
                                $sender->sendMessage("§cUser not found!");
                                return true;
                            }
                            $res = jobController::removeJob($args[1], $args[2]);

                                if($res === "defjob") {
                                    $sender->sendMessage("§cThis is the default job you can't delete this.");
                                    return true;
                                }else if(!$res) {
                                    $sender->sendMessage("§cThis player doesn't have this job!");
                                    return true;
                                }else{
                                    $sender->sendMessage("§aJob of player deleted!");
                                    return true;
                                }








                        case "checkjobs":
                            if(!isset($args[1])){
                                $this->help($sender);
                                return true;
                            }
                            if(!jobuserController::userExist($args[1])){
                                $sender->sendMessage("§cUser not found!");
                                return true;
                            }
                            $sender->sendMessage("§f---§2Jobs: §a".$args[1]."§7-§a(".sizeof(jobController::getJobs($args[1])).")§f---");
                            foreach (jobController::getJobs($args[1]) as $job){
                                $sender->sendMessage("§7- §7".$job);
                            }
                            return true;







                        default:

                            $this->help($sender);
                            break;

                    }
                }else{
                    return false;
                }



        }

        return true;
    }


    private function help($player){
        $commands = [
            "addjob <player> <jobnaam>" => "Add job to player.",
            "removejob <player> <jobnaam>" => "Delete job from player.",
            "checkjobs <player>" => "Get list of jobs player has."
        ];



        $player->sendMessage("§f-----§aTDB Job Main command: /job help§f-----");
        foreach ($commands as $option => $desc){
            $player->sendMessage("§7- §f/job ".$option." §8| §f".$desc);
        }
    }



    public function onjoin(PlayerJoinEvent $e){
        $player = $e->getPlayer();
        jobuserController::createUser($player->getName());


        $pc = $this->purechat;
        if($pc instanceof PureChat){
            $prefix = $pc->getPrefix($player);
            if($prefix === null){
                $pc->setPrefix($this->getConfig()->get("default-job"), $player);
                return;

            }
            if(!jobController::hasJob($player->getName(), $prefix)){
                $pc->setPrefix($this->getConfig()->get("default-job"), $player);
            }

        }
    }



}
