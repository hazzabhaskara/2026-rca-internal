<?php

namespace App\Http\Controllers;

use App\Kecamatan;
use App\Kelurahan;
use App\KotaIndonesia;
use App\ProvinceIndonesia;
use App\TaxpayersProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaxpayersCompanyController extends Controller
{
    //

    public function index(): View
    {
        $taxpayers = TaxpayersProfile::all();
        return view('taxpayers_company.index', compact('taxpayers'));
    }
    public function create(): View
    {
        $provinsi = ProvinceIndonesia::select('id', 'name')->get();
        $kota = KotaIndonesia::select('id', 'name')->get();
        $kecamatan = Kecamatan::select('id', 'name')->get();
        $kelurahan = Kelurahan::select('id', 'name')->get();
        return view('taxpayers_company.create', compact('provinsi', 'kota', 'kecamatan', 'kelurahan'));
    }
    public function store(Request $request): RedirectResponse
    {
        //validate form
        $this->validate($request, [
            'nama_perusahaan'     => 'required|min:5',
            'jalan'   => 'nullable|string',
            'id_provinsi'     => 'nullable|exists:indonesia_provinces,id',
            'id_kota'         => 'nullable|exists:indonesia_cities,id',
            'id_kecamatan'    => 'nullable|exists:indonesia_districts,id',
            'id_kelurahan'    => 'nullable|exists:indonesia_villages,id',
            'kode_pos' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'email' => 'nullable|string',
            'logo'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'bentuk_badan_hukum' => 'nullable|string',
            'npwp' => 'nullable|string',
            'klu_code' => 'nullable|string',
            'klu_description' => 'nullable|string',
            'tax_office' => 'nullable|string'

        ]);

        //upload image
        $image = $request->file('logo');
        $imageName = null;

        if ($image) {
            $image->storeAs('public/taxpayers', $image->hashName());
            $imageName = $image->hashName();
        }


        // simpan data dan ambil objeknya
        $profile = TaxpayersProfile::create([
            'logo'     => $imageName,
            'nama_perusahaan'     => $request->nama_perusahaan,
            'jalan'   => $request->jalan,
            'id_kelurahan'   => $request->id_kelurahan,
            'id_kecamatan'   => $request->id_kecamatan,
            'id_kota'        => $request->id_kota,
            'id_provinsi'    => $request->id_provinsi,
            'kode_pos' => $request->kode_pos,
            'phone_number'     => $request->phone_number,
            'email'   => $request->email,
            'bentuk_badan_hukum' => $request->bentuk_badan_hukum,
            'npwp' => $request->npwp,
            'klu_code' => $request->klu_code,
            'klu_description' => $request->klu_description,
            'tax_office' => $request->tax_office
        ]);

        // redirect ke show dengan id
        return redirect()->route('taxpayers_company.show', $profile->id)->with('success', 'Data Berhasil Disimpan!');
    }
    public function show(string $id): View
    {
        //get post by ID
        $taxpayers = TaxpayersProfile::with(['provinsi', 'kota', 'kelurahan', 'kecamatan'])->findOrFail($id);

        //render view with post
        return view('taxpayers_company.show', compact('taxpayers'));
    }
    public function edit(string $id): View
    {
        //get post by ID
        $taxpayers = TaxpayersProfile::findOrFail($id);
        $provinsi = ProvinceIndonesia::select('id', 'name')->get();
        $kota = KotaIndonesia::select('id', 'name')->get();
        $kecamatan = Kecamatan::select('id', 'name')->get();
        $kelurahan = Kelurahan::select('id', 'name')->get();

        //render view with post
        return view('taxpayers_company.edit', compact('taxpayers', 'provinsi', 'kota', 'kecamatan', 'kelurahan'));
    }
    public function update(Request $request, $id): RedirectResponse
    {
        //validate form
        $this->validate($request, [
            'nama_perusahaan'     => 'required|min:5',
            'jalan'   => 'nullable|string',
            'id_provinsi'     => 'nullable|exists:indonesia_provinces,id',
            'id_kota'         => 'nullable|exists:indonesia_cities,id',
            'id_kecamatan'    => 'nullable|exists:indonesia_districts,id',
            'id_kelurahan'    => 'nullable|exists:indonesia_villages,id',
            'kode_pos' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'email' => 'nullable|string',
            'logo'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        //get post by ID
        $taxpayers = TaxpayersProfile::findOrFail($id);

        //check if image is uploaded
        if ($request->hasFile('logo')) {

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/taxpayers', $image->hashName());

            //delete old image
            Storage::delete('public/taxpayers/' . $taxpayers->logo);

            //update post with new image
            $taxpayers->update([
                'image'     => $image->hashName(),
                'nama_perusahaan'     => $request->nama_perusahaan,
                'jalan'   => $request->jalan,
                'id_kelurahan' => $request->id_kelurahan,
                'id_kecamatan'     => $request->id_kecamatan,
                'id_kota'   => $request->id_kota,
                'id_provinsi' => $request->id_provinsi,
                'kode_pos' => $request->kode_pos,
                'phone_number'     => $request->phone_number,
                'email'   => $request->email,
                'bentuk_badan_hukum' => $request->bentuk_badan_hukum,
                'npwp' => $request->npwp,
                'klu_code' => $request->klu_code,
                'klu_description' => $request->klu_description,
                'tax_office' => $request->tax_office
            ]);
        } else {

            //update post without image
            $taxpayers->update([
                'nama_perusahaan'     => $request->nama_perusahaan,
                'jalan'   => $request->jalan,
                'id_kelurahan' => $request->id_kelurahan,
                'id_kecamatan'     => $request->id_kecamatan,
                'id_kota'   => $request->id_kota,
                'id_provinsi' => $request->id_provinsi,
                'kode_pos' => $request->kode_pos,
                'phone_number'     => $request->phone_number,
                'email'   => $request->email,
                'bentuk_badan_hukum' => $request->bentuk_badan_hukum,
                'npwp' => $request->npwp,
                'klu_code' => $request->klu_code,
                'klu_description' => $request->klu_description,
                'tax_office' => $request->tax_office
            ]);
        }

        //redirect to index
        return redirect()->route('taxpayers_company.show', $taxpayers->id)->with(['success' => 'Data Berhasil Diubah!']);
    }
}
