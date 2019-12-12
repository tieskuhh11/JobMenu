<?php

namespace DataLion\JobMenu\Controllers;

use DataLion\JobMenu\Main;

class jobuserController
{
    public static function userExist(string $playername){
        $stmt = Main::getInstance()->handle->prepare("SELECT count(*) as amount FROM jobusers WHERE username = :username");
        $stmt->bindParam(":username",$playername);
        $result = $stmt->execute()->fetchArray();
        $stmt->close();
        if($result["amount"] == 0){
            return false;
        }else{
            return true;
        }

    }


    public static function createUser(string $playername){
        if(jobuserController::userExist($playername)){
            return false;
        }else{

            $standard = Main::getInstance()->config->get("default-job");
            $jobs = json_encode(array($standard));
            $stmt = Main::getInstance()->handle->prepare("INSERT INTO jobusers(username, jobs) VALUES (:username, :jobs)");
            $stmt->bindParam(":username", $playername);
            $stmt->bindParam(":jobs", $jobs);
            $stmt->execute();
            $stmt->close();

            return true;
        }
    }
}