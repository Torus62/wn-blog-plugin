<?php

namespace Torus\Blog\Updates;

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

class PostsShowFeaturedImage extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('torus_blog_posts', 'show_featured_image')) {
            Schema::table('torus_blog_posts', function ($table) {
                $table->boolean('show_featured_image')->nullable()->default(1);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('torus_blog_posts', 'show_featured_image')) {
            Schema::table('torus_blog_posts', function ($table) {
                $table->dropColumn('show_featured_image');
            });
        }
    }
}
