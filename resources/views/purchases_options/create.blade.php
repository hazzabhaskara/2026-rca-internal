@extends('layouts.app')
@section('content')
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-xl p-8 border border-gray-200">
                <form action="{{ route('purchases_options.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Aging Period -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">📅 Aging Period (dalam hari)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="aging_first_period" class="block text-sm font-medium text-gray-600">First
                                    Period</label>
                                <input type="number" name="aging_first_period" id="aging_first_period"
                                    class="form-input mt-1 block w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="contoh: 30">
                            </div>
                            <div>
                                <label for="aging_second_period" class="block text-sm font-medium text-gray-600">Second
                                    Period</label>
                                <input type="number" name="aging_second_period" id="aging_second_period"
                                    class="form-input mt-1 block w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="contoh: 60">
                            </div>
                            <div>
                                <label for="aging_third_period" class="block text-sm font-medium text-gray-600">Third
                                    Period</label>
                                <input type="number" name="aging_third_period" id="aging_third_period"
                                    class="form-input mt-1 block w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="contoh: 90">
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="pt-4">
                        <a href="{{ route('purchases_options.index') }}"
                            class="px-6 py-2 mr-3 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Kembali
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            💾 Simpan Options
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
