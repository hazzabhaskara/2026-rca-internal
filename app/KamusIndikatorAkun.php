<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KamusIndikatorAkun extends Model
{
    protected $table = 'kamus_indikator_akuns';

    protected $fillable = [
        'kategori',
        'nama_indikator',
        'keyword_pattern',
        'match_type',
        'sumber_dana_prefix',
        'arah_mutasi',
        'min_amount',
        'max_amount',
        'target_kode_akun',
        'target_nama_akun',
        'alternate_kode_akuns',
        'tingkat_kepastian',
        'penjelasan_aturan',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'alternate_kode_akuns' => 'array',
        'is_active' => 'boolean',
        'min_amount' => 'float',
        'max_amount' => 'float',
        'urutan' => 'integer',
    ];

    public function targetAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'target_kode_akun', 'kode_akun');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('urutan', 'asc');
    }

    /**
     * Memeriksa apakah teks komentar cocok dengan pola kata kunci
     */
    public function matchesText(?string $text): bool
    {
        if (empty($text) || empty($this->keyword_pattern)) {
            return false;
        }

        $upperText = strtoupper(trim($text));
        $pattern = trim($this->keyword_pattern);

        if ($this->match_type === 'regex') {
            return (bool) @preg_match('/' . $pattern . '/i', $text);
        }

        if ($this->match_type === 'exact') {
            return $upperText === strtoupper($pattern);
        }

        // Default: contains (bisa koma separated keywords)
        $keywords = array_filter(array_map('trim', explode(',', $pattern)));
        foreach ($keywords as $kw) {
            if ($kw !== '' && strpos($upperText, strtoupper($kw)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Memeriksa apakah nominal memenuhi batasan min & max
     */
    public function matchesAmount(float $amount): bool
    {
        $amt = abs($amount);
        if ($this->min_amount !== null && $amt < $this->min_amount) {
            return false;
        }
        if ($this->max_amount !== null && $amt > $this->max_amount) {
            return false;
        }
        return true;
    }
}
