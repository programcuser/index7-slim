<?php

namespace App;

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
