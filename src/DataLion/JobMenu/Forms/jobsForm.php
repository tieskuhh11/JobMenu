<?php

namespace DataLion\JobMenu\Forms;
use _64FF00\PureChat\PureChat;
use DataLion\JobMenu\Controllers\jobController;
use DataLion\JobMenu\Main;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;

class jobsForm
{
    private $jobs;

    public function __construct(Player $player)
    {

        $this->jobs = jobController::getJobs($player->getName());


        $form = new SimpleForm(function(Player $player, $data){
            if($data !== null){
                if(isset($data)){
                    $this->handleForm($player, $data);
                    return true;
                }else{
                    $player->sendMessage("§cUnknown error please contact: §aDataLion");
                    return false;
                }
            }
            return false;
        });
        $form->setTitle("Job selection");
        $form->setContent("Select Job");
        foreach ($this->jobs as $job){
            if($job == Main::getInstance()->config->get("default-job")){
                $form->addButton("§l".$job);
            }else{
                $form->addButton($job);
            }
        }

        $player->sendForm($form);



    }


    private function handleForm(Player $player, $data){
        $pc = Main::getInstance()->purechat;
        if($pc instanceof PureChat){
            $pc->setPrefix($this->jobs[$data], $player);
        }
        $player->sendMessage($this->jobs[$data]." §aSelected.");

    }
}