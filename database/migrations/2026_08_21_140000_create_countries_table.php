<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso2', 2)->unique();
            $table->string('dial_code');
            $table->string('flag_emoji', 8);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Real ISO 3166-1 / ITU-T E.164 data — Africa first (this platform's primary
        // market), then other regions. order=0 (Côte d'Ivoire) is the default selection.
        $countries = [
            ['name' => "Côte d'Ivoire", 'iso2' => 'CI', 'dial_code' => '+225', 'flag_emoji' => '🇨🇮', 'order' => 0],
            ['name' => 'Sénégal', 'iso2' => 'SN', 'dial_code' => '+221', 'flag_emoji' => '🇸🇳', 'order' => 1],
            ['name' => 'Mali', 'iso2' => 'ML', 'dial_code' => '+223', 'flag_emoji' => '🇲🇱', 'order' => 2],
            ['name' => 'Burkina Faso', 'iso2' => 'BF', 'dial_code' => '+226', 'flag_emoji' => '🇧🇫', 'order' => 3],
            ['name' => 'Guinée', 'iso2' => 'GN', 'dial_code' => '+224', 'flag_emoji' => '🇬🇳', 'order' => 4],
            ['name' => 'Bénin', 'iso2' => 'BJ', 'dial_code' => '+229', 'flag_emoji' => '🇧🇯', 'order' => 5],
            ['name' => 'Togo', 'iso2' => 'TG', 'dial_code' => '+228', 'flag_emoji' => '🇹🇬', 'order' => 6],
            ['name' => 'Niger', 'iso2' => 'NE', 'dial_code' => '+227', 'flag_emoji' => '🇳🇪', 'order' => 7],
            ['name' => 'Ghana', 'iso2' => 'GH', 'dial_code' => '+233', 'flag_emoji' => '🇬🇭', 'order' => 8],
            ['name' => 'Nigéria', 'iso2' => 'NG', 'dial_code' => '+234', 'flag_emoji' => '🇳🇬', 'order' => 9],
            ['name' => 'Liberia', 'iso2' => 'LR', 'dial_code' => '+231', 'flag_emoji' => '🇱🇷', 'order' => 10],
            ['name' => 'Sierra Leone', 'iso2' => 'SL', 'dial_code' => '+232', 'flag_emoji' => '🇸🇱', 'order' => 11],
            ['name' => 'Guinée-Bissau', 'iso2' => 'GW', 'dial_code' => '+245', 'flag_emoji' => '🇬🇼', 'order' => 12],
            ['name' => 'Gambie', 'iso2' => 'GM', 'dial_code' => '+220', 'flag_emoji' => '🇬🇲', 'order' => 13],
            ['name' => 'Mauritanie', 'iso2' => 'MR', 'dial_code' => '+222', 'flag_emoji' => '🇲🇷', 'order' => 14],
            ['name' => 'Cap-Vert', 'iso2' => 'CV', 'dial_code' => '+238', 'flag_emoji' => '🇨🇻', 'order' => 15],
            ['name' => 'Cameroun', 'iso2' => 'CM', 'dial_code' => '+237', 'flag_emoji' => '🇨🇲', 'order' => 16],
            ['name' => 'Gabon', 'iso2' => 'GA', 'dial_code' => '+241', 'flag_emoji' => '🇬🇦', 'order' => 17],
            ['name' => 'Congo-Brazzaville', 'iso2' => 'CG', 'dial_code' => '+242', 'flag_emoji' => '🇨🇬', 'order' => 18],
            ['name' => 'République Démocratique du Congo', 'iso2' => 'CD', 'dial_code' => '+243', 'flag_emoji' => '🇨🇩', 'order' => 19],
            ['name' => 'Tchad', 'iso2' => 'TD', 'dial_code' => '+235', 'flag_emoji' => '🇹🇩', 'order' => 20],
            ['name' => 'République Centrafricaine', 'iso2' => 'CF', 'dial_code' => '+236', 'flag_emoji' => '🇨🇫', 'order' => 21],
            ['name' => 'Guinée Équatoriale', 'iso2' => 'GQ', 'dial_code' => '+240', 'flag_emoji' => '🇬🇶', 'order' => 22],
            ['name' => 'Maroc', 'iso2' => 'MA', 'dial_code' => '+212', 'flag_emoji' => '🇲🇦', 'order' => 23],
            ['name' => 'Algérie', 'iso2' => 'DZ', 'dial_code' => '+213', 'flag_emoji' => '🇩🇿', 'order' => 24],
            ['name' => 'Tunisie', 'iso2' => 'TN', 'dial_code' => '+216', 'flag_emoji' => '🇹🇳', 'order' => 25],
            ['name' => 'Égypte', 'iso2' => 'EG', 'dial_code' => '+20', 'flag_emoji' => '🇪🇬', 'order' => 26],
            ['name' => 'Rwanda', 'iso2' => 'RW', 'dial_code' => '+250', 'flag_emoji' => '🇷🇼', 'order' => 27],
            ['name' => 'Burundi', 'iso2' => 'BI', 'dial_code' => '+257', 'flag_emoji' => '🇧🇮', 'order' => 28],
            ['name' => 'Kenya', 'iso2' => 'KE', 'dial_code' => '+254', 'flag_emoji' => '🇰🇪', 'order' => 29],
            ['name' => 'Éthiopie', 'iso2' => 'ET', 'dial_code' => '+251', 'flag_emoji' => '🇪🇹', 'order' => 30],
            ['name' => 'Afrique du Sud', 'iso2' => 'ZA', 'dial_code' => '+27', 'flag_emoji' => '🇿🇦', 'order' => 31],
            ['name' => 'Madagascar', 'iso2' => 'MG', 'dial_code' => '+261', 'flag_emoji' => '🇲🇬', 'order' => 32],
            ['name' => 'France', 'iso2' => 'FR', 'dial_code' => '+33', 'flag_emoji' => '🇫🇷', 'order' => 33],
            ['name' => 'Belgique', 'iso2' => 'BE', 'dial_code' => '+32', 'flag_emoji' => '🇧🇪', 'order' => 34],
            ['name' => 'Suisse', 'iso2' => 'CH', 'dial_code' => '+41', 'flag_emoji' => '🇨🇭', 'order' => 35],
            ['name' => 'Canada', 'iso2' => 'CA', 'dial_code' => '+1', 'flag_emoji' => '🇨🇦', 'order' => 36],
            ['name' => 'États-Unis', 'iso2' => 'US', 'dial_code' => '+1', 'flag_emoji' => '🇺🇸', 'order' => 37],
            ['name' => 'Royaume-Uni', 'iso2' => 'GB', 'dial_code' => '+44', 'flag_emoji' => '🇬🇧', 'order' => 38],
            ['name' => 'Allemagne', 'iso2' => 'DE', 'dial_code' => '+49', 'flag_emoji' => '🇩🇪', 'order' => 39],
            ['name' => 'Portugal', 'iso2' => 'PT', 'dial_code' => '+351', 'flag_emoji' => '🇵🇹', 'order' => 40],
            ['name' => 'Chine', 'iso2' => 'CN', 'dial_code' => '+86', 'flag_emoji' => '🇨🇳', 'order' => 41],
            ['name' => 'Inde', 'iso2' => 'IN', 'dial_code' => '+91', 'flag_emoji' => '🇮🇳', 'order' => 42],
            ['name' => 'Émirats Arabes Unis', 'iso2' => 'AE', 'dial_code' => '+971', 'flag_emoji' => '🇦🇪', 'order' => 43],
            ['name' => 'Liban', 'iso2' => 'LB', 'dial_code' => '+961', 'flag_emoji' => '🇱🇧', 'order' => 44],
            ['name' => 'Turquie', 'iso2' => 'TR', 'dial_code' => '+90', 'flag_emoji' => '🇹🇷', 'order' => 45],
        ];

        $now = now();
        foreach ($countries as &$c) {
            $c['created_at'] = $now;
            $c['updated_at'] = $now;
        }

        \Illuminate\Support\Facades\DB::table('countries')->insert($countries);
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
