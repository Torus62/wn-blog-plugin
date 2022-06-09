<?php

namespace Torus\Blog\Updates;

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

class CategoriesAddExcludeField extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('torus_blog_categories', 'hide_from_listings')) {
            Schema::table('torus_blog_categories', function ($table) {
                $table->boolean('hide_from_listings')->nullable()->default(0);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('torus_blog_categories', 'hide_from_listings')) {
            Schema::table('torus_blog_categories', function ($table) {
                $table->dropColumn('hide_from_listings');
            });
        }
    }
}
