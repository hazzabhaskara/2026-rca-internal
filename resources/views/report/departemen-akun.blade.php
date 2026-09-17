@extends('layouts.app')

@section('content')
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Akun</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Akun</th>
                            @foreach ($departemens as $dept)
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    {{ $dept->deskripsi }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($akuns as $akun)
                            <tr>
                                <td class="px-3 py-1 whitespace-nowrap text-sm text-gray-600">{{ $akun->kode_akun }}
                                </td>
                                <td class="px-3 py-1 whitespace-nowrap text-sm text-gray-900">{{ $akun->nama_akun }}
                                </td>
                                @foreach ($departemens as $dept)
                                    <td class="px-3 py-1 text-center text-sm">
                                        @if (isset($relasiMap[$akun->id][$dept->id]))
                                            <span class="text-green-500 font-bold">✓</span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
