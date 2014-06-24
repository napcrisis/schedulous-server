<?php

class ExampleTest extends TestCase
{
    /*
     * START OF VARIABLE INITIALIZATION
     */

    var $faker;
    var $generatedPhoneNumbers = '+659100000#';
    var $generatedUsers;
    var $numOfContacts = 2;

    // for user creation test case
    var $verification_input;
    var $id;

    public function CreateVerificationInput($code, $user_id)
    {
        return array(
            "user_id" => $user_id,
            "code" => $code,
            "country" => "singapore",
            "device_model" => "GT-I9100"
        );
    }

    public function GeneratePhonebook($user_id, $num)
    {

        $contacts = array();

        for ($i = 0; $i < $num; $i++) {
            $person = array(
                "international_number" => $this->faker->numerify($this->generatedPhoneNumbers),
                "country" => $this->faker->country()
            );
            array_push($contacts, $person);
        }

        return array(
            "user_id" => $user_id,
            "friends" => $contacts
        );
    }

    public function GenerateUser()
    {
        return array(
            "international_number" => $this->faker->numerify($this->generatedPhoneNumbers),
            "country" => $this->faker->country(),
            "device_model" => "GT-I9100"
        );
    }

    // for group creation test case
    public function GenerateGroup($existing_users, $num)
    {
        $contacts = array();
        $numbers = "";
        for ($i = 1; $i < $num; $i++) {
            $person = array(
                array_push($contacts, $existing_users[$i]->user_id)
            );
        }

        return array(
            "group_name" => "group 1",
            "user_id" => "1",
            "group_members" => array(
                "registered" => $contacts,
                "non_registered" => array(
                    $existing_users[count($existing_users) - 1]->international_number,
                    "+6523457896",
                    "+6587953424",
                )
            )
        );
    }

    /*
     * END OF VARIABLE INITIALIZATION
     */

    /**
     * START OF FUNCTIONAL TEST
     */
    public function testUserRegistration()
    {
        $this->faker = Faker\Factory::create();

        echo "====== Registering User ======" . PHP_EOL;
        $result = $this->UserRegistration($this->GenerateUser());
        echo "====== Registering User Completed ======" . PHP_EOL . PHP_EOL;

        $user_id = json_encode($result->user_id);
        $code = $result->code;

        echo "====== Verifying User ======" . PHP_EOL;
        $this->UserVerification($code, $user_id);
        echo json_encode(User::find($user_id)) . PHP_EOL;
        echo "====== Verifying User Completed ======" . PHP_EOL . PHP_EOL;

        echo "====== Sync Phonebook ======" . PHP_EOL;
        $this->SyncPhonebook($user_id);
        echo "====== Sync Phonebook Completed ======" . PHP_EOL . PHP_EOL;
    }

    public function testGroupCreation()
    {
        $this->faker = Faker\Factory::create();

        $existing_users = User::all(array('user_id', 'international_number'));

        echo "====== Creating Group ======" . PHP_EOL;
        $result = $this->CreateGroup($existing_users);
        echo "====== Creating Group Completed ======" . PHP_EOL . PHP_EOL;
    }

    /*
     * END OF FUNCTIONAL TEST
     */

    /*
     * START OF URL CALLING
     */

    // Test 1 - User Creation
    public function SyncPhonebook($user_id)
    {
        $phonebook = $this->GeneratePhonebook($user_id, $this->numOfContacts);

        $response = $this->call('POST', '/user/sync-phonebook', $phonebook);

        $result = json_decode($response->getContent());

        $this->assertTrue($response->isOk());

        echo json_encode($result) . PHP_EOL;
        return $result;
    }

    public function UserRegistration($user)
    {
        $response = $this->call('POST', '/user/register', $user);

        $result = json_decode($response->getContent());

        $this->assertTrue($response->isOk());

        echo json_encode($result) . PHP_EOL;
        return $result;
    }

    public function UserVerification($code, $user_id)
    {
        $input = $this->CreateVerificationInput($code, $user_id);
        $response = $this->call('POST', '/user/verify', $input);
        $result = json_decode($response->getContent());

        $this->assertTrue($response->isOk());

        echo json_encode($result) . PHP_EOL;
        return $result;
    }

    // Test 2 - Group Creation
    public function CreateGroup($existing_users)
    {
        $response = $this->call('POST', '/group/create', $this->GenerateGroup($existing_users, 3));
        $result = $response->getContent();

        echo json_encode($result) . PHP_EOL;
        return $result;
    }

    /*
     * END OF URL CALLING
     */
}
