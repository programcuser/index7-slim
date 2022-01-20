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

        $usersNum = count($usersArr);
        $newUser['id'] = $usersNum + 1;

        $usersArr[] = $newUser;
        $usersJson = json_encode($usersArr, JSON_PRETTY_PRINT);

        file_put_contents($this->path, $usersJson);
    }

    public function find(int $id)
    {
        $usersArr = collect($this->getUsers());
        return $usersArr->firstWhere('id', $id);
    }

    public function all()
    {
        return $this->getUsers();
    }

    private function getUsers(): array
    {
        $users = file_get_contents($this->path);
        $usersArr = json_decode($users, true);
        return $usersArr;
    }
}
