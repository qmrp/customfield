<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Qmrp\CustomField\Traits\HasCustomFields;

class Product extends Model
{
    use HasCustomFields;

    protected $fillable = [
        'name',
        'price',
        'quantity',
        'category_id',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }
}

class Category extends Model
{
    protected $fillable = ['name', 'description'];
}

class Order extends Model
{
    protected $fillable = ['user_id', 'total', 'status'];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
