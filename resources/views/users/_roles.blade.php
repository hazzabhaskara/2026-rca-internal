  @php
      $themeColor = \App\Setting::get('theme_color', '#4F46E5');
  @endphp
  <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
      <h3 class="text-xl font-semibold mb-4">Manajemen Role</h3>

      @if (session('success'))
          <div class="mb-4 text-green-600">{{ session('success') }}</div>
      @endif

      <!-- Form Tambah Role -->
      <form action="{{ route('roles.store') }}" method="POST" class="mb-6 permission-wrapper">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                  <label>Nama Role</label>
                  <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
              </div>
              <div>
                  <input type="hidden" name="guard_name" value="web">
              </div>
          </div>

          @php
              $grouped = [];
              foreach ($permissions as $p) {
                  $parts = explode('.', $p->name);
                  $grouped[$parts[0]][] = $p;
              }
          @endphp

          <div class="mt-4 space-y-4">
              {{-- Global controls --}}
              <div class="flex items-center justify-between">
                  <input type="text"
                      class="search-permission border px-3 py-2 w-full rounded mr-3 focus:ring focus:ring-blue-200"
                      placeholder="Cari permission...">
                  <label class="flex items-center gap-2 whitespace-nowrap">
                      <input type="checkbox" class="select-all-permissions">
                      <span class="text-sm font-medium">Pilih Semua</span>
                  </label>
              </div>

              @foreach ($grouped as $module => $perms)
                  <div class="border rounded p-3 bg-gray-50 module-block">
                      <h4 class="font-semibold text-sm mb-2 capitalize flex items-center">
                          {{ $module }}
                          <label class="flex items-center gap-2 ml-3 text-xs font-normal">
                              <input type="checkbox" class="select-module">
                              <span>Pilih semua modul</span>
                          </label>
                      </h4>
                      <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                          @foreach ($perms as $permission)
                              <label class="flex items-center space-x-2 permission-label"
                                  data-fullname="{{ $permission->name }}">
                                  <input type="checkbox" class="permission" name="permissions[]"
                                      value="{{ $permission->name }}">
                                  <span class="permission-text">
                                      {{ Str::after($permission->name, $module . '.') }}
                                  </span>
                              </label>
                          @endforeach
                      </div>
                  </div>
              @endforeach
          </div>

          <div class="mt-4">
              <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
          </div>
      </form>

      <!-- Tabel Role -->
      <table class="w-full table-auto text-left">
          <thead>
              <tr>
                  <th class="border-b py-2">Nama</th>
                  <th class="border-b py-2">Deskripsi</th>
                  <th class="border-b py-2">Aksi</th>
              </tr>
          </thead>
          <tbody>
              @foreach ($roles as $role)
                  <tr>
                      <td class="py-2">{{ $role->name }}</td>
                      <td class="py-2">{{ $role->guard_name }}</td>
                      <td class="py-2 space-x-2">
                          <button type="button"
                              onclick="document.getElementById('edit-{{ $role->id }}').classList.toggle('hidden')"
                              class="text-blue-500">Edit</button>
                          <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Yakin ingin menghapus?')">
                              @csrf @method('DELETE')
                              <button class="text-red-500">Hapus</button>
                          </form>
                      </td>
                  </tr>

                  <!-- Form Edit Role -->
                  <tr id="edit-{{ $role->id }}" class="hidden">
                      <td colspan="3">
                          <form action="{{ route('roles.update', $role->id) }}" method="POST"
                              class="mt-2 bg-gray-100 p-4 rounded permission-wrapper">
                              @csrf @method('PUT')
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                  <div>
                                      <label>Nama</label>
                                      <input type="text" name="name" value="{{ $role->name }}"
                                          class="w-full border rounded px-3 py-2">
                                  </div>
                                  <div>
                                      <label>Jenis Pengguna</label>
                                      <input type="text" name="guard_name" value="{{ $role->guard_name }}"
                                          class="w-full border rounded px-3 py-2">
                                  </div>
                              </div>

                              <div class="mt-4 space-y-4">
                                  <div class="flex items-center justify-between">
                                      <input type="text"
                                          class="search-permission border px-3 py-2 w-full rounded mr-3 focus:ring focus:ring-blue-200"
                                          placeholder="Cari permission...">
                                      <label class="flex items-center gap-2 whitespace-nowrap">
                                          <input type="checkbox" class="select-all-permissions">
                                          <span class="text-sm font-medium">Pilih Semua</span>
                                      </label>
                                  </div>

                                  @foreach ($grouped as $module => $perms)
                                      <div class="border rounded p-3 bg-gray-50 module-block">
                                          <h4 class="font-semibold text-sm mb-2 capitalize flex items-center">
                                              {{ $module }}
                                              <label class="flex items-center gap-2 ml-3 text-xs font-normal">
                                                  <input type="checkbox" class="select-module">
                                                  <span>Pilih semua modul</span>
                                              </label>
                                          </h4>
                                          <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                              @foreach ($perms as $permission)
                                                  <label class="flex items-center space-x-2 permission-label"
                                                      data-fullname="{{ $permission->name }}">
                                                      <input type="checkbox" class="permission" name="permissions[]"
                                                          value="{{ $permission->name }}"
                                                          {{ $role->permissions->contains('name', $permission->name) ? 'checked' : '' }}>
                                                      <span class="permission-text">
                                                          {{ Str::after($permission->name, $module . '.') }}
                                                      </span>
                                                  </label>
                                              @endforeach
                                          </div>
                                      </div>
                                  @endforeach
                              </div>

                              <div class="mt-4">
                                  <button class="bg-green-600 text-white px-4 py-2 rounded">Update</button>
                              </div>
                          </form>
                      </td>
                  </tr>
              @endforeach
          </tbody>
      </table>
  </div>

  {{-- JS --}}
  <script>
      document.querySelectorAll('.permission-wrapper').forEach(wrapper => {
          const searchInput = wrapper.querySelector('.search-permission');
          const globalSelect = wrapper.querySelector('.select-all-permissions');

          // Search filter per form
          searchInput.addEventListener('input', function() {
              const q = this.value.toLowerCase();
              wrapper.querySelectorAll('.module-block').forEach(block => {
                  let visible = 0;
                  block.querySelectorAll('.permission-label').forEach(label => {
                      const txt = label.querySelector('.permission-text').textContent
                          .toLowerCase();
                      const full = (label.dataset.fullname || '').toLowerCase();
                      const match = txt.includes(q) || full.includes(q);
                      label.style.display = match ? 'flex' : 'none';
                      if (match) visible++;
                  });
                  block.style.display = visible > 0 ? 'block' : 'none';
              });
          });

          // Select all per modul
          wrapper.querySelectorAll('.select-module').forEach(modCb => {
              modCb.addEventListener('change', function() {
                  const block = this.closest('.module-block');
                  block.querySelectorAll('input.permission').forEach(p => p.checked = this
                      .checked);
                  syncGlobal();
              });
          });

          // Select all global
          globalSelect.addEventListener('change', function() {
              const checked = this.checked;
              wrapper.querySelectorAll('input.permission').forEach(p => p.checked = checked);
              wrapper.querySelectorAll('.select-module').forEach(m => m.checked = checked);
          });

          // Sinkronisasi
          function syncModules() {
              wrapper.querySelectorAll('.module-block').forEach(block => {
                  const perms = Array.from(block.querySelectorAll('input.permission'));
                  const allChecked = perms.length > 0 && perms.every(p => p.checked);
                  const modCb = block.querySelector('.select-module');
                  if (modCb) modCb.checked = allChecked;
              });
          }

          function syncGlobal() {
              const allPerms = Array.from(wrapper.querySelectorAll('input.permission'));
              globalSelect.checked = allPerms.length > 0 && allPerms.every(p => p.checked);
          }

          // Inisialisasi
          syncModules();
          syncGlobal();

          wrapper.querySelectorAll('input.permission').forEach(p => {
              p.addEventListener('change', () => {
                  syncModules();
                  syncGlobal();
              });
          });
      });
  </script>
