<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "type",
        "icon",
        "thumbnail",
        "publisher",
        "slug",
        "is_active",
        "is_popular",
        "is_new",
        "sort_order",
        "commission_type",
        "commission_value",
        "input_fields",
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_popular'   => 'boolean',
        'is_new'       => 'boolean',
        'input_fields' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get input fields with defaults if none configured.
     */
    public function getFormFields(): array
    {
        if (!empty($this->input_fields)) {
            return $this->input_fields;
        }

        // Default fields based on category type
        $type = strtolower((string) $this->type);
        $gameTypes = ['game', 'seluler', 'pc', 'voucher'];

        if (in_array($type, $gameTypes)) {
            return [
                ['name' => 'target', 'label' => 'User ID', 'placeholder' => 'Masukkan User ID', 'required' => true],
                ['name' => 'target_zone', 'label' => 'Server', 'placeholder' => 'Masukkan Server (jika ada)', 'required' => false],
            ];
        }

        // PPOB default
        return [
            ['name' => 'target', 'label' => 'Nomor Pelanggan', 'placeholder' => 'Masukkan Nomor', 'required' => true],
        ];
    }
}
