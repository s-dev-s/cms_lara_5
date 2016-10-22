<?php namespace Vis\Builder;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{

    protected $table = 'roles';
    protected $fillable = array('name', 'slug', 'permissions');

    public $timestamps = false;
}
