<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'is_bot',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'is_premium',
        'added_to_attachment_menu',
        'can_join_groups',
        'can_read_all_group_messages',
        'supports_inline_queries',
        'can_connect_to_business',
        'has_main_web_app',

        'violations',
        'last_violation_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_bot' => 'boolean',
            'is_premium' => 'boolean',
            'added_to_attachment_menu' => 'boolean',
            'can_join_groups' => 'boolean',
            'can_read_all_group_messages' => 'boolean',
            'supports_inline_queries' => 'boolean',
            'can_connect_to_business' => 'boolean',
            'has_main_web_app' => 'boolean',
            'last_violation_at' => 'datetime',
        ];
    }
}
