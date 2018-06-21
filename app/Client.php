<?php

namespace CannaPlan;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    // This model represents the table oath client. We will be using it to get the client id and secrets
    protected $table = 'oauth_clients';
}
