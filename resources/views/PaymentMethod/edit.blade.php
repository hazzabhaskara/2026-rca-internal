@extends('layouts.app')

@section('content')
    <div class="py-10 max-w-full mx-auto px-6">
        <form action="{{ route('PaymentMethod.update', $paymentMethod->id) }}" method="POST"
            class="bg-white p-6 rounded-xl shadow-sm space-y-6">
            @csrf
            @method('PUT')

            {{-- Notifikasi error validasi --}}
            @if ($errors->any())
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    <div class="font-semibold mb-2">Terjadi kesalahan:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Header Method --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Kode Method</label>
                    <input type="text" name="kode_jenis" value="{{ old('kode_jenis', $paymentMethod->kode_jenis) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Nama Method</label>
                    <input type="text" name="nama_jenis" value="{{ old('nama_jenis', $paymentMethod->nama_jenis) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Status Method</label>
                    <select name="status"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                        <option value="1" {{ old('status', $paymentMethod->status) == 1 ? 'selected' : '' }}>Ya
                        </option>
                        <option value="0" {{ old('status', $paymentMethod->status) == 0 ? 'selected' : '' }}>Tidak
                        </option>
                    </select>
                </div>
            </div>

            {{-- Akun Terkait --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-gray-800">Akun Terkait</h3>
                    <button type="button" id="add-row" class="btn btn-secondary">+ Tambah Account</button>
                </div>

                <table class="w-full border-collapse border text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="border px-2 py-1 text-left w-[45%]">Account</th>
                            <th class="border px-2 py-1 text-left w-[45%]">Deskripsi</th>
                            <th class="border px-2 py-1 text-left w-[10%]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="rows-body">
                        @php
                            // rebuild dari old() jika validasi gagal, else pakai data relasi
                            $oldAcc = old('account_id');
                            $oldDesk = old('deskripsi');
                            $oldDet = old('detail_id');

                            $useOld = is_array($oldAcc) || is_array($oldDesk) || is_array($oldDet);

                            $details = $useOld
                                ? collect($oldAcc ?? [])->map(function ($v, $i) use ($oldDesk, $oldDet) {
                                    return (object) [
                                        'id' => $oldDet[$i] ?? null,
                                        'account_id' => $v,
                                        'deskripsi' => $oldDesk[$i] ?? null,
                                    ];
                                })
                                : $paymentMethod->details;
                        @endphp

                        @forelse ($details as $d)
                            <tr class="border-b row-item">
                                <td class="border px-2 py-1">
                                    {{-- id detail disertakan agar bisa dibedakan update vs create --}}
                                    <input type="hidden" name="detail_id[]" value="{{ $d->id ?? '' }}">
                                    <select name="account_id[]" class="select2 w-full border rounded">
                                        <option value="">-- Pilih Akun --</option>
                                        @foreach ($account as $acc)
                                            <option value="{{ $acc->id }}"
                                                {{ (string) ($d->account_id ?? '') === (string) $acc->id ? 'selected' : '' }}>
                                                {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="text" name="deskripsi[]" value="{{ $d->deskripsi ?? '' }}"
                                        class="w-full border rounded px-2 py-1">
                                </td>
                                <td class="border px-2 py-1">
                                    <button type="button" class="btn btn-danger remove-row">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            {{-- fallback minimal 1 baris --}}
                            <tr class="border-b row-item">
                                <td class="border px-2 py-1">
                                    <input type="hidden" name="detail_id[]" value="">
                                    <select name="account_id[]" class="select2 w-full border rounded">
                                        <option value="">-- Pilih Akun --</option>
                                        @foreach ($account as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->kode_akun }} -
                                                {{ $acc->nama_akun }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="text" name="deskripsi[]" class="w-full border rounded px-2 py-1">
                                </td>
                                <td class="border px-2 py-1">
                                    <button type="button" class="btn btn-danger remove-row">Hapus</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="text-right">
                <a href="{{ route('PaymentMethod.index') }}" class="btn btn-secondary mr-2">Kembali</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
@endsection

{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


{{-- jQuery & Select2 JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function initSelect2(scope) {
        $(scope).find('.select2').select2();
    }

    $(document).ready(function() {
        initSelect2(document);

        const rowTemplate = `
        <tr class="border-b row-item">
            <td class="border px-2 py-1">
                <input type="hidden" name="detail_id[]" value="">
                <select name="account_id[]" class="select2 w-full border rounded">
                    <option value="">-- Pilih Akun --</option>
                    @foreach ($account as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->kode_akun }} - {{ $acc->nama_akun }}</option>
                    @endforeach
                </select>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="deskripsi[]" class="w-full border rounded px-2 py-1">
            </td>
            <td class="border px-2 py-1">
                <button type="button" class="btn btn-danger remove-row">Hapus</button>
            </td>
        </tr>`;

        $('#add-row').on('click', function() {
            const $row = $(rowTemplate);
            $('#rows-body').append($row);
            initSelect2($row);
        });

        $(document).on('click', '.remove-row', function() {
            const total = $('#rows-body .row-item').length;
            if (total > 1) {
                $(this).closest('tr').remove();
            } else {
                // jika tinggal 1, kosongkan saja
                const $tr = $(this).closest('tr');
                $tr.find('input[name="detail_id[]"]').val('');
                $tr.find('select[name="account_id[]"]').val('').trigger('change');
                $tr.find('input[name="deskripsi[]"]').val('');
            }
        });
    });
</script>
