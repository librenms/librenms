<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PortsFdb;
use Carbon\Carbon;

class AddDatesToFdb extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ports_fdb', function (Blueprint $table) {
              $table->timestamps();
        });
        
        // Let's get a value for existing PortsFdb data :
        DB::table('ports_fdb')->update(array('created_at' => \Carbon\Carbon::now()));
        DB::table('ports_fdb')->update(array('updated_at' => \Carbon\Carbon::now()));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ports_fdb', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
}
