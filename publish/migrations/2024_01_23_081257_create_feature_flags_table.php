<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateFeatureFlagsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('feature', 255);
            $table->string('rule', 255);
            $table->timestamp('overridden_at')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->boolean('overridden')->default(null)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
}
