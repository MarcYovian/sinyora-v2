<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnalysisRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'editableData.doc_date' => 'required|date',
            'editableKegiatan.*.nama_kegiatan_utama' => 'required|string',
            'editableKegiatan.*.lokasi_kegiatan' => 'required|string',
            'editableKegiatan.*.start_date' => 'required|date_format:Y-m-dTH:i',
            'editableKegiatan.*.end_date' => 'required|date_format:Y-m-dTH:i',
            'analysisResult.data.blok_penanda_tangan.*.nama' => 'required|string',
            'analysisResult.data.blok_penanda_tangan.*.jabatan' => 'required|string',
            'analysisResult.data.detail_kegiatan.*.barang_dipinjam.*.item' => 'required|string',
            'analysisResult.data.detail_kegiatan.*.barang_dipinjam.*.jumlah' => 'required|integer|min:1',
            'analysisResult.data.informasi_umum_dokumen.nomor_surat' => 'required|string',
            'analysisResult.data.informasi_umum_dokumen.penerima_surat.*' => 'required|string',
            'analysisResult.data.informasi_umum_dokumen.perihal_surat' => 'required|string|max:255',
            'analysisResult.data.informasi_umum_dokumen.tanggal_surat_dokumen' => 'required|date',
            'analysisResult.data.type' => 'required|string',
        ];
    }
}
