<?php

namespace App\Helpers;

use App\DB\Models\ActivityLog;
use App\DB\Models\UserDevices;
use Jenssegers\Agent\Agent;
use Torann\GeoIP\GeoIP;

class UserHelpers
{
    public static function isLoggedIn() : bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
    public static function isAdmin() : bool{
        return session_status() === PHP_SESSION_ACTIVE;
    }
    public static function isUser() : bool{
        return session_status() === PHP_SESSION_ACTIVE;
    }
    public static function isGuest() : bool{
        return session_status() === PHP_SESSION_ACTIVE;
    }
    public static function get_clientIp()
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    public static function createUserActivity($userId, $action = ''): bool
    {
        $current_ip = self::get_clientIp();
        $agent = new Agent();
        $deviceType = $agent->isMobile() ? 'Mobile' : 'Web';

//        try{
//            $location = GeoIP::getLocation($current_ip);
//            $country = $location->country;
//        }catch(\Exception $e){
//            $country  = '';
//        }
        $activity['user_id'] = $userId;
        $activity['action'] = $action;
        $activity['ip_address'] = $current_ip ?? '0.0.0.0';
        $activity['source'] = $deviceType;
        $activity['location'] = '';
        return (bool)ActivityLog::create($activity);
    }
    public static function AddUserDevice($userId): bool
    {
        $agent = new Agent();
        $deviceType = $agent->isMobile() ? 'Mobile' : 'Web';
        if(UserDevices::where(['user_id' => $userId, 'device_type' => $deviceType])->exists()){
            return true;
        }
        $device['user_id'] = $userId;
        $device['device_type'] = $deviceType;
        $device['name'] = $agent->device();
        return (bool)UserDevices::create($device);
    }
}