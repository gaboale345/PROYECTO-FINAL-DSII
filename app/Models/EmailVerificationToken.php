<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationToken extends Model
{
    use HasFactory;

    protected $table = 'email_verification_tokens';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
