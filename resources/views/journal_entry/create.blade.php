@extends('layouts.app')

@section('content')
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="font-bold text-lg mb-2">General Journal Create</h2>
                <form id="journal-entry-form" action="{{ route('journal_entry.store') }}" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Header -->


                    <!-- Form Inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 text-base">
                        <!-- Periode Buku -->
                        <div>
                            <label for="unit_kerja_id" class="block text-sm font-semibold text-gray-700 mb-1">Periode
                                Buku</label>
                            <select name="unit_kerja_id[]" id="unit_kerja_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                @foreach ($periodeBuku as $g)
                                    <option value="{{ $g->id }}" data-tahun="{{ $g->tahun }}">{{ $g->tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Source -->
                        <div>
                            <label for="source" class="block text-sm font-semibold text-gray-700 mb-1">Source</label>
                            <input type="text" name="source" id="source" placeholder="Masukkan Source"
                                class="w-full rounded-md border border-gray-300 px-3 py-2" value="{{ old('source') }}"
                                required>
                        </div>

                        <!-- Date -->
                        <div>
                            <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-1">Date</label>
                            <input type="date" name="tanggal" id="tanggal"
                                class="w-full rounded-md border border-gray-300 px-3 py-2" value="{{ old('tanggal') }}"
                                required>
                        </div>

                        <!-- Comment -->
                        <div>
                            <label for="comment" class="block text-sm font-semibold text-gray-700 mb-1">Comment</label>
                            <input type="text" name="comment" id="comment" placeholder="Masukkan Comment(opsional)"
                                class="w-full rounded-md border border-gray-300 px-3 py-2" value="{{ old('comment') }}">
                        </div>
                    </div>


                    <!-- Table -->
                    <div class="overflow-x-auto overflow-y-auto max-h-[450px] mb-6 border rounded">
                        <table class="min-w-full table-auto border-collapse text-base text-left bg-white">
                            <thead class="bg-gray-100 text-gray-700 font-semibold sticky top-0 z-10">
                                <tr>
                                    <th class="border px-4 py-3 text-center w-[18%]">Accounts</th>
                                    <th class="border px-4 py-3 text-center w-[10%]">Debits</th>
                                    <th class="border px-4 py-3 text-center w-[10%]">Credits</th>
                                    <th class="border px-4 py-3 text-center w-[20%]">Comment</th>
                                    @can('specpose.access')
                                        <th class="border px-4 py-3 text-center w-[15%]">Specpose</th>
                                    @endcan
                                    @can('fiscal.access')
                                        <th class="border px-4 py-3 text-center w-[7%]">Fiscorr</th>
                                        <th class="border px-4 py-3 text-center w-[15%]">Penyesuaian Fiskal</th>
                                    @endcan
                                    <th class="border px-4 py-3 text-center w-[5%]">Aksi</th>
                                </tr>

                            </thead>
                            <tbody id="item-table-body" class="bg-white">

                            </tbody>
                            <tfoot class="bg-gray-50 font-semibold">
                                <tr>
                                    <td class="border px-4 py-2 text-right">TOTAL</td>
                                    <td class="border px-4 py-2 text-right" id="total-debit"></td>
                                    <td class="border px-4 py-2 text-right" id="total-credit"></td>
                                    <td colspan="2" class="border"></td>
                                </tr>
                        </table>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ route('journal_entry.index') }}" onclick="return confirmCancel(event)"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md">
                            Cancel
                        </a>

                        <button type="submit"
                            class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-sm font-medium rounded-md">
                            Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- File Modal -->
    <div id="fileModal"
        class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
            </h3>
            <div class="space-y-3 text-sm text-gray-700">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">Import File Excel:</label>
                    <input type="file" name="file" class="block w-full text-sm border rounded px-2 py-1" required>
                    <button type="submit" class="bg-green-500 text-white w-full py-1 rounded hover:bg-green-600 text-sm">
                        <i class="fas fa-file-upload mr-1"></i> Import
                    </button>
                </form>
            </div>
            <div class="mt-4 text-right">
                <button onclick="document.getElementById('fileModal').classList.add('hidden')"
                    class="text-sm text-gray-500 hover:text-gray-700">Tutup</button>
            </div>
        </div>
    </div>
    <style>
        /* Style agar Select2 seragam dengan input Tailwind */
        .select2-container .select2-selection--single {
            height: 36px !important;
            /* sama dengan input Tailwind h-9 */
            padding: 4px 8px !important;
            border: 1px solid #d1d5db !important;
            /* border gray-300 */
            border-radius: 0.375rem !important;
            /* rounded-md */
            display: flex;
            align-items: center;
            background-color: #fff !important;
        }

        .select2-container .select2-selection__rendered {
            line-height: 1.25rem !important;
            /* teks rata */
            font-size: 0.875rem !important;
            /* text-sm */
            color: #374151 !important;
            /* gray-700 */
        }

        .select2-container .select2-selection__arrow {
            height: 100% !important;
            top: 0 !important;
        }
    </style>

@endsection

@push('scripts')
    <!-- Include Select2 CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const periodeSelect = document.getElementById('unit_kerja_id');
            const tanggalInput = document.getElementById('tanggal');

            periodeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const tahun = selectedOption.getAttribute('data-tahun');

                if (tahun) {
                    tanggalInput.min = `${tahun}-01-01`;
                    tanggalInput.max = `${tahun}-12-31`;
                    tanggalInput.value = ''; // reset tanggal supaya tidak di luar range
                } else {
                    tanggalInput.min = '';
                    tanggalInput.max = '';
                }
            });
        });
    </script>

    <!-- JS for dropdown toggle -->
    <script>
        $(document).ready(function() {
            let rowIndex = 0;

            function generateRow(index) {
                return `
        <tr class="item-row" data-index="${index}" data-tipe-akun="">
          <td class="border px-2 py-1">
            <select class="item-select w-full border rounded" data-index="${index}"></select>
          </td>
          <td class="border px-2 py-1">
            <input type="hidden" name="items[${index}][kode_akun]" class="kode_akun-${index}" />
            <input type="hidden" name="items[${index}][departemen_akun_id]" class="departemen-akun-${index}" />
            <input type="text" name="items[${index}][debits]" 
                   class="money-input w-full border rounded px-2 py-1 text-right debit-${index}" inputmode="numeric" />
          </td>
          <td class="border px-2 py-1">
            <input type="text" name="items[${index}][credits]" 
                   class="money-input w-full border rounded px-2 py-1 text-right credit-${index}" inputmode="numeric" />
          </td>
          <td class="border px-2 py-1">
            <input type="text" name="items[${index}][comment]" class="w-full border rounded px-2 py-1" />
          </td>
          @can('specpose.access')
          
          <td class="border px-2 py-1">
            <select name="items[${index}][project_id]" class="w-full border rounded px-2 py-1">
                <option value="">-- Pilih Specpose --</option>
                @foreach ($project as $prj)
                    <option value="{{ $prj->id }}">{{ $prj->nama_project }}</option>
                @endforeach
            </select>
          </td>
          @endcan
            @can('fiscal.access')
                <td class="border px-2 py-1 text-center">
                        <input type="hidden" name="items[${index}][pajak]" value="0">
                        <input type="checkbox" class="pajak-checkbox" name="items[${index}][pajak]" value="1">
                    </td>
                    <td class="border px-2 py-1 penyesuaian-col text-center">-</td>
            @endcan

        
          <td class="border px-2 py-1 text-center space-x-1">
            <button type="button" class="add-row px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600" data-index="${index}">+</button>
            <button type="button" class="remove-row px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600" data-index="${index}">X</button>
          </td>
        </tr>`;
            }

            function addRowWithData(data = null, insertAfterIndex = null) {
                const newRow = generateRow(rowIndex);

                if (insertAfterIndex !== null) {
                    $(`tr[data-index="${insertAfterIndex}"]`).after(newRow);
                } else {
                    $('#item-table-body').append(newRow);
                }

                attachSelect2(rowIndex);

                if (data) {
                    const select = $(`select[data-index="${rowIndex}"]`);
                    const optionText = data.kode_akun + ' - ' + (data.nama_akun || '');
                    const option = new Option(optionText, data.kode_akun, true, true);
                    select.append(option).trigger('change.select2');

                    $(`.kode_akun-${rowIndex}`).val(data.kode_akun);
                    $(`.debit-${rowIndex}`).val(new Intl.NumberFormat('id-ID').format(data.total_debit || 0));
                    $(`.credit-${rowIndex}`).val(new Intl.NumberFormat('id-ID').format(data.total_credit || 0));
                }

                rowIndex++;
                updateTotals();
            }

            function attachSelect2(index) {
                $(`select[data-index="${index}"]`).select2({
                    placeholder: 'Cari Akun...',
                    width: '100%', // wajib supaya select2 full width
                    ajax: {
                        url: '/search-account',
                        dataType: 'json',
                        delay: 250,
                        data: params => ({
                            q: params.term
                        }),
                        processResults: data => {
                            let results = [];
                            data.forEach(item => {
                                results.push({
                                    id: item.id,
                                    text: `${item.kode_akun} - ${item.nama_akun}`,
                                    kode_akun: item.kode_akun,
                                    departemen_akun_id: null,
                                    tipe_akun: item.tipe_akun
                                });
                                if (item.daftar_departemen && item.daftar_departemen.length >
                                    0) {
                                    item.daftar_departemen.forEach(dept => {
                                        results.push({
                                            id: `d-${dept.id}`,
                                            text: `${item.kode_akun} - ${item.nama_akun} ${dept.deskripsi}`,
                                            kode_akun: item.kode_akun,
                                            departemen_akun_id: dept.id,
                                            deskripsi_departemen: dept
                                                .deskripsi,
                                            tipe_akun: item.tipe_akun
                                        });
                                    });
                                }
                            });
                            return {
                                results
                            };
                        },
                        cache: true
                    }
                }).on('select2:select', function(e) {
                    const data = e.params.data;
                    $(`.kode_akun-${index}`).val(data.kode_akun);
                    $(`.departemen-akun-${index}`).val(data.departemen_akun_id ?? '');
                    $(`tr[data-index="${index}"]`).attr('data-tipe-akun', data.tipe_akun);

                    $.ajax({
                        url: '/journal-entry/auto-data',
                        type: 'GET',
                        data: {
                            tanggal: $('#tanggal').val(),
                            kode_akun: data.kode_akun
                        },
                        success: function(res) {
                            if (res.success) {
                                $(`.debit-${index}`).val(new Intl.NumberFormat('id-ID').format(
                                    res.total_debit || 0));
                                $(`.credit-${index}`).val(new Intl.NumberFormat('id-ID').format(
                                    res.total_credit || 0));
                                updateTotals();
                            }
                        }
                    });
                });
            }

            function updateTotals() {
                let totalDebit = 0;
                let totalCredit = 0;

                document.querySelectorAll('input[name^="items"][name$="[debits]"]').forEach(input => {
                    let val = parseFloat(input.value.replace(/\./g, '').replace(',', '.')) || 0;
                    totalDebit += val;
                });
                document.querySelectorAll('input[name^="items"][name$="[credits]"]').forEach(input => {
                    let val = parseFloat(input.value.replace(/\./g, '').replace(',', '.')) || 0;
                    totalCredit += val;
                });

                $('#total-debit').text(new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(totalDebit));
                $('#total-credit').text(new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(totalCredit));
            }

            function formatNumberInput(input) {
                let value = input.value;

                // Izinkan pengguna mengetik koma dulu tanpa dihapus
                if (value.endsWith(',')) {
                    return; // jangan format dulu, tunggu user lanjutkan angka desimalnya
                }

                // Hapus semua karakter kecuali angka dan koma
                value = value.replace(/[^\d,]/g, '');

                if (value === '') {
                    input.value = '';
                    return;
                }

                // Pisahkan bagian ribuan dan desimal
                let parts = value.split(',');
                let integerPart = parts[0];
                let decimalPart = parts[1] ? parts[1].slice(0, 2) : '';

                // Tambahkan titik pemisah ribuan
                integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                // Gabungkan kembali
                if (decimalPart !== '') {
                    input.value = `${integerPart},${decimalPart}`;
                } else {
                    input.value = integerPart;
                }
            }



            // ✅ Tambahin event untuk format angka saat user input
            $(document).on('input', '.money-input', function() {
                formatNumberInput(this);
                updateTotals();
            });

            // Checkbox pajak → kolom tambahan
            $(document).on('change', '.pajak-checkbox', function() {
                const row = $(this).closest('tr');
                const tipeAkun = row.attr('data-tipe-akun');
                const col = row.find('.penyesuaian-col');
                const idx = row.data('index');

                if (this.checked) {
                    let html = `
                <div class="flex flex-col space-y-1">
                    <select name="items[${idx}][penyesuaian_fiskal]" class="w-full border rounded px-2 py-1">
            `;
                    if (tipeAkun === 'Pendapatan') {
                        html += '<option value="non_tax">Non Tax Object</option>';
                        html += '<option value="pph_final">PPH Final</option>';
                    } else if (tipeAkun === 'Beban') {
                        html += '<option value="koreksi_plus">Koreksi Positif</option>';
                        html += '<option value="koreksi_minus">Koreksi Negatif</option>';
                    } else {
                        html += '<option value="">-</option>';
                    }
                    html += `</select>
                    <input type="text" name="items[${idx}][kode_fiscal]" placeholder="Kode Fiscal" class="w-full border rounded px-2 py-1" />
                </div>
            `;
                    col.html(html);
                } else {
                    col.text('-');
                }
            });

            // Hapus row
            $(document).on('click', '.remove-row', function() {
                const index = $(this).data('index');
                $(`tr[data-index="${index}"]`).remove();
                updateTotals();
            });

            // Tambah row setelah row tertentu
            $(document).on('click', '.add-row', function() {
                const index = $(this).data('index');
                addRowWithData(null, index);
            });

            // Inisialisasi awal
            for (let i = 0; i < 30; i++) {
                addRowWithData();
            }

            $('#add-row').on('click', function() {
                addRowWithData();
            });
        });
    </script>
@endpush
