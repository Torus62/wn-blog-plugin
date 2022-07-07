<?php

namespace Torus\Blog\Updates;

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

class PostsAddBrandOnlyField extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('torus_blog_posts', 'brand_specific')) {
            Schema::table('torus_blog_posts', function ($table) {
                $table->boolean('brand_specific')->nullable()->default(0);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('torus_blog_posts', 'brand_specific')) {
            Schema::table('torus_blog_posts', function ($table) {
                $table->dropColumn('brand_specific');
            });
        }
    }
}
