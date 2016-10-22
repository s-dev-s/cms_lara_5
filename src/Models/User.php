<?php namespace Vis\Builder;

use Cartalyst\Sentinel\Users\EloquentUser;

class User extends EloquentUser
{

    protected $table = 'users';

    public function getAvatar(array $imgParam)
    {
        if ($this->image) {
            $image = $this->image;
        } else {
            $image = "/packages/vis/builder/img/blank_avatar.gif";
        }

        return glide($image, $imgParam);
    }

    public function getFullName()
    {
        return $this->first_name." ".$this->last_name;
    }
}
