<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ip_views', function (Blueprint $table) {
            # skip PK and foreign key for performance
            $table->ipAddress();
            $table->foreignId('article_id');
            $table->integer('created_at', false, true);
            $table->primary([
                'ip_address',
                'article_id',
            ]);
            $table->index([
                'created_at',
                'article_id'
            ]);
        });

        Schema::create('article_views', function (Blueprint $table) {
            # skip PK and foreign key for performance
            $table->date('date');
            $table->foreignId('article_id');
            $table->integer('count', false, true);
            $table->primary([
                'date',
                'article_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ip_views');
        Schema::dropIfExists('article_views');
    }
};
