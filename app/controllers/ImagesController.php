<?php

class ImagesController extends BaseController
{
    public function postUpload()
    {
        $type = Input::get('type');
        $user_id = "";
        $group_id = "";

        if (Input::has('user_id'))
            $user_id = Input::get('user_id');
        else
            $group_id = Input::get('group_id');

        $destinationPath = 'uploads';
        // If the uploads fail due to file system, you can try doing public_path().'/uploads'
        $filename = str_random(12);
        //$filename = $file->getClientOriginalName();
        //$extension =$file->getClientOriginalExtension();
        $upload_success = Input::file('file')->move($destinationPath, $filename);

        if ($upload_success) {
               Response::json('success', 200);
        } else {
               Response::json('error', 400);
        }
    }

}

?>