@extends('layouts.app')
@section('content')
    {{-- Isi Halaman --}}
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100">

                {{-- Header --}}
                <div
                    class="sticky-header px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex justify-between items-center rounded-t-xl">
                    <h3 class="text-lg font-semibold text-blue-700 flex items-center">
                        <i class="fas fa-sitemap mr-2 text-blue-500"></i> Detail Purchase Options
                    </h3>
                </div>

                {{-- Konten --}}
                <div class="px-8 py-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-100 shadow-sm">
                            <h4 class="font-semibold text-blue-700 mb-2 flex items-center">
                                <i class="fas fa-hourglass-start mr-2 text-blue-500"></i> Aging Periods (Hari)
                            </h4>
                            <ul class="text-sm text-gray-700 list-disc pl-5 space-y-1">
                                <li><strong>First:</strong> {{ $data->aging_first_period }} hari</li>
                                <li><strong>Second:</strong> {{ $data->aging_second_period }} hari</li>
                                <li><strong>Third:</strong> {{ $data->aging_third_period }} hari</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Custom style (opsional) --}}
    <style>
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(5px);
            background-color: rgba(255, 255, 255, 0.95);
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
    </style>
@endsection
