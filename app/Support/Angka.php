<?php

namespace App\Support;

final class Angka
{
    /** Angka dengan koma desimal dan tanpa nol di belakang yang tidak perlu. Null tampil sebagai "-". */
    public static function nilai(?float $angka, int $desimal = 2): string
    {
        if ($angka === null) {
            return '-';
        }

        $teks = number_format($angka, $desimal, ',', '');

        return $desimal > 0 ? rtrim(rtrim($teks, '0'), ',') : $teks;
    }
}