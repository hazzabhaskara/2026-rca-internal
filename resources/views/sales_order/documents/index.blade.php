@extends('layouts.app')

@section('content')
    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">

                <h2 class="text-xl font-bold mb-4">Daftar Dokumen Sales Order</h2>

                {{-- Tampilkan pesan sukses atau error --}}
                @if (session('success'))
                    <div class="bg-green-100 text-green-700 p-2 rounded mb-3">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 text-red-700 p-2 rounded mb-3">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Table Dokumen --}}
                <div class="overflow-x-auto">
                    <table class="table-auto border-collapse border w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-3 py-2 text-left">#</th>
                                <th class="border px-3 py-2 text-left">Sales Order</th>
                                <th class="border px-3 py-2 text-left">Nama File</th>
                                <th class="border px-3 py-2 text-left">Diupload Pada</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documents as $index => $doc)
                                <tr>
                                    <td class="border px-3 py-2">{{ $loop->iteration + ($documents->firstItem() - 1) }}</td>
                                    <td class="border px-3 py-2">{{ $doc->salesOrder->order_number ?? '-' }}</td>
                                    <td class="border px-3 py-2">{{ $doc->document_name }}</td>
                                    <td class="border px-3 py-2">{{ $doc->created_at->format('d-m-Y H:i') }}</td>
                                    <td class="border px-3 py-2 text-center space-x-2">
                                        <a href="{{ route('sales_orders.documents.download', [$doc->salesOrder->id, $doc->id]) }}"
                                            class="text-blue-600 hover:underline">
                                            Download
                                        </a>
                                        <form
                                            action="{{ route('sales_orders.documents.destroy', [$doc->salesOrder->id, $doc->id]) }}"
                                            method="POST" class="inline"
                                            onsubmit="return confirm('Yakin ingin hapus dokumen ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center p-4">Belum ada dokumen</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $documents->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection
