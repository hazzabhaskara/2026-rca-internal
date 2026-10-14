@extends('layouts.app')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-full mx-auto space-y-5">

    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <div class="flex items-center gap-2 font-bold mb-1">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>Terdapat kesalahan pengisian:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="payment-expense-form" method="POST" action="{{ route('payment_expense.update', $payment_expense->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- SAGE 50 TOP ACTION RIBBON TOOLBAR -->
        <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 overflow-hidden text-white">
            <div class="h-1.5 w-full bg-gradient-to-r from-amber-500 via-emerald-400 to-indigo-500"></div>

            <div class="p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-400 flex items-center justify-center font-bold text-lg shadow-inner">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-bold tracking-tight text-white">Edit Payment Expense</h2>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-800 text-amber-400 border border-slate-700">
                                {{ $payment_expense->voucher_number ?: 'EXP#' . $payment_expense->id }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-300">Perbarui rincian pengeluaran kas divisi & sinkronisasi ulang jurnal akuntansi.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <div class="inline-flex rounded-xl bg-slate-800 p-1 border border-slate-700 text-xs font-semibold mr-2">
                        <button type="button" id="tab-btn-form" onclick="switchSageTab('form')"
                            class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white shadow-xs transition-all">
                            <i class="fa-solid fa-receipt mr-1"></i> Form Pengeluaran
                        </button>
                        <button type="button" id="tab-btn-journal" onclick="switchSageTab('journal')"
                            class="px-3 py-1.5 rounded-lg text-slate-300 hover:text-white transition-all">
                            <i class="fa-solid fa-book-journal-whills mr-1"></i> Preview Jurnal
                        </button>
                    </div>

                    <!-- Tombol Cetak Bon -->
                    <a href="{{ route('payment_expense.print_voucher', $payment_expense->id) }}" target="_blank"
                        class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-all">
                        <i class="fa-solid fa-print mr-1 text-emerald-400"></i> Cetak Bon
                    </a>

                    <a href="{{ route('payment_expense.index') }}"
                        class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700 transition-all">
                        <i class="fa-solid fa-xmark mr-1"></i> Batal
                    </a>

                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-md shadow-emerald-500/20 active:scale-95 transition-all">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Perbarui & Simpan</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- SAGE 50 VOUCHER CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            <div id="sage-tab-form" class="p-5 sm:p-6 space-y-6">

                <!-- TWO-COLUMN HEADER SLIP -->
                <div class="bg-slate-50/80 rounded-2xl p-5 border border-slate-200/80 grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- LEFT COLUMN -->
                    <div class="lg:col-span-7 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Divisi / Departemen <span class="text-rose-500">*</span>
                                </label>
                                <select name="departemen_id" id="departemen_id"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 bg-white font-medium focus:ring-2 focus:ring-emerald-500" required>
                                    <option value="">-- Pilih Divisi Terkait --</option>
                                    @foreach ($departements as $dept)
                                        <option value="{{ $dept->id }}" {{ old('departemen_id', $payment_expense->departemen_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->deskripsi }} ({{ $dept->kode }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Kategori Sumber Kas <span class="text-rose-500">*</span>
                                </label>
                                <select name="category_source" id="category_source"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 bg-white font-medium focus:ring-2 focus:ring-emerald-500" required>
                                    @foreach ($categorySources as $cat)
                                        <option value="{{ $cat }}" {{ old('category_source', $payment_expense->category_source ?: 'Kas Kecil (Petty Cash)') == $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Keterangan Source / Ref
                                </label>
                                <input type="text" name="source" id="source"
                                    value="{{ old('source', $payment_expense->source) }}"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Penerima Dana / Vendor
                                </label>
                                <select name="vendor_id" id="vendor_id"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-emerald-500">
                                    <option value="">-- Non-Vendor / Pribadi / Kasir --</option>
                                    @foreach ($vendor as $v)
                                        <option value="{{ $v->id }}" {{ old('vendor_id', $payment_expense->vendor_id) == $v->id ? 'selected' : '' }}>
                                            {{ $v->nama_vendors }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 flex items-center justify-between">
                                    <span>No. Voucher / Bon <span class="text-rose-500">*</span></span>
                                    <button type="button" onclick="fetchVoucherNumber()" title="Generate ulang nomor voucher sesuai akun" class="text-[10px] text-emerald-600 hover:text-emerald-800 font-semibold inline-flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-rotate"></i> Refresh
                                    </button>
                                </label>
                                <div class="relative">
                                    <input type="text" name="voucher_number" id="voucher_number"
                                        value="{{ old('voucher_number', $payment_expense->voucher_number) }}"
                                        class="w-full pl-8 pr-3 py-2 text-xs font-mono font-bold text-slate-900 rounded-xl border border-emerald-300 bg-emerald-50/50 focus:ring-2 focus:ring-emerald-500" required>
                                    <i class="fa-solid fa-barcode absolute left-2.5 top-2.5 text-emerald-600 text-xs"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Tanggal Transaksi <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="date" id="date"
                                    value="{{ old('date', $payment_expense->date) }}"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 bg-white font-medium focus:ring-2 focus:ring-emerald-500" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Sumber Akun / Kas Pengeluaran <span class="text-rose-500">*</span>
                            </label>
                            <select name="account_header_id" id="account_header_id" onchange="onAccountHeaderChanged()"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 bg-white font-medium focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">-- Pilih Akun Kas / Bank --</option>
                                @foreach ($account as $acc)
                                    <option value="{{ $acc->id }}" data-kode="{{ $acc->kode_akun }}" data-nama="{{ $acc->nama_akun }}"
                                        {{ old('account_header_id', $payment_expense->from_account_id) == $acc->id ? 'selected' : '' }}>
                                        [{{ $acc->kode_akun }}] {{ $acc->nama_akun }} ({{ $acc->tipe_akun }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- TOTAL BOX -->
                        <div class="rounded-xl p-3 bg-gradient-to-br from-slate-900 to-slate-800 text-white border border-slate-700 shadow-inner flex items-center justify-between">
                            <div>
                                <div class="text-[10px] uppercase font-bold tracking-wider text-emerald-400">Total Pengeluaran Kas</div>
                                <div class="text-xs text-slate-400 font-mono italic" id="live-terbilang-text">Nol Rupiah</div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg sm:text-xl font-bold font-mono text-emerald-300" id="live-total-display">
                                    Rp 0
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- DISTRIBUTION GRID -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-list-check"></i>
                            </span>
                            <h3 class="text-sm font-bold text-gray-900">Distribusi Akun Beban</h3>
                        </div>
                    </div>

                    <div class="border border-gray-200 rounded-xl overflow-x-auto shadow-2xs">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-gray-200 select-none">
                                <tr>
                                    <th class="py-2.5 px-3 w-10 text-center">#</th>
                                    <th class="py-2.5 px-3 w-4/12">Akun Beban (GL Account) <span class="text-rose-500">*</span></th>
                                    <th class="py-2.5 px-3 w-4/12">Deskripsi / Rincian Belanja</th>
                                    <th class="py-2.5 px-3 w-36 text-right">Nominal (Rp) <span class="text-rose-500">*</span></th>
                                    <th class="py-2.5 px-3 w-28">Pajak</th>
                                    <th class="py-2.5 px-3 w-36 text-right">Subtotal</th>
                                    <th class="py-2.5 px-2 w-12 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="expense-items-body" class="divide-y divide-gray-100 bg-white">
                                <!-- Populated via JS -->
                            </tbody>
                            <tfoot class="bg-slate-50 font-bold border-t border-gray-200 text-slate-800">
                                <tr>
                                    <td colspan="3" class="py-2.5 px-4 text-right uppercase text-[11px] text-gray-500">
                                        Subtotal Beban:
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono" id="grid-subtotal">Rp 0</td>
                                    <td class="py-2.5 px-3 text-center text-[11px] text-gray-500">Pajak: <span id="grid-tax">Rp 0</span></td>
                                    <td class="py-2.5 px-3 text-right font-mono text-emerald-700 text-sm" id="grid-grandtotal">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="button" onclick="addExpenseRow()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs border border-slate-200 active:scale-95 transition-all">
                            <i class="fa-solid fa-plus text-emerald-600"></i>
                            <span>+ Tambah Baris Beban</span>
                        </button>
                    </div>
                </div>

                <!-- FOOTER PANEL -->
                <div class="pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Catatan / Memo Pengeluaran
                        </label>
                        <textarea name="notes" id="notes" rows="3" oninput="updateJournalPreview()"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500">{{ old('notes', $payment_expense->notes) }}</textarea>
                    </div>

                    <!-- Dokumen Bukti -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center justify-between">
                            <span>Dokumen Bukti (Bon / Struk / Nota)</span>
                            @if ($payment_expense->attachment)
                                <a href="{{ $payment_expense->attachment_url }}" target="_blank"
                                    class="text-[11px] text-emerald-600 font-bold hover:underline flex items-center gap-1">
                                    <i class="fa-solid fa-file-arrow-down"></i> Lihat Dokumen Saat Ini
                                </a>
                            @endif
                        </label>

                        <div class="border-2 border-dashed border-gray-300 hover:border-emerald-500 rounded-xl p-4 bg-slate-50/50 text-center transition-colors relative cursor-pointer"
                            onclick="document.getElementById('attachment').click()">
                            <input type="file" name="attachment" id="attachment" accept="image/*,application/pdf" class="hidden"
                                onchange="previewAttachment(event)">
                            <div class="flex flex-col items-center justify-center gap-1.5" id="upload-placeholder">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700">Ganti file dokumen jika diperlukan</span>
                                <span class="text-[10px] text-gray-400">Format: JPG, PNG, WEBP, PDF (Maks. 5 MB)</span>
                            </div>
                            <div id="upload-preview" class="hidden text-xs font-semibold text-emerald-700 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-paperclip"></i>
                                <span id="upload-filename">Nama file terpilih</span>
                                <span class="text-[10px] text-gray-400" id="upload-filesize"></span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- TAB 2: AUDIT JURNAL -->
            <div id="sage-tab-journal" class="p-5 sm:p-6 space-y-4 hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-book-journal-whills text-emerald-600"></i>
                            <span>Pratinjau Jurnal Akuntansi</span>
                        </h3>
                        <p class="text-xs text-gray-500">Jurnal akuntansi terkait yang akan diperbarui secara otomatis.</p>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-gray-200">
                            <tr>
                                <th class="py-2.5 px-4 w-32">Kode Akun</th>
                                <th class="py-2.5 px-4">Nama Akun & Keterangan</th>
                                <th class="py-2.5 px-4 w-36 text-right">Debit (Rp)</th>
                                <th class="py-2.5 px-4 w-36 text-right">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="journal-preview-body" class="divide-y divide-gray-100 bg-white"></tbody>
                        <tfoot class="bg-slate-50 font-bold border-t border-gray-200 text-slate-800">
                            <tr>
                                <td colspan="2" class="py-2.5 px-4 text-right uppercase text-[11px] text-gray-500">Total Keseimbangan:</td>
                                <td class="py-2.5 px-4 text-right font-mono" id="journal-total-debit">Rp 0</td>
                                <td class="py-2.5 px-4 text-right font-mono" id="journal-total-credit">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>

    </form>
</div>

<script>
const GL_ACCOUNTS = @json($account_beban);
const SALES_TAXES = @json($sales_taxes);
const INITIAL_DETAILS = @json($payment_expense->details);

let expenseRows = [];

document.addEventListener('DOMContentLoaded', () => {
    if (INITIAL_DETAILS && INITIAL_DETAILS.length > 0) {
        INITIAL_DETAILS.forEach((d, idx) => {
            expenseRows.push({
                id: idx,
                account_id: d.account_id,
                deskripsi: d.deskripsi || '',
                amount: parseFloat(d.amount) || 0,
                tax_id: d.sales_taxes_id || ''
            });
        });
    } else {
        addExpenseRow();
    }
    renderExpenseGrid();
    updateCalculations();
});

function switchSageTab(tab) {
    const tabForm = document.getElementById('sage-tab-form');
    const tabJournal = document.getElementById('sage-tab-journal');
    const btnForm = document.getElementById('tab-btn-form');
    const btnJournal = document.getElementById('tab-btn-journal');

    if (tab === 'form') {
        tabForm.classList.remove('hidden');
        tabJournal.classList.add('hidden');
        btnForm.className = "px-3 py-1.5 rounded-lg bg-emerald-600 text-white shadow-xs transition-all";
        btnJournal.className = "px-3 py-1.5 rounded-lg text-slate-300 hover:text-white transition-all";
    } else {
        tabForm.classList.add('hidden');
        tabJournal.classList.remove('hidden');
        btnForm.className = "px-3 py-1.5 rounded-lg text-slate-300 hover:text-white transition-all";
        btnJournal.className = "px-3 py-1.5 rounded-lg bg-emerald-600 text-white shadow-xs transition-all";
        updateJournalPreview();
    }
}

function addExpenseRow() {
    expenseRows.push({
        id: expenseRows.length,
        account_id: '',
        deskripsi: '',
        amount: 0,
        tax_id: ''
    });
    renderExpenseGrid();
}

function removeExpenseRow(index) {
    if (expenseRows.length <= 1) {
        alert('Minimal harus terdapat satu baris pengeluaran.');
        return;
    }
    expenseRows.splice(index, 1);
    renderExpenseGrid();
    updateCalculations();
}

function renderExpenseGrid() {
    const tbody = document.getElementById('expense-items-body');
    tbody.innerHTML = '';

    expenseRows.forEach((row, idx) => {
        const tr = document.createElement('tr');
        tr.className = "hover:bg-slate-50/50 transition-colors";

        let optionsHtml = '<option value="">-- Pilih Akun Beban --</option>';
        GL_ACCOUNTS.forEach(acc => {
            const isSelected = row.account_id == acc.id ? 'selected' : '';
            optionsHtml += `<option value="${acc.id}" ${isSelected}>[${acc.kode_akun}] ${acc.nama_akun}</option>`;
        });

        let taxOptionsHtml = '<option value="">Tanpa Pajak</option>';
        SALES_TAXES.forEach(tax => {
            const isSelected = row.tax_id == tax.id ? 'selected' : '';
            taxOptionsHtml += `<option value="${tax.id}" ${isSelected}>${tax.name} (${tax.rate}%)</option>`;
        });

        const amountNum = parseFloat(row.amount) || 0;
        let taxRate = 0;
        const taxObj = SALES_TAXES.find(t => t.id == row.tax_id);
        if (taxObj) taxRate = parseFloat(taxObj.rate) || 0;
        const lineTotal = amountNum + (amountNum * (taxRate / 100));

        tr.innerHTML = `
            <td class="py-2.5 px-3 text-center text-gray-400 font-mono">${idx + 1}</td>
            <td class="py-2.5 px-3">
                <select name="items[${idx}][account_id]" onchange="onRowFieldChange(${idx}, 'account_id', this.value)"
                    class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-gray-300 bg-white font-medium focus:ring-1 focus:ring-emerald-500" required>
                    ${optionsHtml}
                </select>
            </td>
            <td class="py-2.5 px-3">
                <input type="text" name="items[${idx}][deskripsi]" value="${row.deskripsi || ''}"
                    oninput="onRowFieldChange(${idx}, 'deskripsi', this.value)"
                    class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-gray-300 focus:ring-1 focus:ring-emerald-500">
            </td>
            <td class="py-2.5 px-3 text-right">
                <input type="number" step="0.01" min="0" name="items[${idx}][amount]" value="${row.amount || ''}"
                    oninput="onRowFieldChange(${idx}, 'amount', this.value)"
                    class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-gray-300 text-right font-mono font-bold focus:ring-1 focus:ring-emerald-500" required>
            </td>
            <td class="py-2.5 px-3">
                <select name="items[${idx}][tax_id]" onchange="onRowFieldChange(${idx}, 'tax_id', this.value)"
                    class="w-full px-2 py-1.5 text-xs rounded-lg border border-gray-300 bg-white focus:ring-1 focus:ring-emerald-500">
                    ${taxOptionsHtml}
                </select>
            </td>
            <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800">
                Rp ${formatRupiah(lineTotal)}
            </td>
            <td class="py-2.5 px-2 text-center">
                <button type="button" onclick="removeExpenseRow(${idx})"
                    class="w-6 h-6 rounded-md hover:bg-rose-100 text-rose-500 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

function onRowFieldChange(index, field, value) {
    if (expenseRows[index]) {
        expenseRows[index][field] = value;
        if (field === 'amount' || field === 'tax_id') {
            updateCalculations();
        }
    }
}

function updateCalculations() {
    let subtotal = 0;
    let totalTax = 0;

    expenseRows.forEach(row => {
        const amt = parseFloat(row.amount) || 0;
        subtotal += amt;

        if (row.tax_id) {
            const taxObj = SALES_TAXES.find(t => t.id == row.tax_id);
            if (taxObj) {
                totalTax += amt * ((parseFloat(taxObj.rate) || 0) / 100);
            }
        }
    });

    const grandTotal = subtotal + totalTax;

    document.getElementById('grid-subtotal').innerText = 'Rp ' + formatRupiah(subtotal);
    document.getElementById('grid-tax').innerText = 'Rp ' + formatRupiah(totalTax);
    document.getElementById('grid-grandtotal').innerText = 'Rp ' + formatRupiah(grandTotal);
    document.getElementById('live-total-display').innerText = 'Rp ' + formatRupiah(grandTotal);
    document.getElementById('live-terbilang-text').innerText = terbilang(grandTotal) + ' Rupiah';

    updateJournalPreview();
}

function updateJournalPreview() {
    const tbody = document.getElementById('journal-preview-body');
    if (!tbody) return;
    tbody.innerHTML = '';

    const headerAccSelect = document.getElementById('account_header_id');
    const selectedHeaderOpt = headerAccSelect.options[headerAccSelect.selectedIndex];
    const headerAccName = selectedHeaderOpt ? selectedHeaderOpt.getAttribute('data-nama') : 'Kas / Bank';
    const headerAccKode = selectedHeaderOpt ? selectedHeaderOpt.getAttribute('data-kode') : '-';

    let totalDebit = 0;

    expenseRows.forEach((row, idx) => {
        const amt = parseFloat(row.amount) || 0;
        if (amt > 0) {
            const accObj = GL_ACCOUNTS.find(a => a.id == row.account_id);
            const kode = accObj ? accObj.kode_akun : '-';
            const nama = accObj ? accObj.nama_akun : 'Akun Beban';
            const desc = row.deskripsi || 'Pengeluaran Item #' + (idx + 1);

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="py-2.5 px-4 font-mono font-bold text-slate-800">${kode}</td>
                <td class="py-2.5 px-4 font-medium text-slate-700">${nama} <span class="text-gray-400 text-[11px]">(${desc})</span></td>
                <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900">Rp ${formatRupiah(amt)}</td>
                <td class="py-2.5 px-4 text-right font-mono text-gray-300">-</td>
            `;
            tbody.appendChild(tr);
            totalDebit += amt;

            if (row.tax_id) {
                const taxObj = SALES_TAXES.find(t => t.id == row.tax_id);
                if (taxObj) {
                    const taxAmt = amt * ((parseFloat(taxObj.rate) || 0) / 100);
                    const trTax = document.createElement('tr');
                    trTax.innerHTML = `
                        <td class="py-2.5 px-4 font-mono font-bold text-indigo-700">TAX-IN</td>
                        <td class="py-2.5 px-4 font-medium text-indigo-700">Pajak Masukan: ${taxObj.name}</td>
                        <td class="py-2.5 px-4 text-right font-mono font-bold text-indigo-700">Rp ${formatRupiah(taxAmt)}</td>
                        <td class="py-2.5 px-4 text-right font-mono text-gray-300">-</td>
                    `;
                    tbody.appendChild(trTax);
                    totalDebit += taxAmt;
                }
            }
        }
    });

    const trCredit = document.createElement('tr');
    trCredit.innerHTML = `
        <td class="py-2.5 px-4 font-mono font-bold text-emerald-800">${headerAccKode}</td>
        <td class="py-2.5 px-4 font-medium text-emerald-800 pl-8">↳ ${headerAccName} (Pengeluaran Kas)</td>
        <td class="py-2.5 px-4 text-right font-mono text-gray-300">-</td>
        <td class="py-2.5 px-4 text-right font-mono font-bold text-emerald-900">Rp ${formatRupiah(totalDebit)}</td>
    `;
    tbody.appendChild(trCredit);

    document.getElementById('journal-total-debit').innerText = 'Rp ' + formatRupiah(totalDebit);
    document.getElementById('journal-total-credit').innerText = 'Rp ' + formatRupiah(totalDebit);
}

function onAccountHeaderChanged() {
    updateJournalPreview();
}

function fetchVoucherNumber() {
    const accId = document.getElementById('account_header_id').value;
    const date = document.getElementById('date').value;

    fetch(`{{ route('payment_expense.generate_voucher') }}?account_id=${accId}&date=${date}`)
        .then(res => res.json())
        .then(data => {
            if (data && data.voucher_number) {
                document.getElementById('voucher_number').value = data.voucher_number;
            }
        })
        .catch(err => console.error(err));
}

function previewAttachment(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('upload-placeholder').classList.add('hidden');
        document.getElementById('upload-preview').classList.remove('hidden');
        document.getElementById('upload-filename').innerText = file.name;
        document.getElementById('upload-filesize').innerText = '(' + (file.size / 1024).toFixed(1) + ' KB)';
    }
}

function formatRupiah(angka) {
    return Math.round(angka).toLocaleString('id-ID');
}

function terbilang(angka) {
    angka = Math.floor(Math.abs(Number(angka)));
    const baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
    let hasil = '';

    if (angka < 12) {
        hasil = ' ' + baca[angka];
    } else if (angka < 20) {
        hasil = terbilang(angka - 10) + ' Belas';
    } else if (angka < 100) {
        hasil = terbilang(Math.floor(angka / 10)) + ' Puluh' + terbilang(angka % 10);
    } else if (angka < 200) {
        hasil = ' Seratus' + terbilang(angka - 100);
    } else if (angka < 1000) {
        hasil = terbilang(Math.floor(angka / 100)) + ' Ratus' + terbilang(angka % 100);
    } else if (angka < 2000) {
        hasil = ' Seribu' + terbilang(angka - 1000);
    } else if (angka < 1000000) {
        hasil = terbilang(Math.floor(angka / 1000)) + ' Ribu' + terbilang(angka % 1000);
    } else if (angka < 1000000000) {
        hasil = terbilang(Math.floor(angka / 1000000)) + ' Juta' + terbilang(angka % 1000000);
    } else if (angka < 1000000000000) {
        hasil = terbilang(Math.floor(angka / 1000000000)) + ' Miliar' + terbilang(angka % 1000000000);
    } else {
        hasil = terbilang(Math.floor(angka / 1000000000000)) + ' Triliun' + terbilang(angka % 1000000000000);
    }

    return hasil.trim() || 'Nol';
}
</script>
@endsection
