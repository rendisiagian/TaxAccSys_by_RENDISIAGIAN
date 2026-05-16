<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Draft Faktur (Antrean Verifikasi)') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12" x-data="draftFakturManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Messages -->
            <div x-show="message" x-transition class="bg-emerald-50 border-l-4 border-emerald-400 p-4 rounded-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-emerald-700" x-text="message"></p>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-slate-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'ppn_in'" :class="activeTab === 'ppn_in' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Faktur Masukan (VAT In)
                        <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium" :class="activeTab === 'ppn_in' ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-600'">{{ $draftsIn->count() }}</span>
                    </button>
                    <button @click="activeTab = 'ppn_out'" :class="activeTab === 'ppn_out' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Faktur Keluaran (VAT Out)
                        <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium" :class="activeTab === 'ppn_out' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-600'">{{ $draftsOut->count() }}</span>
                    </button>
                </nav>
            </div>

            <!-- Upload Zone -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 transition-colors" :class="activeTab === 'ppn_in' ? 'border-t-4 border-t-indigo-500' : 'border-t-4 border-t-emerald-500'">
                <h3 class="text-lg font-bold text-slate-800 mb-2">Unggah Faktur <span x-text="activeTab === 'ppn_in' ? 'Masukan' : 'Keluaran'"></span> (PDF)</h3>
                
                <!-- Notice Validation -->
                <div x-show="activeTab === 'ppn_out'" class="mb-4 bg-amber-50 border-l-4 border-amber-400 p-3 rounded-md">
                    <p class="text-xs text-amber-800 font-medium">⚠️ Validasi Otomatis: Sistem akan menolak file PDF Faktur Keluaran jika NPWP Penjual tidak sesuai dengan NPWP Perusahaan Aktif ({{ $company->name ?? 'Belum Dipilih' }}).</p>
                </div>

                <p class="text-sm text-slate-500 mb-6">Sistem akan mengekstrak data dari dokumen PDF secara otomatis. Anda dapat mengunggah banyak file sekaligus.</p>
                
                <div 
                    class="border-2 border-dashed rounded-xl p-10 text-center transition-colors cursor-pointer"
                    :class="activeTab === 'ppn_in' ? 'border-indigo-300 hover:bg-indigo-50' : 'border-emerald-300 hover:bg-emerald-50'"
                    @dragover.prevent="$el.classList.add(activeTab === 'ppn_in' ? 'bg-indigo-50' : 'bg-emerald-50')"
                    @dragleave.prevent="$el.classList.remove('bg-indigo-50', 'bg-emerald-50')"
                    @drop.prevent="handleDrop($event); $el.classList.remove('bg-indigo-50', 'bg-emerald-50')"
                    @click="$refs.fileInput.click()"
                >
                    <svg class="mx-auto h-12 w-12 mb-4" :class="activeTab === 'ppn_in' ? 'text-indigo-400' : 'text-emerald-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-slate-700 font-medium text-lg">Klik atau seret & lepas file PDF <span x-text="activeTab === 'ppn_in' ? 'Pajak Masukan' : 'Pajak Keluaran'"></span> di sini</p>
                    <p class="text-slate-500 text-sm mt-2">Maksimal 5MB per file</p>
                    <input type="file" x-ref="fileInput" multiple accept="application/pdf" class="hidden" @change="handleFiles($event.target.files)">
                </div>

                <div x-show="isUploading" class="mt-4 flex items-center font-medium" :class="activeTab === 'ppn_in' ? 'text-indigo-600' : 'text-emerald-600'">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sedang mengekstrak dokumen... Mohon tunggu.
                </div>
            </div>

            <!-- Table Drafts IN -->
            <div x-show="activeTab === 'ppn_in'" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-indigo-50/50">
                    <h3 class="text-lg font-bold text-slate-800">Antrean Verifikasi PPN Masukan</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 font-medium border-b border-slate-200">
                            <tr>
                                <th class="py-4 px-6">Tgl Faktur</th>
                                <th class="py-4 px-6">No. Dokumen</th>
                                <th class="py-4 px-6">Nama Penjual (Seller)</th>
                                <th class="py-4 px-6 text-right">DPP (Rp)</th>
                                <th class="py-4 px-6 text-right">PPN (Rp)</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($draftsIn as $draft)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-4 px-6">{{ \Carbon\Carbon::parse($draft->transaction_date)->format('d M Y') }}</td>
                                <td class="py-4 px-6 font-medium text-slate-800">{{ $draft->document_number ?: '-' }}</td>
                                <td class="py-4 px-6">{{ $draft->counterpart_name ?: '-' }}</td>
                                <td class="py-4 px-6 text-right">{{ number_format($draft->tax_base, 2, ',', '.') }}</td>
                                <td class="py-4 px-6 text-right font-medium text-indigo-600">{{ number_format($draft->tax_amount, 2, ',', '.') }}</td>
                                <td class="py-4 px-6 text-center">
                                    <button @click="openVerifyModal({{ $draft->toJson() }})" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 font-medium text-xs transition-colors">
                                        Review
                                    </button>
                                    <button @click="deleteDraft({{ $draft->id }})" class="ml-2 inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 font-medium text-xs transition-colors">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="py-12 px-6 text-center text-slate-500">Belum ada faktur masukan dalam antrean verifikasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table Drafts OUT -->
            <div x-show="activeTab === 'ppn_out'" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" style="display: none;">
                <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-emerald-50/50">
                    <h3 class="text-lg font-bold text-slate-800">Antrean Verifikasi PPN Keluaran</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 font-medium border-b border-slate-200">
                            <tr>
                                <th class="py-4 px-6">Tgl Faktur</th>
                                <th class="py-4 px-6">No. Dokumen</th>
                                <th class="py-4 px-6">Nama Pembeli (Buyer)</th>
                                <th class="py-4 px-6 text-right">DPP (Rp)</th>
                                <th class="py-4 px-6 text-right">PPN (Rp)</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($draftsOut as $draft)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-4 px-6">{{ \Carbon\Carbon::parse($draft->transaction_date)->format('d M Y') }}</td>
                                <td class="py-4 px-6 font-medium text-slate-800">{{ $draft->document_number ?: '-' }}</td>
                                <td class="py-4 px-6">{{ $draft->counterpart_name ?: '-' }}</td>
                                <td class="py-4 px-6 text-right">{{ number_format($draft->tax_base, 2, ',', '.') }}</td>
                                <td class="py-4 px-6 text-right font-medium text-emerald-600">{{ number_format($draft->tax_amount, 2, ',', '.') }}</td>
                                <td class="py-4 px-6 text-center">
                                    <button @click="openVerifyModal({{ $draft->toJson() }})" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 font-medium text-xs transition-colors">
                                        Review
                                    </button>
                                    <button @click="deleteDraft({{ $draft->id }})" class="ml-2 inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 font-medium text-xs transition-colors">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="py-12 px-6 text-center text-slate-500">Belum ada faktur keluaran dalam antrean verifikasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Verify -->
    <template x-teleport="body">
        <div x-data="verifyModal()" x-show="isOpen" @open-verify-modal.window="open($event.detail)" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div x-show="isOpen" x-transition.opacity class="fixed inset-0 transition-opacity bg-slate-900/75 backdrop-blur-sm" @click="close()"></div>

                <div x-show="isOpen" x-transition class="relative inline-block w-full max-w-4xl p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl">
                    <div class="flex justify-between items-center border-b border-slate-200 pb-4 mb-6">
                        <h3 class="text-xl font-bold text-slate-800">Review & Verifikasi Faktur</h3>
                        <button @click="close()" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitVerification">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Ekstraksi Readonly -->
                            <div class="space-y-4">
                                <h4 class="font-semibold text-slate-700 border-b pb-2">A. Data Ekstraksi (Read-only)</h4>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500">Nomor Dokumen</label>
                                    <div class="mt-1 p-2 bg-slate-50 rounded-lg text-sm text-slate-800 font-mono" x-text="draft.document_number || '-'"></div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500">Nama Lawan Transaksi</label>
                                    <div class="mt-1 p-2 bg-slate-50 rounded-lg text-sm text-slate-800 font-medium" x-text="draft.counterpart_name || '-'"></div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500">NPWP Lawan Transaksi</label>
                                    <div class="mt-1 p-2 bg-slate-50 rounded-lg text-sm text-slate-800" x-text="draft.counterpart_tin || '-'"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500">DPP (Rp)</label>
                                        <div class="mt-1 p-2 bg-slate-50 rounded-lg text-sm text-slate-800 font-bold" x-text="formatRupiah(draft.tax_base)"></div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500">PPN (Rp)</label>
                                        <div class="mt-1 p-2 bg-indigo-50 rounded-lg text-sm text-indigo-700 font-bold" x-text="formatRupiah(draft.tax_amount)"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Relasi -->
                            <div class="space-y-4">
                                <h4 class="font-semibold text-slate-700 border-b pb-2">B. Lengkapi Data Sistem</h4>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Jenis Pajak <span class="text-red-500">*</span></label>
                                    <select x-model="formData.tax_type" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm sm:text-sm bg-slate-100" readonly disabled>
                                        <option value="ppn_in">PPN Masukan (VAT In)</option>
                                        <option value="ppn_out">PPN Keluaran (VAT Out)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Perusahaan <span class="text-red-500">*</span></label>
                                    <select x-model="formData.company_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm sm:text-sm bg-slate-100" readonly disabled>
                                        <option value="{{ $company->id ?? 1 }}">{{ $company->name ?? 'PT Utama (Head Office)' }}</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Status Tindakan <span class="text-red-500">*</span></label>
                                    <select x-model="formData.status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm text-emerald-700 font-medium bg-emerald-50" required>
                                        <option value="verified">Verified (Siap Jurnal)</option>
                                        <option value="posted">Posted (Langsung Jurnal)</option>
                                        <option value="draft">Simpan sebagai Draft</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Rincian Item jika ada -->
                        <div class="mb-8" x-show="draft.items && draft.items.length > 0">
                            <h4 class="font-semibold text-slate-700 border-b pb-2 mb-4">C. Rincian Item Barang/Jasa</h4>
                            <div class="overflow-hidden rounded-lg border border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Nama Barang</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500">Harga (Rp)</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-slate-500">Qty</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500">Total (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        <template x-for="item in draft.items" :key="item.id">
                                            <tr>
                                                <td class="px-4 py-2 text-slate-800" x-text="item.item_name"></td>
                                                <td class="px-4 py-2 text-right text-slate-600" x-text="formatRupiah(item.unit_price)"></td>
                                                <td class="px-4 py-2 text-center text-slate-600" x-text="item.quantity + ' ' + (item.unit || '')"></td>
                                                <td class="px-4 py-2 text-right font-medium text-slate-800" x-text="formatRupiah(item.total_price)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-5">
                            <button type="button" @click="close()" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-medium transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="px-6 py-2 text-white rounded-lg font-medium shadow-sm transition-colors flex items-center" :class="formData.tax_type === 'ppn_in' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-emerald-600 hover:bg-emerald-700'" :disabled="isSubmitting">
                                <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Simpan Verifikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    @push('scripts')
    <script>
        function draftFakturManager() {
            return {
                activeTab: 'ppn_in',
                isUploading: false,
                message: '',
                handleDrop(e) {
                    if (e.dataTransfer.files.length > 0) {
                        this.handleFiles(e.dataTransfer.files);
                    }
                },
                async handleFiles(files) {
                    if (files.length === 0) return;
                    
                    this.isUploading = true;
                    this.message = '';
                    
                    let formData = new FormData();
                    for(let i=0; i<files.length; i++) {
                        formData.append('files[]', files[i]);
                    }
                    formData.append('tax_type', this.activeTab); // Kirim jenis tab aktif ke backend

                    try {
                        const response = await fetch('{{ route("taxes.draft_faktur.upload") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });
                        
                        const result = await response.json();
                        if(result.success) {
                            if(result.errors && result.errors.length > 0) {
                                alert(result.message + "\n\n" + result.errors.join("\n"));
                            } else {
                                this.message = result.message;
                            }
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            alert("Gagal mengunggah file.");
                        }
                    } catch (error) {
                        alert('Terjadi kesalahan saat mengunggah.');
                    } finally {
                        this.isUploading = false;
                    }
                },
                openVerifyModal(draft) {
                    window.dispatchEvent(new CustomEvent('open-verify-modal', { detail: draft }));
                },
                async deleteDraft(id) {
                    if(!confirm('Yakin ingin menghapus draf ini beserta file fisiknya?')) return;
                    
                    try {
                        const response = await fetch(`/taxes/draft-faktur/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        if(response.ok) window.location.reload();
                    } catch(e) {
                        alert('Gagal menghapus');
                    }
                }
            }
        }

        function verifyModal() {
            return {
                isOpen: false,
                isSubmitting: false,
                draft: {},
                formData: {
                    company_id: '{{ $company->id ?? 1 }}',
                    tax_type: 'ppn_in',
                    status: 'verified'
                },
                open(draftData) {
                    this.draft = draftData;
                    this.formData.tax_type = draftData.tax_type || 'ppn_in';
                    this.isOpen = true;
                },
                close() {
                    this.isOpen = false;
                    this.draft = {};
                },
                formatRupiah(num) {
                    if(!num) return '0';
                    return parseFloat(num).toLocaleString('id-ID');
                },
                async submitVerification() {
                    this.isSubmitting = true;
                    try {
                        const response = await fetch(`/taxes/draft-faktur/${this.draft.id}/verify`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.formData)
                        });
                        
                        const result = await response.json();
                        if(result.success) {
                            this.close();
                            window.location.reload();
                        }
                    } catch(e) {
                        alert('Gagal memverifikasi');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
