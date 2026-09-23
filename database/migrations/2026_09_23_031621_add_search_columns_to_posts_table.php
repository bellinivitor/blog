<?php

use Domain\Post\Actions\ExtractPostPlainTextAction;
use Domain\Post\Actions\NormalizeSearchTextAction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('search_title')->default('')->after('content');
            $table->longText('search_content')->nullable()->after('search_title');
        });

        $normalize = new NormalizeSearchTextAction;
        $extract = new ExtractPostPlainTextAction;

        DB::table('posts')->orderBy('id')->each(function (object $post) use ($normalize, $extract): void {
            DB::table('posts')->where('id', $post->id)->update([
                'search_title' => $normalize($post->title),
                'search_content' => $normalize($extract($post->content)),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['search_title', 'search_content']);
        });
    }
};
