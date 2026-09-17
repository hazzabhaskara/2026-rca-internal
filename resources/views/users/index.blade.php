@extends('layouts.app')

@section('content')
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8" x-data="{ tab: '{{ $activeTab }}' }">
            <!-- Tabs -->
            <div class="mb-6 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button @click="tab = 'roles'"
                        :class="tab === 'roles' ? 'border-blue-500 text-blue-600' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        Roles
                    </button>
                    <button @click="tab = 'users'"
                        :class="tab === 'users' ? 'border-blue-500 text-blue-600' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        Users
                    </button>
                </nav>
            </div>

            <!-- Tab Content: Users -->
            <div x-show="tab === 'users'">
                @if (session('success') && $activeTab == 'users')
                    <div class="mb-4 text-green-600">{{ session('success') }}</div>
                @endif

                @include('users._users')
            </div>

            <!-- Tab Content: Roles -->
            <div x-show="tab === 'roles'">
                @if (session('success') && $activeTab == 'roles')
                    <div class="mb-4 text-green-600">{{ session('success') }}</div>
                @endif

                @include('users._roles')
            </div>
        </div>
    </div>



    <style>
        /* Improved sticky header implementation */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(5px);
            background-color: rgba(255, 255, 255, 0.9);
        }

        /* Table header sticky positioning */
        table thead {
            position: sticky;
            top: 68px;
            /* Height of the sticky-header */
            z-index: 10;
        }

        /* Beautiful scrollbar */
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

        /* Smooth transitions */
        tr {
            transition: background-color 0.2s ease;
        }
    </style>
@endsection
