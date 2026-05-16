<?php

namespace App\Http\Controllers;

use App\Models\TaxTransaction;
use App\Models\TaxTransactionItem;
use App\Services\FakturPajakExtractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DraftFakturController extends Controller
{
    protected $extractorService;

    public function __construct(FakturPajakExtractorService $extractorService)
    {
        $this->extractorService = $extractorService;
    }

    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;

        $draftsIn = TaxTransaction::with('items')
            ->where('company_id', $company->id ?? 1)
            ->where('tax_type', 'ppn_in')
            ->where('status', 'draft')
            ->latest()
            ->get();
            
        $draftsOut = TaxTransaction::with('items')
            ->where('company_id', $company->id ?? 1)
            ->where('tax_type', 'ppn_out')
            ->where('status', 'draft')
            ->latest()
            ->get();

        return view('taxes.draft_faktur.index', compact('draftsIn', 'draftsOut', 'company'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|mimes:pdf|max:5120', // Max 5MB per file
            'tax_type' => 'required|in:ppn_in,ppn_out'
        ]);

        $company = $request->user()->currentCompany;
        $files = $request->file('files');
        $taxType = $request->tax_type;
        $processedCount = 0;
        $rejectedCount = 0;
        $errors = [];

        if ($files) {
            foreach ($files as $file) {
                // 1. Simpan file sementara
                $tempPath = $file->store('faktur/temp', 'public');
                $absolutePath = storage_path('app/public/' . $tempPath);

                // 2. Ekstrak data dari PDF
                $extractedData = $this->extractorService->extract($absolutePath);

                // Jika ekstraksi gagal, lewati
                if (isset($extractedData['error'])) {
                    $rejectedCount++;
                    $errors[] = "Gagal membaca PDF: " . $file->getClientOriginalName();
                    continue;
                }

                $header = $extractedData['header'];
                
                // --- Validasi NPWP Perusahaan ---
                $companyNpwp = preg_replace('/[^0-9]/', '', $company->npwp ?? '');
                $sellerNpwp = preg_replace('/[^0-9]/', '', $header['seller_tin'] ?? '');
                $buyerNpwp = preg_replace('/[^0-9]/', '', $header['buyer_tin'] ?? '');
                
                if ($taxType === 'ppn_out') {
                    // Faktur Keluaran: Penjual (Seller) harus sama dengan Perusahaan Aktif
                    if ($companyNpwp && $sellerNpwp && $sellerNpwp !== $companyNpwp) {
                        $rejectedCount++;
                        $errors[] = "Ditolak: NPWP Penjual pada file {$file->getClientOriginalName()} tidak cocok dengan NPWP Perusahaan Aktif ({$company->name}).";
                        continue;
                    }
                } elseif ($taxType === 'ppn_in') {
                    // Faktur Masukan: Pembeli (Buyer) harus sama dengan Perusahaan Aktif
                    if ($companyNpwp && $buyerNpwp && $buyerNpwp !== $companyNpwp) {
                        $rejectedCount++;
                        $errors[] = "Ditolak: NPWP Pembeli pada file {$file->getClientOriginalName()} tidak cocok dengan NPWP Perusahaan Aktif ({$company->name}).";
                        continue;
                    }
                }
                
                DB::transaction(function () use ($header, $extractedData, $file, $tempPath, $taxType, $company, &$processedCount) {
                    // Cek duplikasi nomor dokumen jika ada
                    if ($header['document_number']) {
                        $exists = TaxTransaction::where('document_number', $header['document_number'])->exists();
                        if ($exists) return; // Skip if already exists
                    }

                    // Buat header transaksi
                    $taxTransaction = TaxTransaction::create([
                        'company_id' => $company->id ?? 1, 
                        'tax_type' => $taxType,
                        'transaction_date' => $header['tanggal_faktur'] ? date('Y-m-d', strtotime($header['tanggal_faktur'])) : now(),
                        'document_number' => $header['document_number'],
                        'counterpart_name' => $taxType === 'ppn_out' ? ($header['buyer_name'] ?: 'Unknown') : ($header['seller_name'] ?: 'Unknown'),
                        'counterpart_tin' => $taxType === 'ppn_out' ? ($header['buyer_tin'] ?: 'Unknown') : ($header['seller_tin'] ?: 'Unknown'),
                        'tax_base' => $header['dpp'],
                        'tax_amount' => $header['ppn'],
                        'tax_rate' => $header['dpp'] > 0 ? round(($header['ppn'] / $header['dpp']) * 100) : 11,
                        'transaction_value' => $header['dpp'] + $header['ppn'],
                        'status' => 'draft',
                    ]);

                    // Rename & Pindahkan File
                    $safeClientName = Str::slug($taxTransaction->counterpart_name ?: 'unknown');
                    $newFilename = "{$taxTransaction->id}_{$header['document_number']}_{$safeClientName}.pdf";
                    $newPath = "faktur/draft/{$newFilename}";
                    
                    Storage::disk('public')->move($tempPath, $newPath);
                    $taxTransaction->update(['file_path' => $newPath]);

                    // Masukkan detail item
                    if (!empty($extractedData['items'])) {
                        foreach ($extractedData['items'] as $item) {
                            $taxTransaction->items()->create([
                                'item_code' => $item['kode'],
                                'item_name' => $item['nama_barang'],
                                'quantity' => $item['qty'],
                                'unit' => $item['satuan'],
                                'unit_price' => $item['harga_satuan'],
                                'discount' => $item['diskon'],
                                'total_price' => $item['total_harga'],
                            ]);
                        }
                    }

                    $processedCount++;
                });
            }
        }

        $message = "Berhasil mengekstrak $processedCount dokumen.";
        if ($rejectedCount > 0) {
            $message .= " Ditolak: $rejectedCount dokumen karena tidak sesuai NPWP.";
        }

        return response()->json([
            'success' => true, 
            'message' => $message,
            'errors' => $errors
        ]);
    }

    public function verify(Request $request, $id)
    {
        $transaction = TaxTransaction::findOrFail($id);
        
        $request->validate([
            'company_id' => 'required',
            'tax_type' => 'required',
            'status' => 'required', // e.g., 'verified', 'posted'
        ]);

        $transaction->update($request->only([
            'company_id', 'branch_id', 'project_id', 'tax_type', 'transaction_date',
            'document_number', 'counterpart_name', 'counterpart_tin', 'tax_base', 'tax_amount', 'status'
        ]));

        // Pindahkan file dari draft ke final folder jika status berubah
        if ($transaction->status !== 'draft' && Str::contains($transaction->file_path, 'draft/')) {
            $newPath = str_replace('draft/', 'final/', $transaction->file_path);
            if (Storage::disk('public')->exists($transaction->file_path)) {
                Storage::disk('public')->move($transaction->file_path, $newPath);
                $transaction->update(['file_path' => $newPath]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Faktur berhasil diverifikasi.']);
    }

    public function destroy($id)
    {
        $transaction = TaxTransaction::findOrFail($id);
        
        if ($transaction->file_path && Storage::disk('public')->exists($transaction->file_path)) {
            Storage::disk('public')->delete($transaction->file_path);
        }
        
        $transaction->delete();
        
        return response()->json(['success' => true]);
    }
}
