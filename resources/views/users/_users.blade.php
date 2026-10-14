@php
    $themeColor = \App\Setting::get('theme_color', '#D552A3');

    // Siapkan payload data users untuk Alpine.js
    $usersPayload = $users->map(function($u) {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role_id' => $u->role_id ?? ($u->roles->first()->id ?? ''),
            'role_name' => $u->role->name ?? ($u->roles->first()->name ?? '-'),
            'employee_name' => $u->employee->nama_karyawan ?? null,
            'employee_id' => $u->employee->id ?? null,
        ];
    });

    // Helper avatar inisial
    function getInitials($name) {
        $words = explode(' ', trim($name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
        }
        return $initials ?: 'U';
    }
@endphp

<div x-data="userManager({{ $usersPayload->toJson() }})" class="space-y-6">

    <!-- Card Container Daftar User -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Top Accent Bar -->
        <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #8b5cf6 0%, {{ $themeColor }} 100%);"></div>

        <!-- Header & Toolbar -->
        <div class="p-5 md:p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shadow-xs font-bold text-xs" style="background-color: {{ $themeColor }}">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Daftar Akun Pengguna</h2>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola akun login sistem, penautan karyawan, dan penugasan peran (role).
                </p>
            </div>

            <div class="flex items-center gap-3">
                <!-- Search Box -->
                <div class="relative w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama, email, role..."
                        class="w-full pl-8 pr-3 py-1.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:ring-2 bg-white">
                </div>

                <!-- Tambah User Button -->
                <button type="button" @click="openCreateUser()"
                    style="background-color: {{ $themeColor }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs md:text-sm font-semibold text-white shadow-sm hover:opacity-95 active:scale-95 transition-all shrink-0">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Tambah User</span>
                </button>
            </div>
        </div>

        <!-- Tabel User -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50/80 text-xs uppercase font-semibold text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4">Nama Pengguna</th>
                        <th class="px-6 py-4">Alamat Email</th>
                        <th class="px-6 py-4">Peran (Role)</th>
                        <th class="px-6 py-4">Karyawan Terkait</th>
                        <th class="px-6 py-4 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <!-- Nama Pengguna & Avatar -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs text-white shadow-xs shrink-0"
                                        style="background: linear-gradient(135deg, {{ $themeColor }} 0%, #8b5cf6 100%);">
                                        <span x-text="getInitials(user.name)"></span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm" x-text="user.name"></div>
                                        <span class="text-[11px] text-gray-400 font-mono" x-text="'ID #' + user.id"></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-gray-700">
                                    <i class="fa-regular fa-envelope text-gray-400 text-xs"></i>
                                    <span class="text-xs font-medium" x-text="user.email"></span>
                                </div>
                            </td>

                            <!-- Role Badge -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                                    :class="user.role_name.toLowerCase() === 'admin' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-purple-100 text-purple-800 border border-purple-200'">
                                    <i class="fa-solid text-[10px]" :class="user.role_name.toLowerCase() === 'admin' ? 'fa-crown text-amber-600' : 'fa-shield-halved text-purple-600'"></i>
                                    <span x-text="user.role_name"></span>
                                </span>
                            </td>

                            <!-- Employee Terkait -->
                            <td class="px-6 py-4">
                                <template x-if="user.employee_name">
                                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md font-medium border border-emerald-200">
                                        <i class="fa-solid fa-id-badge text-emerald-500"></i>
                                        <span x-text="user.employee_name"></span>
                                    </span>
                                </template>
                                <template x-if="!user.employee_name">
                                    <span class="text-xs text-gray-400 italic">Bukan Karyawan</span>
                                </template>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right space-x-1">
                                <button type="button" @click="openEditUser(user)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100 text-xs font-semibold transition-all">
                                    <i class="fa-solid fa-pen-to-square text-gray-500"></i>
                                    <span>Edit</span>
                                </button>
                                <button type="button" @click="confirmDeleteUser(user)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition-all">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus</span>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL DIALOG TAMBAH & EDIT USER                                      -->
    <!-- ===================================================================== -->
    <div x-show="userModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop Blur -->
        <div x-show="userModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeUserModal()"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="userModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                
                <form :action="userFormAction" method="POST" class="m-0 p-0">
                    @csrf
                    <template x-if="isEditUser">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Modal Header -->
                    <div class="p-5 border-b border-gray-200 bg-gray-50/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-xs" style="background-color: {{ $themeColor }}">
                                <i class="fa-solid" :class="isEditUser ? 'fa-user-pen' : 'fa-user-plus'"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900" x-text="isEditUser ? 'Edit Akun Pengguna' : 'Tambah Pengguna Baru'"></h3>
                                <p class="text-xs text-gray-500">Isi data akun pengguna dan tentukan peran aksesnya.</p>
                            </div>
                        </div>
                        <button type="button" @click="closeUserModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-200/60 font-bold transition-colors">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <div class="p-6 space-y-4">
                        
                        <!-- Pilihan Employee (Hanya saat Tambah User) -->
                        <template x-if="!isEditUser">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                    Pilih Karyawan (Opsional)
                                </label>
                                <select @change="onEmployeeSelect($event)" name="employee_id" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 bg-white">
                                    <option value="">-- Pilih Karyawan (Auto-fill Nama & Email) --</option>
                                    @foreach ($employee as $emp)
                                        <option value="{{ $emp->id }}" data-nama="{{ $emp->nama_karyawan }}" data-email="{{ $emp->email }}">
                                            {{ $emp->nama_karyawan }} ({{ $emp->email ?: 'Tanpa Email' }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-gray-400 mt-1">Memilih karyawan akan otomatis mengisi nama dan email di bawah.</p>
                            </div>
                        </template>

                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" x-model="userName" required placeholder="Contoh: Budi Setiawan"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 bg-white">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" x-model="userEmail" required placeholder="nama@rembulancitraabadi.com"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 bg-white">
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                Kata Sandi (Password)
                                <template x-if="!isEditUser"><span class="text-rose-500">*</span></template>
                                <template x-if="isEditUser"><span class="text-gray-400 font-normal lowercase">(kosongkan jika tidak diubah)</span></template>
                            </label>
                            <input type="password" name="password" :required="!isEditUser" placeholder="Minimal 6 karakter"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 bg-white">
                        </div>

                        <!-- Role Selection -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                Peran Akun (Role)
                            </label>
                            <select name="role_id" x-model="userRoleId" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 bg-white">
                                <option value="">-- Pilih Role Akses --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="p-5 border-t border-gray-200 bg-gray-50/80 flex items-center justify-between">
                        <button type="button" @click="closeUserModal()"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-100 transition-all">
                            Batal
                        </button>
                        <button type="submit"
                            style="background-color: {{ $themeColor }}"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:opacity-95 active:scale-95 transition-all">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span x-text="isEditUser ? 'Simpan Perubahan' : 'Buat User Baru'"></span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Hidden form for deleting user -->
    <form id="delete-user-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>

<script>
function userManager(initialUsers) {
    return {
        usersList: initialUsers || [],
        searchQuery: '',
        userModalOpen: false,
        isEditUser: false,
        userId: null,
        userName: '',
        userEmail: '',
        userRoleId: '',
        userFormAction: '{{ route('users.store') }}',

        get filteredUsers() {
            if (!this.searchQuery) return this.usersList;
            const q = this.searchQuery.toLowerCase().trim();
            return this.usersList.filter(u => {
                return (u.name && u.name.toLowerCase().includes(q)) ||
                       (u.email && u.email.toLowerCase().includes(q)) ||
                       (u.role_name && u.role_name.toLowerCase().includes(q)) ||
                       (u.employee_name && u.employee_name.toLowerCase().includes(q));
            });
        },

        getInitials(name) {
            if (!name) return 'U';
            const parts = name.trim().split(' ');
            let res = '';
            for (let i = 0; i < Math.min(parts.length, 2); i++) {
                if (parts[i].length > 0) res += parts[i][0].toUpperCase();
            }
            return res || 'U';
        },

        openCreateUser() {
            this.isEditUser = false;
            this.userId = null;
            this.userName = '';
            this.userEmail = '';
            this.userRoleId = '';
            this.userFormAction = '{{ route('users.store') }}';
            this.userModalOpen = true;
        },

        openEditUser(user) {
            this.isEditUser = true;
            this.userId = user.id;
            this.userName = user.name;
            this.userEmail = user.email;
            this.userRoleId = user.role_id;
            this.userFormAction = '/users/' + user.id;
            this.userModalOpen = true;
        },

        closeUserModal() {
            this.userModalOpen = false;
        },

        onEmployeeSelect(event) {
            const select = event.target;
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const nama = selectedOpt.getAttribute('data-nama');
                const email = selectedOpt.getAttribute('data-email');
                if (nama) this.userName = nama;
                if (email) this.userEmail = email;
            }
        },

        confirmDeleteUser(user) {
            Swal.fire({
                title: 'Hapus User "' + user.name + '"?',
                text: 'Akun user ini akan dihapus dari sistem. Karyawan yang terhubung akan dilepaskan status user-nya.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus User',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-user-form');
                    form.action = '/users/' + user.id;
                    form.submit();
                }
            });
        }
    };
}
</script>
