@php use App\Setting; @endphp

@extends('layouts.app')

@section('content')
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <div
                    class="px-6 py-4 border-b border-gray-100 bg-white sticky-header flex flex-col md:flex-row justify-between items-start md:items-center gap-4">

                    <div class="flex flex-wrap gap-2">
                        <!-- File Button -->
                        <button onclick="document.getElementById('fileModal').classList.remove('hidden')"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-export text-blue-500 mr-2"></i> File
                        </button>

                    </div>
                </div>
                <div class="relative overflow-x-auto" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <table class="min-w-full divide-y">
                        <thead class="bg-gray-50 sticky top-0 z-10"
                            style="background-color: {{ Setting::get('theme_color', '#4F46E5') }}">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Kode Akun</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Nama Akun</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Tipe Akun</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Level Akun</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($chartOfAccounts as $account)
                                @php
                                    $margin = $account->level_indent * 20;

                                    // Parent key untuk JavaScript toggle
                                    switch ($account->level_indent) {
                                        case 1:
                                            $parentKode = substr($account->kode_akun, 0, 1);
                                            break;
                                        case 2:
                                            $parentKode = substr($account->kode_akun, 0, 2);
                                            break;
                                        case 3:
                                            $parentKode = substr($account->kode_akun, 0, 3);
                                            break;
                                        default:
                                            $parentKode = '';
                                    }

                                    // Warna latar sesuai level
                                    switch ($account->level_indent) {
                                        case 0:
                                            $rowBg = 'bg-gray-100';
                                            break;
                                        case 1:
                                            $rowBg = 'bg-blue-50';
                                            break;
                                        case 2:
                                            $rowBg = 'bg-green-50';
                                            break;
                                        case 3:
                                            $rowBg = 'bg-white';
                                            break;
                                        default:
                                            $rowBg = 'bg-white';
                                            break;
                                    }
                                @endphp
                                <tr class="akun-row {{ $rowBg }}" data-kode="{{ $account->kode_akun }}"
                                    data-level="{{ strtolower($account->level_akun) }}"
                                    data-parent="{{ $account->parent_prefix ?? '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if ($account->has_child)
                                                <button class="mr-1 text-blue-600 font-bold"
                                                    onclick="toggleChild('{{ $account->kode_akun }}')"
                                                    id="btn-{{ $account->kode_akun }}">➕</button>
                                            @else
                                                <span class="mr-4"></span>
                                            @endif
                                            <span>{{ $account->kode_akun }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center" style="margin-left: {{ $margin }}px">
                                            {{ $account->nama_akun }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $account->tipe_akun }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $account->level_akun }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
                <a href="{{ asset('template/template_chart_of_account.xlsx') }}" download
                    class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-excel mr-2 text-green-600"></i> Download Template Excel
                </a>
                <a href="{{ route('export.chartOfAccount') }}" class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-download mr-2 text-blue-500"></i> Export Account
                </a>
                <form action="{{ route('import.chartOfAccount') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-2">
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

    <!-- JS for dropdown toggle -->
    <script>
        document.getElementById('menu-button').addEventListener('click', function() {
            document.getElementById('dropdown-menu').classList.toggle('hidden');
        });

        window.addEventListener('click', function(e) {
            const button = document.getElementById('menu-button');
            const menu = document.getElementById('dropdown-menu');
            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
    <script>
        function toggleChild(kodeInduk) {
            const btn = document.getElementById('btn-' + kodeInduk);
            const isExpand = btn.innerText === '➕';

            // Cari semua row dengan parent = prefix kodeInduk
            const children = document.querySelectorAll(`tr[data-parent^="${kodeInduk}"]`);

            children.forEach(row => {
                if (isExpand) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                    const kode = row.getAttribute('data-kode');
                    const childBtn = document.getElementById('btn-' + kode);
                    if (childBtn) childBtn.innerText = '➕';
                    collapseRecursive(kode);
                }
            });

            btn.innerText = isExpand ? '➖' : '➕';
        }

        function collapseRecursive(kodeInduk) {
            const children = document.querySelectorAll(`tr[data-parent^="${kodeInduk}"]`);
            children.forEach(row => {
                row.style.display = 'none';
                const kode = row.getAttribute('data-kode');
                const childBtn = document.getElementById('btn-' + kode);
                if (childBtn) childBtn.innerText = '➕';
                collapseRecursive(kode);
            });
        }

        window.addEventListener('DOMContentLoaded', () => {
            const rows = document.querySelectorAll('tr.akun-row');
            rows.forEach(row => {
                const level = row.getAttribute('data-level');
                if (level !== 'header') {
                    row.style.display = 'none';
                }
            });
        });
    </script>

    <style>
        table thead {
            position: sticky;
            top: 68px;
            z-index: 10;
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        tr {
            transition: background-color 0.2s ease;
        }
    </style>
@endsection
