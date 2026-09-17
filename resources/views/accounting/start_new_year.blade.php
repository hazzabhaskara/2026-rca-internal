@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Proses Start New Year</h1>

        <button id="btnStartNewYear" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Mulai Start New Year
        </button>

        <div id="result" class="mt-4"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.getElementById('btnStartNewYear').addEventListener('click', function() {
            Swal.fire({
                title: 'Yakin ingin melakukan proses Start New Year?',
                text: "Pastikan data sudah benar!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, mulai proses',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                this.disabled = true;
                this.textContent = 'Memproses...';

                axios.post('{{ route('accounting.start_new_year_proses') }}')
                    .then(response => {
                        const res = response.data;
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message,
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Server',
                            text: error.message || error,
                        });
                    })
                    .finally(() => {
                        this.disabled = false;
                        this.textContent = 'Mulai Start New Year';
                    });
            });
        });
    </script>
@endsection
