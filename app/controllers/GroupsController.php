<?php

class GroupsController extends BaseController
{
    public function postCreate()
    {
        $method_name = '[group] create';
        Log::info('===== START OF ' . strtoupper($method_name) . '  =====');
        Log::info('[' . Request::getClientIp() . '] ');
        Log::info(json_encode(Input::all()));

        $group_name = Input::get('group_name');
        $user_id = Input::get('user_id');

        $group_members = Input::get('group_members');
        $registered = $group_members['registered'];
        $non_registered = $group_members['non_registered'];

        // create group record
        $group = Group::create(array('group_name' => $group_name, 'user_id' => $user_id));

        // check if non_registered users are existing registered users
        $filtered_non_reg = array();
        foreach ($non_registered as $number) {
            $user = User::where('international_number', '=', $number)->get();
            if (count($user) == 1) {
                array_push($registered, $user[0]->user_id);
                continue;
            }
            array_push($filtered_non_reg, $number);
        }

//        echo json_encode($registered) . PHP_EOL;

        // create account for non-registered
        // push their user_id to registered array
        $phoneUtil = PhoneNumberUtil::getInstance();
        $geocoder = PhoneNumberOfflineGeocoder::getInstance();
        foreach ($filtered_non_reg as $international_number) {
            $numberProto = $phoneUtil->parse($international_number, "US");
            $country = strtolower($geocoder->getDescriptionForNumber($numberProto, "en_US"));
            $user = User::firstOrCreate(array('international_number' => $international_number, 'country' => $country));
//            echo json_encode($user) . PHP_EOL;
            if ($user->user_id != $user_id) {
                User::find($user_id)->friends()->attach($user);
            }
            array_push($registered, $user->user_id);
        }

        // push creator's user_id to registered array
        array_push($registered, $user_id);
//        echo json_encode($registered) . PHP_EOL;

        // add registered users to group_users table
        foreach ($registered as $user_id) {
            //$member = GroupUser::create(array('group_id' => $group->group_id, 'user_id' => $user_id));
            //echo json_encode($member) . PHP_EOL;
            Group::find($group->group_id)->group_user()->attach($user_id);
        }

//        $members = GroupUser::all();
//        echo json_encode($members) . PHP_EOL;

        $group = Group::find($group->group_id);
        $group_members = $group->group_user()->get(array('users.user_id', 'users.profile_pic', 'users.international_number', 'users.name'));

        $result = array('group' => $group, 'members' => $group_members);
        echo json_encode($result) . PHP_EOL;
        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
        return null;

    }
}
