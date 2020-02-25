<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
    const SALT = 'Zo4rU5Z1YyKJAASY0PT6EUg7BBYdlEhPaNLuxAwU8oas1ElzHv0Ri7EM6iraktpx5w';

    const PRIMARY = 1;
    const SUB = 2;

    const CREATED_AT = 'account_created';
    const UPDATED_AT = 'account_last_update_dt';

    protected $table = 'tblAccount';

    protected $primaryKey = 'account_id';

    protected $hidden = [
        'account_pwd'
    ];

    protected $fillable = [
        'account_first_name',
        'account_last_name',
        'account_email_address',
        'account_usn',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions() {
        return $this->hasMany('App\Model\Permission', 'user_id', 'account_id');
    }


    /**
     * @param Builder $query
     * @param string $username
     * @param string $password
     * @return Builder
     */
    public function scopeByUsernameAndPassword($query, $username, $password) {
        $hashedPassword = $this->hashPassword($password);
        return $query->where(['account_usn' => $username, 'account_pwd' => $hashedPassword]);
    }

    public static function hashPassword($password) {
        return hash('sha512', $password . self::SALT);
    }

    public function getPermissions() {
        return ['these', 'are', 'the', 'permissions'];
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    public function hasPermission($name) {
        if ($this->isAdmin())
            return true;

        $permission = $this->permissions()->where('name', '=', $name)->first();

        return $permission;
    }

    /**
     * @param $name
     * @param $value
     * @return bool|mixed
     */
    public function hasPermissionWithValue($name, $value) {
        if ($this->isAdmin())
            return true;

        $permission = $this->permissions()->where(['name' => $name, 'value' => $value])->first();

        if (!$permission)
            return false;

        return $permission->value ? $permission->value : true;
    }

    /**
     * @param $surveyId
     * @return bool|mixed
     */
    public function canViewSurvey($surveyId) {
        return $this->hasPermissionWithValue(Permission::CAN_VIEW_SURVEY, $surveyId);
    }

    /**
     * For now, anybody that has 0 permission table entries is an admin.
     *
     * @return bool
     */
    public function isAdmin() {
        return $this->permissions()->where('name', '=', 'Admin')->first();
    }
}