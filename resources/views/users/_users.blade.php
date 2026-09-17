  @php
      $themeColor = \App\Setting::get('theme_color', '#4F46E5');
  @endphp
  <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
      <h3 class="text-xl font-semibold mb-4">Manajemen User</h3>

      @if (session('success'))
          <div class="mb-4 text-green-600">{{ session('success') }}</div>
      @endif

      @if ($errors->any())
          <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
              <ul class="list-disc list-inside">
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif

      <!-- Form Tambah User -->
      <form action="{{ route('users.store') }}" method="POST" class="mb-6">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                  <div>
                      <label>Employee</label>
                      <select id="employeeSelect" name="employee_id"
                          class="select-employee w-full border rounded px-3 py-2">
                          <option value="">- Pilih Employee -</option>
                          @foreach ($employee as $emp)
                              <option value="{{ $emp->id }}" data-nama="{{ $emp->nama_karyawan }}"
                                  data-email="{{ $emp->email }}">
                                  {{ $emp->nama_karyawan }}
                              </option>
                          @endforeach
                      </select>

                  </div>
              </div>
              <div>
                  <label>Nama</label>
                  <input type="text" name="name" id="nameInput" class="w-full border rounded px-3 py-2" required>
              </div>
              <div>
                  <label>Email</label>
                  <input type="email" name="email" id="emailInput" class="w-full border rounded px-3 py-2" required>
              </div>
              <div>
                  <label>Password</label>
                  <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
              </div>
              <div>
                  <label>Role</label>
                  <select name="role_id" class="w-full border rounded px-3 py-2">
                      <option value="">- Pilih Role -</option>
                      @foreach ($roles as $role)
                          <option value="{{ $role->id }}">{{ $role->name }}</option>
                      @endforeach
                  </select>
              </div>
          </div>
          <div class="mt-4">
              <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Simpan</button>
          </div>
      </form>

      <!-- Tabel User -->
      <table class="w-full table-auto text-left">
          <thead>
              <tr>
                  <th class="border-b py-2">Nama</th>
                  <th class="border-b py-2">Email</th>
                  <th class="border-b py-2">Role</th>
                  <th class="border-b py-2">Aksi</th>
              </tr>
          </thead>
          <tbody>
              @foreach ($users as $user)
                  <tr>
                      <td class="py-2">{{ $user->name }}</td>
                      <td class="py-2">{{ $user->email }}</td>
                      <td class="py-2">{{ $user->role->name ?? '-' }}</td>
                      <td class="py-2 space-x-2">
                          <button
                              @click="document.getElementById('edit-user-{{ $user->id }}').classList.toggle('hidden')"
                              class="text-blue-500">Edit</button>
                          <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                              @csrf @method('DELETE')
                              <button class="text-red-500">Hapus</button>
                          </form>
                      </td>
                  </tr>

                  <!-- Form Edit -->
                  <tr id="edit-user-{{ $user->id }}" class="hidden">
                      <td colspan="4">
                          <form action="{{ route('users.update', $user->id) }}" method="POST"
                              class="mt-2 bg-gray-100 p-4 rounded">
                              @csrf @method('PUT')
                              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                  <div>
                                      <label>Nama</label>
                                      <input type="text" name="name" value="{{ $user->name }}"
                                          class="w-full border rounded px-3 py-2">
                                  </div>
                                  <div>
                                      <label>Email</label>
                                      <input type="email" name="email" value="{{ $user->email }}"
                                          class="w-full border rounded px-3 py-2">
                                  </div>
                                  <div>
                                      <label>Password <small>(Kosongkan jika tidak diubah)</small></label>
                                      <input type="password" name="password" class="w-full border rounded px-3 py-2">
                                  </div>
                                  <div>
                                      <label>Role</label>
                                      <select name="role_id" class="w-full border rounded px-3 py-2">
                                          <option value="">- Pilih Role -</option>
                                          @foreach ($roles as $role)
                                              <option value="{{ $role->id }}"
                                                  {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                  {{ $role->name }}
                                              </option>
                                          @endforeach
                                      </select>
                                  </div>
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

  {{-- Select2 CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  {{-- Select2 JS --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
      $(document).ready(function() {
          $('.select-employee').select2({
              placeholder: "Search employee...",
              allowClear: true
          });
      });
  </script>

  <script>
      document.getElementById('employeeSelect').addEventListener('change', function() {
          const selectedOption = this.options[this.selectedIndex];
          const name = selectedOption.getAttribute('data-nama');
          const email = selectedOption.getAttribute('data-email');

          document.getElementById('nameInput').value = name || '';
          document.getElementById('emailInput').value = email || '';
      });
  </script>
