@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <!-- Sticky Card Header -->
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp

                <div class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 flex justify-between items-center"
                    style="background: {{ $themeColor }};">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3 text-white text-xl"></i>
                        Inventory & Service
                    </h3>
                    @can('inventory.create')
                        <a href="{{ route('inventory.create') }}"
                            class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                            <i class="fas fa-plus mr-2"></i> Add Inventory & Service
                        </a>
                    @endcan

                </div>

                <div class="bg-white shadow-md rounded overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium">Item Number</th>
                                <th class="px-6 py-3 text-left text-sm font-medium"> Item Description</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Type</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Description</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Picture Path</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Thumbnail Path</th>
                                <th class="px-6 py-3 text-right text-sm font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($items as $item)
                                <tr>
                                    <td class="px-4 py-2 text-left">{{ $item->item_number }}</td>
                                    <td class="px-4 py-2 text-left">{{ $item->item_description ?? '-' }}</td>
                                    <td class="px-4 py-2 text-left">{{ strtoupper($item->type) }}</td>
                                    <td class="px-4 py-2 text-left">{{ $item->description }}</td>
                                    <td class="px-4 py-2 text-left">{{ $item->picture_path }}</td>
                                    <td class="px-4 py-2 text-left">{{ $item->thumbnail_path }}</td>

                                    <td class="px-4 py-2 text-right">
                                        <div class="flex justify-end space-x-3">
                                            {{-- @can('inventory.view')
                                                <a href="{{ route('inventory.show', $item->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50 transition-colors"
                                                    title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan --}}
                                            @can('inventory.update')
                                                <a href="{{ route('inventory.edit', $item->id) }}"
                                                    class="text-yellow-500 hover:text-yellow-700 p-2 rounded-full hover:bg-yellow-50 transition-colors"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('inventory.delete')
                                                <form id="delete-form-{{ $item->id }}"
                                                    action="{{ route('inventory.destroy', $item->id) }}" method="POST"
                                                    style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>

                                                <button type="button" onclick="confirmDelete({{ $item->id }})"
                                                    class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition-colors"
                                                    title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Belum ada data inventory.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="p-4 bg-white border-t">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
