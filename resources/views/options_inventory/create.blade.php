@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto bg-white mt-6 p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-6">Inventory Options</h2>

        <form action="{{ route('options_inventory.store') }}" method="POST">
            @csrf

            <!-- Costing Method -->
            <div class="mb-4">
                <label class="block font-medium mb-2">Inventory Costing Method</label>
                <div class="flex items-center space-x-4">
                    <label>
                        <input type="radio" name="costing_method" value="average" required>
                        <span class="ml-1">Average Cost</span>
                    </label>
                    {{-- <label>
                        <input type="radio" name="costing_method" value="fifo">
                        <span class="ml-1">First In, First Out (FIFO)</span>
                    </label> --}}
                </div>
            </div>

            <!-- Profit Evaluation Method -->
            <div class="mb-4">
                <label class="block font-medium mb-2">Profit Evaluation Method</label>
                <div class="flex items-center space-x-4">
                    {{-- <label>
                        <input type="radio" name="profit_eval_method" value="markup" required>
                        <span class="ml-1">Markup</span>
                    </label> --}}
                    <label>
                        <input type="radio" name="profit_eval_method" value="margin">
                        <span class="ml-1">Margin</span>
                    </label>
                </div>
            </div>

            <!-- Sort Inventory & Service -->
            <div class="mb-4">
                <label class="block font-medium mb-2">Sort Inventory & Service By</label>
                <div class="flex items-center space-x-4">
                    <label>
                        <input type="radio" name="sort_inventory_service" value="number" required>
                        <span class="ml-1">Number</span>
                    </label>
                    <label>
                        <input type="radio" name="sort_inventory_service" value="description">
                        <span class="ml-1">Description</span>
                    </label>
                </div>
            </div>

            <!-- Allow below zero -->
            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="allow_below_zero" value="1">
                    <span class="ml-2">Allow inventory levels to go below zero</span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3">

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save
                </button>
            </div>
        </form>
    </div>
@endsection
