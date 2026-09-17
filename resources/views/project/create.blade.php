@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="py-8">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="font-bold text-lg mb-4">Specpose Create</h2>
                    <form action="{{ route('project.store') }}" method="POST">
                        @csrf

                        {{-- Error Validation --}}
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

                        {{-- Form Fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 text-base">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Project</label>
                                <input type="text" name="nama_project"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2" value="{{ old('project') }}"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Start Date</label>
                                <input type="date" name="start_date"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2"
                                    value="{{ old('start_date') }}" required>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">End Date</label>
                                <input type="date" name="end_date"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2"
                                    value="{{ old('end_date') }}">
                            </div>
                        </div>

                        {{-- Balance Forward Section --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Balance Forward</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="revenue" class="block text-sm text-gray-600 mb-1">Revenue</label>
                                    <input type="text" name="revenue" id="revenue"
                                        class="w-full number-format rounded-md border border-gray-300 px-3 py-2"
                                        value="{{ old('revenue') }}">
                                </div>
                                <div>
                                    <label for="expense" class="block text-sm text-gray-600 mb-1">Expense</label>
                                    <input type="text" name="expens" id="expense"
                                        class="w-full number-format rounded-md border border-gray-300 px-3 py-2"
                                        value="{{ old('expens') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="md:col-span-1">
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" required
                                class="w-1/3 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="In Progress" {{ old('status') == 'In Progress' ? 'selected' : '' }}>In
                                    Progress</option>
                                <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                                <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed
                                </option>
                            </select>
                            @error('status')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="mt-6 flex justify-end gap-4">
                            <a href="{{ route('project.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md">
                                <i class="fas fa-arrow-left mr-2"></i> Batal
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-md hover:bg-indigo-700">
                                <i class="fas fa-save mr-2"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection

    <!-- Optional: Select2 CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.number-format');

            inputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    this.value = new Intl.NumberFormat('id-ID').format(value);
                });
            });
        });
    </script>
