<?php

namespace App;

/*
class UserRepository
{
    private $path;

    public function __construct(){
        $this->path = __DIR__ . '/../files/users/users.json';
    }

    public function save(array $newUser)
    {
        $usersArr = $this->getUsers();

        if (count($usersArr) !== 0) {
            $lastKey = array_key_last($usersArr);
            $lastId = $usersArr[$lastKey]['id'];
            $newId = $lastId + 1;
            $newUser['id'] = $newId;

            $usersArr[$newId] = $newUser;
        } else {
            $id = 1;
            $newUser['id'] = $id;
            $usersArr[$id] = $newUser;
        }

        $usersJson = json_encode($usersArr, JSON_PRETTY_PRINT);

        file_put_contents($this->path, $usersJson);
    }

    public function find(int $id)
    {
        $usersArr = $this->getUsers();
        
        if (array_key_exists($id, $usersArr)) {
            return $usersArr[$id];
        }

        return null;
    }

    public function destroy(int $id)
    {
        $usersArr = $this->getUsers();

        if (array_key_exists($id, $usersArr)) {
            unset($usersArr[$id]);

            $usersJson = json_encode($usersArr, JSON_PRETTY_PRINT);
            file_put_contents($this->path, $usersJson);
        }
    }


    public function all()
    {
        return array_values($this->getUsers());
    }

    private function getUsers(): array
    {
        $users = file_get_contents($this->path);
        $usersArr = json_decode($users, true);
        return $usersArr;
    }
}
*/

class UserRepository
{
    private $jsonArr;

    public function __construct(string $jsonStr){
        $this->jsonArr = json_decode($jsonStr, true);
    }

    public function save(array $newUser)
    {
        if (count($this->jsonArr) !== 0) {
            if (!array_key_exists('id', $newUser)) {
                $lastKey = array_key_last($this->jsonArr);
                $lastId = $this->jsonArr[$lastKey]['id'];
                $newId = $lastId + 1;
                $newUser['id'] = $newId;

                $this->jsonArr[$newId] = $newUser;
            } else {
                $id = $newUser['id'];
                $this->jsonArr[$id] = $newUser;
            }
        } else {
            $id = 1;
            $newUser['id'] = $id;
            $this->jsonArr[$id] = $newUser;
        }
    }

    public function find(int $id)
    {    
        if (array_key_exists($id, $this->jsonArr)) {
            return $this->jsonArr[$id];
        }

        return null;
    }

    public function destroy(int $id)
    {
        if (array_key_exists($id, $this->jsonArr)) {
            unset($this->jsonArr[$id]);
        }
    }

    public function all()
    {
        return array_values($this->jsonArr);
    }

    public function getJson() {
        return json_encode($this->jsonArr);
    }
}
