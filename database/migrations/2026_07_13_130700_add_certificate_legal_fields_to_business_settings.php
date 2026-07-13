<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->string('cin', 40)->nullable()->after('registry_authority');
            $table->string('gstin', 40)->nullable()->after('cin');
            $table->string('registrar_lei_number', 30)->nullable()->after('gstin');
            $table->string('ubisecure_lei', 30)->nullable()->after('registrar_lei_number');
            $table->string('nasdaq_lei', 30)->nullable()->after('ubisecure_lei');
            $table->text('registered_office_address')->nullable()->after('nasdaq_lei');
            $table->text('office_location_address')->nullable()->after('registered_office_address');
        });

        DB::table('lei_business_settings')->whereNull('cin')->update([
            'cin' => 'U74999PN2019FTC184211',
            'gstin' => '19AADCL9323M1Z0',
            'registrar_lei_number' => '9845003A5176DAA0E442',
            'ubisecure_lei' => '529900T8BM49AURSDO55',
            'nasdaq_lei' => '485100001PLJJ09NZT59',
            'registered_office_address' => '15A, 4th Floor, City Vista, Tower A Fountain Road, Kharadi, Pune, Maharashtra 411014, India',
            'office_location_address' => 'Sixth Floor, CCSG 0615, Block G, City Center Siliguri, Matigara, Siliguri, Darjeeling, West Bengal 734010, India',
        ]);
    }

    public function down(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->dropColumn([
                'cin',
                'gstin',
                'registrar_lei_number',
                'ubisecure_lei',
                'nasdaq_lei',
                'registered_office_address',
                'office_location_address',
            ]);
        });
    }
};
