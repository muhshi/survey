<?php

use App\Models\MasterWilayah;
use Illuminate\Support\Facades\Cache;

test('region endpoints return cached data', function () {
    Cache::flush();

    MasterWilayah::create([
        'idsubsls' => '33210100010001',
        'nmkec' => 'DEMAK',
        'kdkec' => '010',
        'nmdesa' => 'BINTORO',
        'kddesa' => '001',
        'nmsls' => 'RT 01 RW 01',
        'kdsls' => '0001',
    ]);

    $res1 = $this->getJson('/api/regions/kecamatan');
    $res1->assertOk()->assertJsonFragment(['value' => 'DEMAK']);
    expect(Cache::has('regions_kecamatan'))->toBeTrue();

    $res2 = $this->getJson('/api/regions/desa?kecamatan=DEMAK');
    $res2->assertOk()->assertJsonFragment(['value' => 'BINTORO']);
    expect(Cache::has('regions_desa_DEMAK'))->toBeTrue();
});
