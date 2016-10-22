<?php namespace Vis\Builder;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = array('id_user', 'ip_user', 'message', "model", "id_record", "action");
}
