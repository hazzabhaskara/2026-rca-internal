@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-xl mx-auto bg-white rounded-lg shadow-md p-6 border border-gray-100">

            <form method="POST" action="{{ route('setting_departement.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-3 mb-6">
                    <label class="flex items-center space-x-3">
                        <input type="radio" name="value" value="Accounting"
                            {{ $setting->value == 'Accounting' ? 'checked' : '' }}
                            class="form-radio text-indigo-600 h-5 w-5">
                        <span class="text-gray-700">Use Departmental Accounting</span>
                    </label>

                    <label class="flex items-center space-x-3">
                        <input type="radio" name="value" value="-" {{ $setting->value == '-' ? 'checked' : '' }}
                            class="form-radio text-red-500 h-5 w-5">
                        <span class="text-gray-700"> Do not use Departmental Accounting</span>
                    </label>
                </div>

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition duration-200">
                    Simpan
                </button>
            </form>
        </div>
    </div>
@endsection
