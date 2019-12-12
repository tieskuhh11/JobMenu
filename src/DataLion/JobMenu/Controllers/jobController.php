<?php

namespace DataLion\JobMenu\Controllers;

use DataLion\JobMenu\Main;
use mohagames\LevelAPI\utils\LevelManager;
use pocketmine\level\Level;

class jobController
{
    public static function getJobs(string $playername){
        $stmt = Main::getInstance()->handle->prepare("SELECT * FROM jobusers WHERE username = :username");
        $stmt->bindParam(":username", $playername);
        $result = $stmt->execute()->fetchArray();
        return json_decode($result["jobs"]);
    }

    public static function addJob(string $playername, string $jobname){
        if(jobController::hasJob($playername, $jobname)){
            return false;
        }

        $jobs = jobController::getJobs($playername);
        array_push($jobs, $jobname);
        if(sizeof($jobs) > Main::getInstance()->config->get("max-jobs")){
            return null;
        }

        self::setJobs($playername, $jobs);


        return true;

    }

    public static function removeJob(string $playername, string $jobname){
            if(!jobController::hasJob($playername, $jobname)){
                return false;
            }
            $jobs = jobController::getJobs($playername);
            if($jobname == Main::getInstance()->config->get("default-job")){
                return "defjob";
            }
            $index = array_search($jobname, $jobs);
            unset($jobs[$index]);
            $newjobs = [];
            foreach ($jobs as $job) {
                $newjobs[] = $job;
            }



            return true;

    }

    public static function setJobs(string $playername, array $joblist){
        $jobs = json_encode($joblist);
        $stmt = Main::getInstance()->handle->prepare("UPDATE jobusers SET jobs = :joblist WHERE username = :username");
        $stmt->bindParam(":joblist", $jobs);
        $stmt->bindParam(":username", $playername);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    public static function hasJob(string $playername, string $jobname) : bool {

        if(in_array($jobname, jobController::getJobs($playername))){
            return true;
        }else{
            return false;
        }
    }


}