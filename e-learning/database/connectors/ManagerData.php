<?php
/**
 * Created by PhpStorm.
 * User: s1500631
 * Date: 8.11.2017
 * Time: 11.23
 */
namespace database\connectors;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;

class ManagerData{
    /**
     * @return mixed returns list of all managers. When you have a manager you can get his data by refering the variable '$manager->account' this returns the managers name.
     */
    public static function getManagers(){
        try{
            $managers = DB::select('select * from manager;');
            return $managers;
        }catch (\Exception $ex){
            return null;
        }
    }

    /**
     * Creates a manager with all relations. Relations are created in MySQL with triggers.
     * @param $account Name of the manager you want to create.
     * @param $password Password of the manager you want to create. Password should be hashed already when you are gathering it if not hash it manually.
     * @param $status Status of the manager you want to create.
     * @param $creationip CreationIp of the manager you want to create.
     */
    public static function insertManager($account,$password,$status){
        DB::beginTransaction();
        try{
            DB::statement('set @disable_update_logintime  = 1');
            DB::insert('insert into manager (account,password,status,creationtime,updatetime,lastlogintime,creationip,lastloginip,loginnum)
            values(?,?,?,now(),now(),now(),?,?,loginnum + 1);',[$account,$password,$status,request()->ip(),request()->ip()]);
            DB::statement('set @disable_update_logintime  = null');
            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
        }
    }
    public static function insertManagerHash($account,$password,$status){
        DB::beginTransaction();
        try{
            DB::statement('set @disable_update_logintime  = 1');
            $hashed = Hash::make($password);
            DB::insert('insert into manager (account,password,status,creationtime,updatetime,lastlogintime,creationip,lastloginip,loginnum)
            values(?,?,?,now(),now(),now(),?,?,loginnum + 1);',[$account,$hashed,$status,request()->ip(),request()->ip()]);
            DB::statement('set @disable_update_logintime  = null');
            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
        }
    }
    public static function checkManagerAccoutName($account){
        $result=DB::select('select * from manager where account = ?',[$account]);
        try{
            if(!(is_null($result[0]))) {
                return true;
            }else return false;
        }catch (\Exception $exception){}
    }
    /**
     * @param $id managerid which you are looking from the manager which data you are looking for.
     * @return mixed returns variable which contains all tdelhe data from manager column in database
     */
    public static function getManager($id){
        try{
            $result = DB::select('select * from manager where managerid = ?',[$id])[0];
            return $result;
        }catch (\Exception $ex){
            return null;
        }
    }

    /**
     * @param $id Managerid of the manager your role you wanted to know.
     * @return mixed Returns everything in the role column that it found with the managerid given.
     *
     */
    public static function getManagerRole($id){
        try{
            $roleid = DB::select('select * from manager_role where manager_id = ?',[$id])[0]->role_id;
            $role = DB::select('select * from role where roleid = ?',[$roleid]);
            return $role[0];
        }catch (\Exception $ex){
            return null;
        }
    }
    public static function getPw($account){
        try{
            return DB::select('select password from manager where account = ?',[$account])[0]->password;
        }catch(\Exception $exception){}
    }
    /**
     * @param $id Id of the manager.
     * @param $password Unhashed password attempt.
     * @param $newpassword New password you were about to create.
     */
    public static function updatePassword($id,$password,$newpassword){
        DB::beginTransaction();
        try{
            if(self::checkPassword($id,$password)){
                $hashed = Hash::make($newpassword);
                DB::update('update manager set password =? where managerid = ?',[$hashed,$id]);
            }
            DB::commit();
            return true;
        }catch (\Exception $ex){
            DB::rollBack();
            return false;
        }
    }

    /**
     * @param $id managerid of the manager in question.
     * @param $password Unhashed password for checking if its right.
     * @return bool Returns true if the password was right returns false if the password was wrong.
     */
    private static function checkPassword($id,$password){
        try{
            $hashed = DB::select('select password from manager where managerid = ?',[$id])[0]->password;
            if(Hash::check($password,$hashed)){
                return true;
            }else return false;
        }catch (\Exception $ex){
            echo('checkpassword failed'
            );
            return false;
        }
    }

    /**
     * @param $id
     * @param $password
     */
    public static function deleteManager($id,$password){
        DB::beginTransaction();
        try {
            if (self::checkPassword($id, $password)) {
                DB::delete('delete from manager_loginlog where manager_id = ?',[$id]);
                DB::delete('delete from manager_role where manage_rid = ?',[$id]);
                DB::delete('delete from manager where managerid = ?', [$id]);
                DB::commit();
            }
        }catch(\Exception $ex){
            echo($ex);
            DB::rollBack();
        }
    }

    /**
     * Tries the managers alleged name and the managers alleged password to login.
     * @param $manageraccount Accounts name.
     * @param $triedpassword Managers alleged password.
     * @return bool Returns whether login was successful or not.
     */
    public static function login($manageraccount, $triedpassword,$browser){
        try{
            $result=DB::select('select password,managerid from manager where account = ?',[$manageraccount])[0];
            $hashed = $result->password;
            if(Hash::check($triedpassword,$hashed)) {
                self::insertLoginLog($result->managerid,request()->ip(),true,$browser);
                return true;
            } else {
               self::insertLoginLog($result->managerid,request()->ip(),false,$browser); # change the ip. atm i dont know how to get the ip because im testing locally not running the server.
                return false;
            }
        }catch (\Exception $ex){
            return false;
        }
    }
    //TODO Possible error incoming when trying to insert chinese characters into database.
    /**
     * When manager tries to login to his account, he triggers this method and sends data about the login to the server.
     * @param $id Who tried logging in.
     * @param $ip  Where did he try to login.
     * @param $result Whether he was successful or not.
     */
    private static function insertLoginLog($id,$ip,$result,$browser){
        DB::beginTransaction();
        try{
            if($result){
                DB::insert('insert into manager_loginlog (manager_id, logintime, loginip, result,browser) values (?,now(),?,?,?)',[$id,$ip,$result,$browser]);
            }else{
                DB::statement('set @disable_update_logintime = 1');
                DB::insert('insert into manager_loginlog (manager_id, logintime, loginip, result,browser) values (?,now(),?,?,?)',[$id,$ip,$result,$browser]);
                DB::statement('set @disable_update_logintime = null');
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
        }
    }
    public static function getManagerByAccount($account){
        try{
            $result = DB::select('select * from manager where account = ?',[$account])[0];
            return $result;
        }catch (\Exception $ex){
            return null;
        }
    }
    public static function getManagerId($account){
        try{
            $result = DB::select('select managerid from manager where account = ?',[$account])[0]->managerid;
            return $result;
        }catch (\Exception $ex){
            return null;
        }
    }
}
