<?php

namespace App\Http\Resources\Absensi;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AbsensiCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'success' => true,
            'message' => 'Data Absensi Siswa',
            'data' => $this->collection,
        ];
    }
}
