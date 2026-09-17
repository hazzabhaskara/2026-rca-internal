<?php

namespace App\Http\Controllers;

use App\CompanyProfile;
use App\Kecamatan;
use App\Kelurahan;
use App\KotaIndonesia;
use App\LegalDocumentCompanyProfile;
use App\ProvinceIndonesia;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    //
    public function index()
    {
        $companyprofile = CompanyProfile::all();
        return view('company_profile.index', compact('companyprofile'));
    }
    public function create(): View
    {
        $provinsi = ProvinceIndonesia::select('id', 'name')->get();
        $kota = KotaIndonesia::select('id', 'name')->get();
        $kecamatan = Kecamatan::select('id', 'name')->get();
        $kelurahan = Kelurahan::select('id', 'name')->get();
        return view('company_profile.create', compact('provinsi', 'kota', 'kecamatan', 'kelurahan'));
    }
    public function store(Request $request): RedirectResponse
    {
        // ✅ Validasi input
        $this->validate($request, [
            'nama_perusahaan' => 'required|min:5',
            'jalan'           => 'nullable|string',
            'id_provinsi'     => 'nullable|exists:indonesia_provinces,id',
            'id_kota'         => 'nullable|exists:indonesia_cities,id',
            'id_kecamatan'    => 'nullable|exists:indonesia_districts,id',
            'id_kelurahan'    => 'nullable|exists:indonesia_villages,id',
            'kode_pos'        => 'nullable|string',
            'phone_number'    => 'nullable|string',
            'email'           => 'nullable|string',
            'logo'            => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

            // dokumen legal
            'nib'                     => 'nullable|mimes:pdf|max:2048',
            'akte_pendirian'          => 'nullable|mimes:pdf|max:2048',
            'akta_perubahan_terakhir' => 'nullable|mimes:pdf|max:2048',
            'bnri'                    => 'nullable|mimes:pdf|max:2048',
            'npwp_perusahaan'         => 'nullable|mimes:pdf|max:2048',
            'sppl'                    => 'nullable|mimes:pdf|max:2048',
            'sptataruang'             => 'nullable|mimes:pdf|max:2048',
            'ktp_pemegang_saham'      => 'nullable|mimes:pdf|max:2048',
            'k3l'                     => 'nullable|mimes:pdf|max:2048',
            'skkemenkumhan'           => 'nullable|mimes:pdf|max:2048',
        ]);

        // ✅ Upload logo
        $imageName = null;
        if ($request->hasFile('logo')) {
            $imageName = $request->file('logo')->hashName();
            $request->file('logo')->storeAs('public/informasi_perusahaan', $imageName);
        }

        // ✅ Simpan data perusahaan
        $perusahaan = CompanyProfile::create([
            'logo'           => $imageName,
            'nama_perusahaan' => $request->nama_perusahaan,
            'jalan'          => $request->jalan,
            'id_kelurahan'   => $request->id_kelurahan,
            'id_kecamatan'   => $request->id_kecamatan,
            'id_kota'        => $request->id_kota,
            'id_provinsi'    => $request->id_provinsi,
            'kode_pos'       => $request->kode_pos,
            'phone_number'   => $request->phone_number,
            'email'          => $request->email
        ]);

        // ✅ Upload dokumen legal
        $dokumens = [
            'akte_pendirian'          => 'Akte Pendirian',
            'akte_perubahan_terakhir' => 'Akte Perubahan Terakhir',
            'nib'                     => 'NIB',
            'skkemenkumhan'           => 'SKKEMENKUMHAN',
            'bnri'                    => 'BNRI',
            'npwp_perusahaan'         => 'NPWP Perusahaan',
            'sppl'                    => 'SPPL',
            'sptataruang'             => 'SPTATARUANG',
            'ktp_pemegang_saham'      => 'KTP Pemegang Saham',
            'k3l'                     => 'K3L'
        ];

        foreach ($dokumens as $fieldName => $jenisDokumen) {
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/legal_documents', $filename);

                LegalDocumentCompanyProfile::create([
                    'company_profile_id' => $perusahaan->id,
                    'jenis_dokumen'      => $jenisDokumen,
                    'file_path'          => 'legal_documents/' . $filename,
                ]);
            }
        }

        return redirect()->route('company_profile.show', $perusahaan->id)
            ->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function show(string $id): View
    {
        //get post by ID
        $informasiPerusahaans = CompanyProfile::with('legalDocuments', 'provinsi', 'kota', 'kecamatan', 'kelurahan')->findOrFail($id);

        //render view with post
        return view('company_profile.show', compact('informasiPerusahaans'));
    }
    public function edit(string $id): View
    {
        //get post by ID
        $informasiPerusahaan = CompanyProfile::with('legalDocuments', 'provinsi', 'kota', 'kecamatan', 'kelurahan')->findOrFail($id);
        $provinsi = ProvinceIndonesia::select('id', 'name')->get();
        $kota = KotaIndonesia::select('id', 'name')->get();
        $kecamatan = Kecamatan::select('id', 'name')->get();
        $kelurahan = Kelurahan::select('id', 'name')->get();

        //render view with post
        return view('company_profile.edit', compact('informasiPerusahaan', 'provinsi', 'kota', 'kecamatan', 'kelurahan'));
    }
    public function update(Request $request, $id): RedirectResponse
    {
        // validasi form
        $this->validate($request, [
            'nama_perusahaan' => 'required|min:5',
            'jalan'           => 'nullable|string',
            'id_provinsi'     => 'nullable|exists:indonesia_provinces,id',
            'id_kota'         => 'nullable|exists:indonesia_cities,id',
            'id_kecamatan'    => 'nullable|exists:indonesia_districts,id',
            'id_kelurahan'    => 'nullable|exists:indonesia_villages,id',
            'kode_pos'        => 'nullable|string',
            'phone_number'    => 'nullable|string',
            'email'           => 'nullable|string',
            'logo'            => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'nib'                     => 'nullable|mimes:pdf|max:2048',
            'akte_pendirian'          => 'nullable|mimes:pdf|max:2048',
            'akta_perubahan_terakhir' => 'nullable|mimes:pdf|max:2048',
            'bnri'                     => 'nullable|mimes:pdf|max:2048',
            'npwp_perusahaan'          => 'nullable|mimes:pdf|max:2048',
            'sppl' => 'nullable|mimes:pdf|max:2048',
            'sptataruang'                     => 'nullable|mimes:pdf|max:2048',
            'ktp_pemegang_saham'          => 'nullable|mimes:pdf|max:2048',
            'k3l' => 'nullable|mimes:pdf|max:2048',
            'skkemenkumhan' => 'nullable|mimes:pdf|max:2048',
        ]);

        $informasiPerusahaan = CompanyProfile::findOrFail($id);

        // jika ada upload logo baru
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logo->storeAs('public/informasi_perusahaan', $logo->hashName());

            // hapus logo lama
            if ($informasiPerusahaan->logo) {
                Storage::delete('public/informasi_perusahaan/' . $informasiPerusahaan->logo);
            }

            $informasiPerusahaan->logo = $logo->hashName();
        }

        // update data perusahaan
        $informasiPerusahaan->update([
            'logo'          => $informasiPerusahaan->logo, // jika berubah
            'nama_perusahaan' => $request->nama_perusahaan,
            'jalan'         => $request->jalan,
            'id_kelurahan'   => $request->id_kelurahan,
            'id_kecamatan'   => $request->id_kecamatan,
            'id_kota'        => $request->id_kota,
            'id_provinsi'    => $request->id_provinsi,
            'kode_pos'      => $request->kode_pos,
            'phone_number'  => $request->phone_number,
            'email'         => $request->email,
        ]);

        // mapping dokumen legal
        $dokumens = [
            'akte_pendirian' => 'Akte Pendirian',
            'akte_perubahan_terakhir' => 'Akte Perubahan Terakhir',
            'nib' => 'NIB',
            'skkemenkumhan' => 'SKKEMENKUMHAN',
            'bnri' => 'BNRI',
            'npwp_perusahaan' => 'NPWP Perusahaan',
            'sppl' => 'SPPL',
            'sptataruang' => 'SPTATARUANG',
            'ktp_pemegang_saham' => 'KTP Pemegang Saham',
            'k3l' => 'K3L'
        ];

        foreach ($dokumens as $field => $jenisDokumen) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('public/legal_documents', $filename);

                // cari dokumen lama berdasarkan jenis dan id perusahaan
                $existingDoc = LegalDocumentCompanyProfile::where('company_profile_id', $informasiPerusahaan->id)
                    ->where('jenis_dokumen', $jenisDokumen)
                    ->first();

                if ($existingDoc) {
                    // hapus file lama
                    Storage::delete('public/' . $existingDoc->file_path);

                    // update path file
                    $existingDoc->update([
                        'file_path' => 'legal_documents/' . $filename
                    ]);
                } else {
                    // insert baru kalau belum ada
                    LegalDocumentCompanyProfile::create([
                        'company_profile_id' => $informasiPerusahaan->id,
                        'jenis_dokumen' => $jenisDokumen,
                        'file_path' => 'legal_documents/' . $filename
                    ]);
                }
            }
        }

        return redirect()->route('company_profile.show', $informasiPerusahaan->id)->with(['success' => 'Data Berhasil Diubah!']);
    }
    public function destroy($id): RedirectResponse
    {
        //get post by ID
        $company = CompanyProfile::findOrFail($id);

        //delete image


        //delete post
        $company->delete();

        //redirect to index
        return redirect()->route('company_profile.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}
