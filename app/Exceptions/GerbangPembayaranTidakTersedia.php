<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Gerbang pembayaran (DOKU) tidak dapat dihubungi atau menolak permintaan
 * cipta pembayaran. Tiada caj dikenakan kepada penjaga.
 */
class GerbangPembayaranTidakTersedia extends RuntimeException {}
