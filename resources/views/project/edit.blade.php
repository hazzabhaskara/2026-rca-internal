@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="font-bold text-lg mb-4">Specpose Edit</h2>

                <form action="{{ route('project.update', $project->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                        <div>
                            <label for="nama_project" class="block text-sm font-medium text-gray-700">Project</label>
                            <input type="text" name="nama_project" id="nama_project_project"
                                value="{{ old('nama_project', $project->nama_project) }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('nama_project')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date"
                            value="{{ old('start_date', $project->start_date) }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('start_date')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date"
                            value="{{ old('end_date', $project->end_date) }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('end_date')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="revenue" class="block text-sm font-medium text-gray-700">Revenue</label>
                        <input type="text" name="revenue" id="revenue" value="{{ old('revenue', $project->revenue) }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('revenue')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="expens" class="block text-sm font-medium text-gray-700">Expense</label>
                        <input type="text" name="expens" id="expens" value="{{ old('expens', $project->expens) }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('expens')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-gray-700 font-medium mb-1">Status
                            Komponen</label>
                        <select name="status" id="status" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih --</option>
                            <option value="Pending" {{ old('status', $project->status) == 'Pending' ? 'selected' : '' }}>
                                Pending</option>
                            <option value="In Progress"
                                {{ old('status', $project->status) == 'In Progress' ? 'selected' : '' }}>
                                In Progress</option>
                            <option value="Cancelled"
                                {{ old('status', $project->status) == 'Cancelled' ? 'selected' : '' }}>
                                Cancelled</option>
                            <option value="Completed"
                                {{ old('status', $project->status) == 'Completed' ? 'selected' : '' }}>
                                Completed</option>
                        </select>
                    </div>


                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" onclick="history.go(-1)"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
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
