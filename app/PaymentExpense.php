<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\chartOfAccount;
use App\JournalEntry;

class PaymentExpense extends Model
{
    protected $fillable = [
        'user_id',
        'departemen_id',
        'voucher_number',
        'from_account_id',
        'source',
        'category_source',
        'date',
        'vendor_id',
        'notes',
        'attachment',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function departemen()
    {
        return $this->belongsTo(Departement::class, 'departemen_id');
    }

    public function Vendor()
    {
        return $this->belongsTo(Vendors::class, 'vendor_id');
    }

    public function Account()
    {
        return $this->belongsTo(chartOfAccount::class, 'from_account_id');
    }

    public function details()
    {
        return $this->hasMany(PaymentExpenseDetail::class, 'payment_expense_id');
    }

    /**
     * Hitung Total Nominal Kotor Detail
     */
    public function getSubtotalAttribute()
    {
        return $this->details->sum('amount');
    }

    /**
     * Hitung Total Pajak
     */
    public function getTotalTaxAttribute()
    {
        return $this->details->sum(function ($item) {
            $rate = $item->salesTaxes->rate ?? 0;
            return $item->amount * ($rate / 100);
        });
    }

    /**
     * Hitung Grand Total (Subtotal + Tax)
     */
    public function getGrandTotalAttribute()
    {
        return $this->details->sum(function ($item) {
            $rate = $item->salesTaxes->rate ?? 0;
            return $item->amount + ($item->amount * ($rate / 100));
        });
    }

    /**
     * URL Lampiran Dokumen
     */
    public function getAttachmentUrlAttribute()
    {
        if (!$this->attachment) {
            return null;
        }

        if (filter_var($this->attachment, FILTER_VALIDATE_URL)) {
            return $this->attachment;
        }

        return asset($this->attachment);
    }

    /**
     * Helper Terbilang Bahasa Indonesia
     */
    public static function terbilang($angka)
    {
        $angka = abs((float) $angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $terbilang = '';

        if ($angka < 12) {
            $terbilang = ' ' . $baca[(int)$angka];
        } elseif ($angka < 20) {
            $terbilang = self::terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            $terbilang = self::terbilang(floor($angka / 10)) . ' Puluh' . self::terbilang($angka % 10);
        } elseif ($angka < 200) {
            $terbilang = ' Seratus' . self::terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $terbilang = self::terbilang(floor($angka / 100)) . ' Ratus' . self::terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $terbilang = ' Seribu' . self::terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $terbilang = self::terbilang(floor($angka / 1000)) . ' Ribu' . self::terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $terbilang = self::terbilang(floor($angka / 1000000)) . ' Juta' . self::terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            $terbilang = self::terbilang(floor($angka / 1000000000)) . ' Miliar' . self::terbilang(fmod($angka, 1000000000));
        } else {
            $terbilang = self::terbilang(floor($angka / 1000000000000)) . ' Triliun' . self::terbilang(fmod($angka, 1000000000000));
        }

        return trim($terbilang);
    }

    /**
     * Terbilang Rupiah
     */
    public function getTerbilangAttribute()
    {
        $grandTotal = $this->grand_total;
        if ($grandTotal <= 0) {
            return 'Nol Rupiah';
        }
        return self::terbilang($grandTotal) . ' Rupiah';
    }

    /**
     * Peta Prefix Nomor Voucher Standar berdasarkan Akun Kas/Bank (chartOfAccount)
     */
     public const ACCOUNT_PREFIX_MAP = [
        // KAS
        '1101000' => 'KAS',     // KAS
        '1101001' => 'KASBDG',  // KAS OPERASIONAL BANDUNG
        '1101002' => 'KASOMS',  // KAS OMSET
        '1101003' => 'KASFIR',  // KAS FIRMAN
        '1101004' => 'KASRIS',  // KAS RISKI
        '1101005' => 'KASIMN',  // KAS PESONA - IMAN
        '1101006' => 'KASGWT',  // KAS PESONA - GOWET
        '1101007' => 'KASTMP',  // KAS TAMPUNGAN OMSET
        '1101008' => 'KASAML',  // KAS AMEL
        '1101009' => 'KASRHM',  // KAS RAHMAN
        '1101010' => 'KASACL',  // KAS OPERASIONAL ANCOL
        '1101011' => 'KASSRT',  // KAS SETOR RETAIL
        '1101012' => 'KASQPS',  // KAS QRIS PESONA

        // BANK
        '1102000' => 'BANK',    // BANK
        '1102001' => 'MDR6215', // MANDIRI 1310062136215_OPERASIONAL
        '1102002' => 'MDR0012', // MANDIRI 1310000650012_QRIS ODS
        '1102003' => 'MDR6214', // MANDIRI 1310062146214_QRIS AWA
        '1102004' => 'MDR0015', // MANDIRI 1310000620015_QRIS JBL
        '1102005' => 'MDR6213', // MANDIRI 1310062156213_QRIS DUFAN
        '1102006' => 'MDR6646', // MANDIRI AMEL 1200014486646_TRANS QR
        '1102007' => 'BNI7884', // BNI AMEL 1907207884_KAS OPS ANCOL
        '1102008' => 'MDR9999', // MANDIRI 1320571799999_PESONA
        '1102009' => 'MDR9999', // MANDIRI 1320077379999_PESONA GIRO
        '1102010' => 'BCA5366', // BCA 2330635366_YERRY
        '1102011' => 'BRI7562', // BRI 34601001707562_AMEL
        '1102012' => 'BCAEVI',  // BCA EVI
        '1102013' => 'JAGO',    // BANK JAGO RAHMAN
        '1102014' => 'OCBC4186',// OCBC 693818420186_AMEL
        '1102015' => 'BRI7509', // BRI 034601053377509_AMEL
        '1102016' => 'BNI0365', // BNI 2010790365_OPERASIONAL
        '1102017' => 'BNI6625', // BNI 2009826625_TAMPUNGAN QRIS DUFAN
        '1102018' => 'BNI9741', // BNI 2020529741_TAMPUNGAN QRIS ODS JBL AWA
        '1102019' => 'BNI9555', // BNI 2015889555_NEWSPAPER BOOTH
        '1102020' => 'BTN',     // BTN REDI
    ];

    /**
     * Dapatkan kode prefix berdasarkan akun kas/bank
     */
    public static function getAccountPrefix($account)
    {
        if (is_numeric($account)) {
            $account = chartOfAccount::find($account);
        }

        if (!$account) {
            return 'KAS';
        }

        if (isset(self::ACCOUNT_PREFIX_MAP[$account->kode_akun])) {
            return self::ACCOUNT_PREFIX_MAP[$account->kode_akun];
        }

        $nama = strtoupper(trim($account->nama_akun));

        // Deteksi rekening bank
        $bankCodes = ['BCA', 'BNI', 'BRI', 'MANDIRI', 'MDR', 'BTN', 'OCBC', 'CIMB', 'DANAMON', 'PERMATA', 'BSI', 'JAGO'];
        foreach ($bankCodes as $bank) {
            if (strpos($nama, $bank) !== false) {
                $code = ($bank === 'MANDIRI') ? 'MDR' : $bank;
                if (preg_match('/(\d{4,})/', $nama, $m)) {
                    return $code . substr($m[1], -4);
                }
                $remainder = trim(str_replace($bank, '', $nama));
                $remainder = preg_replace('/[^A-Z]/', '', $remainder);
                if (!empty($remainder)) {
                    return $code . substr($remainder, 0, 4);
                }
                return $code;
            }
        }

        // Deteksi kas
        if (strpos($nama, 'KAS') !== false) {
            $remainder = trim(str_replace('KAS', '', $nama));
            $remainder = preg_replace('/[^A-Z]/', '', $remainder);
            if (!empty($remainder)) {
                return 'KAS' . substr($remainder, 0, 3);
            }
            return 'KAS';
        }

        $clean = preg_replace('/[^A-Z0-9]/', '', $nama);
        return !empty($clean) ? substr($clean, 0, 6) : 'KAS';
    }

    /**
     * Generator Nomor Voucher Pengeluaran Kas/Bank ({PREFIX}-{YYMMDD}-{SEQ_001})
     * Disesuaikan dengan Sumber Akun / Kas Pengeluaran (Bukan sekadar BKK statis)
     */
    public static function generateVoucherNumber($accountId = null, $date = null)
    {
        $dateObj = $date ? Carbon::parse($date) : Carbon::now();
        $dateYmd = $dateObj->format('ymd');

        $prefix = self::getAccountPrefix($accountId);
        $searchPrefix = "{$prefix}-{$dateYmd}-";

        // 1. Cek urutan terakhir di PaymentExpense
        $lastExpense = self::where('voucher_number', 'LIKE', $searchPrefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $seqExpense = 0;
        if ($lastExpense && preg_match('/-(\d+)$/', $lastExpense->voucher_number, $matches)) {
            $seqExpense = intval($matches[1]);
        }

        // 2. Cek urutan terakhir di JournalEntry (menghindari duplikasi nomor jurnal)
        $lastJournal = JournalEntry::where('source', 'LIKE', $searchPrefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $seqJournal = 0;
        if ($lastJournal && preg_match('/-(\d+)$/', $lastJournal->source, $matches)) {
            $seqJournal = intval($matches[1]);
        }

        $nextNumber = max($seqExpense, $seqJournal) + 1;

        return $searchPrefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
