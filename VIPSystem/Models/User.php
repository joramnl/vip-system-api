<?php


namespace VIPSystem\Models;


class User
{

    private $user_id;
    private $user_name;
    private $user_steamid;
    private $user_base64_image;

    /**
     * User constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->user_id = $data[0]["user_id"];
        $this->user_name = $data[0]["user_name"];
        $this->user_steamid = $data[0]["user_steamid"];
        $this->user_base64_image = $data[0]["user_base64_image"];
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
    public function getUserId()
    {
        return $this->user_id;
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
            "user_id" => $this->user_id,
            "user_name" => $this->user_name,
            "user_steamid" => $this->user_steamid,
            "user_base64_image" => $this->user_base64_image,
        ];
    }

}