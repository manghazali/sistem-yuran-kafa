<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentGatewayTransaksi extends Model
{
    protected $table = 'payment_gateway_transaksi';

    protected $fillable = [
        'order_id',
        'amaun',
        'status',
        'transaction_id',
        'msg',
        'payload_callback',
    ];

    protected function casts(): array
    {
        return [
            'amaun' => 'decimal:2',
        ];
    }

    public function itemBayaran(): HasMany
    {
        return $this->hasMany(PaymentGatewayTransaksiItem::class, 'payment_gateway_transaksi_id');
    }
}
