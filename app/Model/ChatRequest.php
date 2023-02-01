<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class ChatRequest extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_requests';
	
	public function guest()
    {
		return $this->belongsTo('App\Guest','guest_id');
    }
	
	public function operator()
    {
		return $this->belongsTo('App\User','operator_id');
    }
}