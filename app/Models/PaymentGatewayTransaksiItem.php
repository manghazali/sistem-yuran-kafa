<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewayTransaksiItem extends Model
{
    protected $table = 'payment_gateway_transaksi_item';

    protected $fillable = [
        'payment_gateway_transaksi_id',
        'pelajar_id',
        'tetapan_yuran_pelajar_id',
        'bulan',
        'tahun',
        'keterangan',
        'amaun',
    ];

    protected function casts(): array
    {
        return [
            'amaun' => 'decimal:2',
        ];
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(PaymentGatewayTransaksi::class, 'payment_gateway_transaksi_id');
    }

    public function pelajar(): BelongsTo
    {
        return $this->belongsTo(Pelajar::class, 'pelajar_id');
    }
}
