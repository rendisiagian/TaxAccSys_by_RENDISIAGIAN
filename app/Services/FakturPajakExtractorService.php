<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;

class FakturPajakExtractorService
{
    protected $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Parse text from PDF file and extract Faktur Pajak data.
     * 
     * @param string $pdfFilePath Absolute path to PDF file
     * @return array Extracted data
     */
    public function extract($pdfFilePath)
    {
        try {
            $pdf = $this->parser->parseFile($pdfFilePath);
            $text = $pdf->getText();
            
            // Normalize spaces and newlines
            $text = preg_replace('/\s+/', ' ', $text);

            return [
                'header' => $this->extractHeader($text),
                'items' => $this->extractItems($text),
                'raw_text' => $text,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to parse PDF: " . $e->getMessage());
            return [
                'header' => null,
                'items' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    protected function extractHeader($text)
    {
        $data = [
            'document_number' => null,
            'tanggal_faktur' => null,
            'seller_name' => null,
            'seller_tin' => null,
            'buyer_name' => null,
            'buyer_tin' => null,
            'dpp' => 0,
            'ppn' => 0,
        ];

        // 1. Nomor Faktur
        if (preg_match('/Kode dan Nomor Seri Faktur Pajak\s*:\s*([\d\.\-]+)/i', $text, $matches)) {
            $data['document_number'] = trim($matches[1]);
        }

        // 2. Penjual (PKP)
        if (preg_match('/Pengusaha Kena Pajak.*?Nama\s*:\s*(.*?)Alamat/i', $text, $matches)) {
            $data['seller_name'] = trim($matches[1]);
        }
        if (preg_match('/Pengusaha Kena Pajak.*?NPWP\s*:\s*([\d\.\-]+)/i', $text, $matches)) {
            $data['seller_tin'] = trim($matches[1]);
        }

        // 3. Pembeli
        if (preg_match('/Pembeli Barang Kena Pajak.*?Nama\s*:\s*(.*?)Alamat/i', $text, $matches)) {
            $data['buyer_name'] = trim($matches[1]);
        }
        if (preg_match('/Pembeli Barang Kena Pajak.*?NPWP\s*(?:\/\s*NIK\s*)?:\s*([\d\.\-]+)/i', $text, $matches)) {
            $data['buyer_tin'] = trim($matches[1]);
        }

        // 4. DPP & PPN
        if (preg_match('/Dasar Pengenaan Pajak\s*([\d\.,]+)/i', $text, $matches)) {
            $data['dpp'] = $this->cleanNumber($matches[1]);
        }
        if (preg_match('/Total PPN\s*([\d\.,]+)/i', $text, $matches) || preg_match('/PPN\s*=\s*\d+%\s*x\s*Dasar Pengenaan Pajak\s*([\d\.,]+)/i', $text, $matches)) {
            $data['ppn'] = $this->cleanNumber($matches[1]);
        }

        // Tanggal
        if (preg_match('/Jakarta,\s*(\d{1,2}\s+[A-Za-z]+\s+\d{4})/i', $text, $matches)) { // Example, depends on city
             $data['tanggal_faktur'] = trim($matches[1]);
        }

        return $data;
    }

    protected function extractItems($text)
    {
        $items = [];

        // Pola Modern 2025 (Diadopsi dari app.js)
        // 1 AB1234 Jasa Manpower Rp 1.000.000 x 1 Unit ...
        $regexModern = '/(?:^|\s)(\d+)\s+([A-Z0-9]{6})\s+(.*?)\s+Rp\s*([\d\.,]+)\s*x\s*([\d\.,]+)\s*([A-Za-z\s]+?)\s+Potongan Harga\s*=\s*Rp\s*([\d\.,]+)\s+PPnBM[^=]*=\s*Rp\s*([\d\.,]+)\s+([\d\.,]+)/';
        
        if (preg_match_all($regexModern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $items[] = [
                    'no' => $match[1],
                    'kode' => $match[2],
                    'nama_barang' => trim($match[3]),
                    'harga_satuan' => $this->cleanNumber($match[4]),
                    'qty' => $this->cleanNumber($match[5]),
                    'satuan' => trim($match[6]),
                    'diskon' => $this->cleanNumber($match[7]),
                    'total_harga' => $this->cleanNumber($match[9]),
                ];
            }
            return $items;
        }

        // Jika tidak match, gunakan pola fallback sederhana (Pola Legacy)
        $fallbackRe = '/Rp\s*([\d\.,]+)\s*x\s*([\d\.,]+)/';
        if (preg_match_all($fallbackRe, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            // Logika fallback sederhana
            // Dalam implementasi nyata, ini bisa lebih kompleks sesuai app.js
        }

        return $items;
    }

    protected function cleanNumber($numberStr)
    {
        return (float) str_replace(['.', ','], ['', '.'], $numberStr);
    }
}
