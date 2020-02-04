<?php


namespace VIPSystem\Models;


class User
{

    private $user_name;
    private $user_steamid;
    private $user_base64_image;

    /**
     * User constructor.
     * @param $user_name
     * @param $user_steamid
     * @param $user_base64_image
     */
    public function __construct($user_name, $user_steamid, $user_base64_image)
    {
        $this->user_name = $user_name;
        $this->user_steamid = $user_steamid;
        $this->user_base64_image = $user_base64_image;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * @return mixed
     */
    public function getUserSteamID()
    {
        return $this->user_steamid;
    }

    /**
     * @return mixed
     */
    public function getUserBase64Image()
    {
        return $this->user_base64_image;
    }

    public function toArray() : array {
        return [
            "user_name" => $this->user_name,
            "user_steamid" => $this->user_steamid,
            "user_base64_image" => $this->user_base64_image,
        ];
    }

}