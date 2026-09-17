@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">Manajemen Permissions per Role</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="accordion" id="accordionRoles">
            @foreach ($roles as $role)
                <div class="accordion-item mb-3">
                    <h2 class="accordion-header" id="heading{{ $role->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse{{ $role->id }}" aria-expanded="false"
                            aria-controls="collapse{{ $role->id }}">
                            {{ ucfirst($role->name) }}
                        </button>
                    </h2>
                    <div id="collapse{{ $role->id }}" class="accordion-collapse collapse"
                        aria-labelledby="heading{{ $role->id }}" data-bs-parent="#accordionRoles">
                        <div class="accordion-body">
                            <form action="{{ route('roles.permissions.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role_id" value="{{ $role->id }}">

                                <div class="row">
                                    @foreach ($permissions as $permission)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                                    value="{{ $permission->name }}"
                                                    id="perm_{{ $role->id }}_{{ $permission->id }}"
                                                    {{ $role->permissions->contains('name', $permission->name) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="perm_{{ $role->id }}_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-primary btn-sm">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
