<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Akun extends Authenticatable
{
    use Notifiable;

    protected $table = 'data_akun';

    protected $fillable = [
        'username',
        'password_hash',
        'nama',
        'role',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Override default password field for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
