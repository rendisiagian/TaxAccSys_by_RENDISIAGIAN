<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) return;

        $accounts = [
            // ── 1xxx ASET ──
            ['1000', 'Aset', 'asset', 'debit', true, 1, null],
              ['1100', 'Aset Lancar', 'asset', 'debit', true, 2, '1000'],
                ['1110', 'Kas & Bank', 'asset', 'debit', true, 3, '1100'],
                  ['1111', 'Kas', 'asset', 'debit', false, 4, '1110'],
                  ['1112', 'Bank', 'asset', 'debit', false, 4, '1110'],
                ['1120', 'Piutang Usaha', 'asset', 'debit', false, 3, '1100'],
                ['1130', 'Piutang Lain-lain', 'asset', 'debit', false, 3, '1100'],
                ['1140', 'Persediaan', 'asset', 'debit', false, 3, '1100'],
                ['1150', 'Pajak Dibayar Dimuka', 'asset', 'debit', true, 3, '1100'],
                  ['1151', 'PPN Masukan', 'asset', 'debit', false, 4, '1150'],
                  ['1152', 'PPh 22 Dibayar Dimuka', 'asset', 'debit', false, 4, '1150'],
                  ['1153', 'PPh 23 Dibayar Dimuka', 'asset', 'debit', false, 4, '1150'],
                  ['1154', 'PPh 25 Dibayar Dimuka', 'asset', 'debit', false, 4, '1150'],
                  ['1155', 'Piutang Restitusi Pajak', 'asset', 'debit', false, 4, '1150'],
                ['1160', 'Biaya Dibayar Dimuka', 'asset', 'debit', false, 3, '1100'],
              ['1200', 'Aset Tidak Lancar', 'asset', 'debit', true, 2, '1000'],
                ['1210', 'Aset Tetap', 'asset', 'debit', false, 3, '1200'],
                ['1215', 'Akumulasi Penyusutan', 'asset', 'credit', false, 3, '1200'],
                ['1220', 'Aset Tak Berwujud', 'asset', 'debit', false, 3, '1200'],
                ['1225', 'Akumulasi Amortisasi', 'asset', 'credit', false, 3, '1200'],

            // ── 1xxx ASET PAJAK TANGGUHAN ──
                ['1230', 'Aset Pajak Tangguhan (DTA)', 'asset', 'debit', false, 3, '1200'],

            // ── 2xxx LIABILITAS ──
            ['2000', 'Liabilitas', 'liability', 'credit', true, 1, null],
              ['2100', 'Liabilitas Jangka Pendek', 'liability', 'credit', true, 2, '2000'],
                ['2110', 'Utang Usaha', 'liability', 'credit', false, 3, '2100'],
                ['2120', 'Utang Pajak', 'liability', 'credit', true, 3, '2100'],
                  ['2121', 'Utang PPh 21', 'liability', 'credit', false, 4, '2120'],
                  ['2122', 'Utang PPh 23/26', 'liability', 'credit', false, 4, '2120'],
                  ['2123', 'Utang PPh 4(2)', 'liability', 'credit', false, 4, '2120'],
                  ['2124', 'Utang PPh 25', 'liability', 'credit', false, 4, '2120'],
                  ['2125', 'Utang PPh Badan', 'liability', 'credit', false, 4, '2120'],
                  ['2126', 'PPN Keluaran', 'liability', 'credit', false, 4, '2120'],
                  ['2127', 'Utang STP', 'liability', 'credit', false, 4, '2120'],
                  ['2128', 'Utang SKPKB', 'liability', 'credit', false, 4, '2120'],
                ['2130', 'Utang Lain-lain', 'liability', 'credit', false, 3, '2100'],
                ['2140', 'Utang Gaji & Bonus', 'liability', 'credit', false, 3, '2100'],
              ['2200', 'Liabilitas Jangka Panjang', 'liability', 'credit', true, 2, '2000'],
                ['2210', 'Utang Bank Jangka Panjang', 'liability', 'credit', false, 3, '2200'],

            // ── 2xxx LIABILITAS PAJAK TANGGUHAN ──
                ['2220', 'Liabilitas Pajak Tangguhan (DTL)', 'liability', 'credit', false, 3, '2200'],

            // ── 3xxx EKUITAS ──
            ['3000', 'Ekuitas', 'equity', 'credit', true, 1, null],
              ['3100', 'Modal Disetor', 'equity', 'credit', false, 2, '3000'],
              ['3200', 'Laba Ditahan', 'equity', 'credit', false, 2, '3000'],
              ['3300', 'Laba Tahun Berjalan', 'equity', 'credit', false, 2, '3000'],

            // ── 4xxx PENDAPATAN ──
            ['4000', 'Pendapatan', 'revenue', 'credit', true, 1, null],
              ['4100', 'Pendapatan Usaha', 'revenue', 'credit', false, 2, '4000'],
              ['4200', 'Pendapatan Jasa Konstruksi', 'revenue', 'credit', false, 2, '4000'],
              ['4300', 'Diskon Penjualan', 'revenue', 'debit', false, 2, '4000'],
              ['4400', 'Retur Penjualan', 'revenue', 'debit', false, 2, '4000'],

            // ── 5xxx HPP ──
            ['5000', 'Harga Pokok', 'cogs', 'debit', true, 1, null],
              ['5100', 'Harga Pokok Penjualan', 'cogs', 'debit', false, 2, '5000'],
              ['5200', 'Biaya Langsung Proyek', 'cogs', 'debit', false, 2, '5000'],

            // ── 6xxx BEBAN OPERASIONAL ──
            ['6000', 'Beban Operasional', 'expense', 'debit', true, 1, null],
              ['6100', 'Beban Gaji & Tunjangan', 'expense', 'debit', false, 2, '6000'],
              ['6200', 'Beban Sewa', 'expense', 'debit', false, 2, '6000'],
              ['6300', 'Beban Penyusutan', 'expense', 'debit', false, 2, '6000'],
              ['6400', 'Beban Utilitas', 'expense', 'debit', false, 2, '6000'],
              ['6500', 'Beban Administrasi & Umum', 'expense', 'debit', false, 2, '6000'],
              ['6600', 'Beban Overhead Proyek', 'expense', 'debit', false, 2, '6000'],

            // ── 7xxx PENDAPATAN/BEBAN LAIN ──
            ['7000', 'Pendapatan & Beban Lain', 'other_income', 'credit', true, 1, null],
              ['7100', 'Pendapatan Bunga', 'other_income', 'credit', false, 2, '7000'],
              ['7200', 'Beban Bunga', 'other_income', 'debit', false, 2, '7000'],
              ['7300', 'Laba/Rugi Selisih Kurs', 'other_income', 'credit', false, 2, '7000'],
              ['7400', 'Pendapatan Lain-lain', 'other_income', 'credit', false, 2, '7000'],
              ['7500', 'Beban Lain-lain', 'other_income', 'debit', false, 2, '7000'],

            // ── 8xxx AKUN PAJAK ──
            ['8000', 'Akun Pajak Penghasilan', 'tax', 'debit', true, 1, null],
              ['8100', 'Beban Pajak Kini', 'tax', 'debit', false, 2, '8000'],
              ['8200', 'Beban Pajak Tangguhan', 'tax', 'debit', false, 2, '8000'],
              ['8210', 'Manfaat Pajak Tangguhan', 'tax', 'credit', false, 2, '8000'],
              ['8300', 'Beban Denda/Bunga Pajak', 'tax', 'debit', false, 2, '8000'],
              ['8400', 'Beban Pajak Kurang Bayar', 'tax', 'debit', false, 2, '8000'],
        ];

        $codeToId = [];

        foreach ($accounts as $i => $acct) {
            [$code, $name, $type, $balance, $isHeader, $level, $parentCode] = $acct;

            $record = ChartOfAccount::create([
                'company_id'     => $company->id,
                'parent_id'      => $parentCode ? ($codeToId[$parentCode] ?? null) : null,
                'account_code'   => $code,
                'account_name'   => $name,
                'account_type'   => $type,
                'normal_balance' => $balance,
                'is_header'      => $isHeader,
                'is_active'      => true,
                'is_system'      => true,
                'level'          => $level,
                'sort_order'     => $i,
            ]);

            $codeToId[$code] = $record->id;
        }
    }
}
