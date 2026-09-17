@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">Manajemen Permissions</h4>

        {{-- Flash Message --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Form Tambah Permission --}}
        <form action="{{ route('permissions.store') }}" method="POST" class="mb-4">
            @csrf
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="name" class="form-control" placeholder="Masukkan nama permission" required>
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary">Tambah</button>
                </div>
            </div>
        </form>

        {{-- Tabel Permissions --}}
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Permission</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissions as $index => $permission)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <form action="{{ route('permissions.update', $permission->id) }}" method="POST" class="d-flex">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $permission->name }}"
                                    class="form-control form-control-sm me-2" required>
                                <button class="btn btn-sm btn-warning">Update</button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus permission ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach

                @if ($permissions->isEmpty())
                    <tr>
                        <td colspan="3" class="text-center">Belum ada permission.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection
