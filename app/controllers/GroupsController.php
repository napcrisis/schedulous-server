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
        $user_id = Input::get('auth.user_id');

        $registered = Input::get('registered');
        $unregistered = Input::get('unregistered');

        // create group record
        $group = Group::create(array('group_name' => $group_name, 'user_id' => $user_id));
        $group_id = $group->group_id;

        $this->addMembers($registered, $unregistered, $user_id, $group_id);

        $result = array('status' => 'success', 'group_id' => $group->group_id);
        Log::info(json_encode($result));
        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
        return $result;
    }

    public function postList()
    {
        $method_name = '[group] list';
        Log::info('===== START OF ' . strtoupper($method_name) . '  =====');
        Log::info('[' . Request::getClientIp() . '] ');
        Log::info(json_encode(Input::all()));

        $user_id = Input::get('auth.user_id');

        $user = User::find($user_id);
        $grouplist = $user->groups()->get(array('groups.user_id', 'groups.group_id', 'group_name', 'group_pic_url'));
        $user_group = array();
        foreach ($grouplist as $group) {
            $groupmembers = Group::find($group->group_id)->members()->get(array('group_users.user_id'));
            $members = array();
            foreach ($groupmembers as $person) {
                $user_id = $person->user_id;
                $phone_number = $user::find($user_id, ['international_number'])->international_number;
                array_push($members, array("user_id" => $user_id, 'international_number' => $phone_number));
            }

            $group_obj = array(
                'group_id' => $group->group_id,
                'group_name' => $group->group_name,
                'group_pic_url' => $group->group_pic_url,
                'members' => $members,
                'creator_id' => $group->user_id
            );
            array_push($user_group, $group_obj);
        }

        $result = array("status" => "success", "groups" => $user_group);
        Log::info(json_encode($result));
        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
        return $result;
    }

    public function postUpdateGroup()
    {
        $method_name = '[user] update-group';
        Log::info('===== START OF ' . strtoupper($method_name) . '  =====');
        Log::info('[' . Request::getClientIp() . '] ');
        Log::info(json_encode(Input::all()));

        $user_id = Input::get('auth.user_id');
        $group_id = Input::get('group_id');
        $group_name = Input::get('group_name');
        $url = Input::get('url');
        $registered = Input::get('registered');
        $unregistered = Input::get('unregistered');

        $group = null;
        if (!is_null($group_name) && !is_null($url)) { // update both group_name and url
            $group = Group::where('group_id', '=', $group_id)
                ->update(array('group_name' => $group_name, 'group_pic_url' => $url));
        } else if (!is_null($group_name)) { // update group_name only
            $group = Group::where('group_id', '=', $group_id)
                ->update(array('group_name' => $group_name));
        } else { // update url only
            $group = Group::where('group_id', '=', $group_id)
                ->update(array('group_pic_url' => $url));
        }

        if (!is_null($registered) || !is_null($unregistered)) {
            $this->addMembers($registered, $unregistered, $user_id, $group_id);
        }

        if (count($group) == 1) {
            $status = array("status" => "success");
        } else {
            $status = array("status" => "fail");
        }

        Log::info(json_encode($status));
        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
        return $status;
    }

    private function addMembers($registered, $unregistered, $user_id, $group_id)
    {
        // check if unregistered users are existing registered users
        $filtered_non_reg = array();
        foreach ($unregistered as $number) {
            $user = User::where('international_number', '=', $number)->get();
            if (count($user) == 1) {
                array_push($registered, $user[0]->user_id);
                continue;
            }
            array_push($filtered_non_reg, $number);
        }

        // create account for non-registered
        // and push their user_id to registered array
        foreach ($filtered_non_reg as $international_number) {
            $country = $this->countryFromNumber($international_number);
            $user = User::firstOrCreate(array('international_number' => $international_number, 'country' => $country));
            if ($user->user_id != $user_id) {
                User::find($user_id)->friends()->attach($user);
            }
            array_push($registered, $user->user_id);
        }

        // add registered users to group_users table
        foreach ($registered as $user_id) {
            Group::find($group_id)->members()->attach($user_id);
        }
    }
}
