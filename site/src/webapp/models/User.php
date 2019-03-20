<?php

namespace ttm4135\webapp\models;

use PDO;

class User
{
    const INSERT_QUERY = "INSERT INTO users(username, password, email, isadmin) VALUES(? , ? , ? , ?)";
    const UPDATE_QUERY = "UPDATE users SET username=?, password=?, email=?, isadmin='? WHERE id='%s'";
    const DELETE_QUERY = "DELETE FROM users WHERE id='%s'";
    const FIND_BY_NAME_QUERY = "SELECT * FROM users WHERE username=:username";
    const FIND_BY_ID_QUERY = "SELECT * FROM users WHERE id=:id";
    protected $id = null;
    protected $username;
    protected $password;
    protected $email;
    protected $isAdmin = 0;

    static $app;


    static function make($id, $username, $password, $email, $isAdmin)
    {
        $user = new User();
        $user->id = $id;
        $user->username = $username;
        $user->password = $password;
        $user->email = $email;
        $user->isAdmin = $isAdmin;

        return $user;
    }

    static function makeEmpty()
    {
        return new User();
    }

    /**
     * Insert or update a user object to db.
     */
    function save()
    {
        // Data to be used
        $data = [
            $this->username,
            $this->password,
            $this->email,
            $this->isAdmin
        ];
        if ($this->id === null) {
            $query = self::$app->db->prepare(self::INSERT_QUERY);
            return $query->execute($data);

        } else {
            $query = self::$app->db->prepare(self::UPDATE_QUERY);
            array_push($data, $this->id);
            return $query->execute($data);
        }
    }

    function delete()
    {
        $query = sprintf(self::DELETE_QUERY,
            $this->id
        );
        return self::$app->db->exec($query);
    }

    function getId()
    {
        return $this->id;
    }

    function getUsername()
    {
        return $this->username;
    }

    function getPassword()
    {
        return $this->password;
    }

    function getEmail()
    {
        return $this->email;
    }

    function isAdmin()
    {
        return $this->isAdmin === "1";
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setUsername($username)
    {
        $this->username = $username;
    }

    function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    function setEmail($email)
    {
        $this->email = $email;
    }

    function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }


    /**
     * Get user in db by userid
     *
     * @param string $userid
     * @return mixed User or null if not found.
     */
    static function findById($userid)
    {
        $query = self::$app->db->prepare(self::FIND_BY_ID_QUERY);
        if ($query->execute([':id' => $userid])) {
            $row = $query->fetch();
            if ($row) {
                return User::makeFromSql($row);
            }
        }
        return null;
    }

    /**
     * Find user in db by username.
     *
     * @param string $username
     * @return mixed User or null if not found.
     */
    static function findByUser($username)
    {
        $query = self::$app->db->prepare(self::FIND_BY_NAME_QUERY);
        if ($query->execute([':username' => $username])) {
            $row = $query->fetch();
            if ($row) {
                return User::makeFromSql($row);
            }
        }
        return null;
    }


    static function all()
    {
        $query = "SELECT * FROM users";
        $results = self::$app->db->query($query);

        $users = [];

        foreach ($results as $row) {
            $user = User::makeFromSql($row);
            array_push($users, $user);
        }

        return $users;
    }

    static function makeFromSql($row)
    {
        return User::make(
            $row['id'],
            $row['username'],
            $row['password'],
            $row['email'],
            $row['isadmin']
        );
    }

    public function __toString()
    {
        return implode(" ", [
            "id" => $this->id,
            "username" => $this->id,
            "password" => $this->password,
            "email" => $this->email,
            "isAdmin" => $this->isAdmin
        ]);
    }

}


User::$app = \Slim\Slim::getInstance();

