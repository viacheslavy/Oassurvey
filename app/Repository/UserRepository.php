<?php

namespace App\Repository;

use App\Model\Permission;
use App\Model\Subuser;
use App\Model\User;

class UserRepository {
    const SALT = 'Zo4rU5Z1YyKJAASY0PT6EUg7BBYdlEhPaNLuxAwU8oas1ElzHv0Ri7EM6iraktpx5w';

    public static function hashPassword($password) {
        return hash('sha512', $password . UserRepository::SALT);
    }

    /**
     * @param $username
     * @param $password
     * @return User|null|static
     */
    public static function findByUsernameAndPassword($username, $password) {
        $hashedPassword = UserRepository::hashPassword($password);
        return User::where(['account_usn' => $username, 'account_pwd' => $hashedPassword])->first();
    }

    public static function usernameExists($id, $username, $type = User::PRIMARY) {
        $user = User::where('account_usn', $username)
            ->where('account_id', '<>', $id)
            ->first();

        return !!$user;
    }

    public static function findBySurveyId($surveyId) {
        $users = User::whereHas('permissions', function($query) use ($surveyId) {
            $query->where(['name' => Permission::CAN_VIEW_SURVEY, 'value' => $surveyId]);
        })->get();

        return $users;
    }

    public static function find($userId) {
//        $user = User::find($userId);
        return User::find($userId);
    }
}