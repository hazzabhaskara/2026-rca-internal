@extends('layouts.app')

@section('content')
    <div class="max-w-full mt-6 mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-6">Edit Inventory Options</h2>

        <form action="{{ route('options_inventory.update', $option->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Costing Method -->
            <div class="mb-4">
                <label class="block font-medium mb-2">Inventory Costing Method</label>
                <div class="flex items-center space-x-4">
                    <label>
                        <input type="radio" name="costing_method" value="average"
                            {{ $option->costing_method == 'average' ? 'checked' : '' }}>
                        <span class="ml-1">Average Cost</span>
                    </label>
                </div>
            </div>

            <!-- Profit Evaluation Method -->
            <div class="mb-4">
                <label class="block font-medium mb-2">Profit Evaluation Method</label>
                <div class="flex items-center space-x-4">
                    <label>
                        <input type="radio" name="profit_eval_method" value="margin"
                            {{ $option->profit_eval_method == 'margin' ? 'checked' : '' }}>
                        <span class="ml-1">Margin</span>
                    </label>
                </div>
            </div>

            <!-- Sort Inventory & Service -->
            <div class="mb-4">
                <label class="block font-medium mb-2">Sort Inventory & Service By</label>
                <div class="flex items-center space-x-4">
                    <label>
                        <input type="radio" name="sort_inventory_service" value="number"
                            {{ $option->sort_inventory_service == 'number' ? 'checked' : '' }}>
                        <span class="ml-1">Number</span>
                    </label>
                    <label>
                        <input type="radio" name="sort_inventory_service" value="description"
                            {{ $option->sort_inventory_service == 'description' ? 'checked' : '' }}>
                        <span class="ml-1">Description</span>
                    </label>
                </div>
            </div>

            <!-- Allow below zero -->
            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="allow_below_zero" value="1"
                        {{ $option->allow_below_zero ? 'checked' : '' }}>
                    <span class="ml-2">Allow inventory levels to go below zero</span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update
                </button>
            </div>
        </form>
    </div>
@endsection
