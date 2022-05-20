<?php

namespace Torus\Blog\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use Torus\Blog\Models\Category as CategoryModel;

class PostsAddRegionsBrand extends Migration
{

    public function up()
    {
        if (!Schema::hasColumn('torus_blog_posts', 'regions')) {
            Schema::table('torus_blog_posts', function ($table) {
                $table->json('regions')->nullable();
            });
        }

        if (!Schema::hasColumn('torus_blog_posts', 'brand')) {
            Schema::table('torus_blog_posts', function ($table) {
                $table->string('brand')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('torus_blog_posts', 'regions')) {
            Schema::table('torus_blog_posts', function ($table) {
                $table->dropColumn('regions');
            });
        }
        if (Schema::hasColumn('torus_blog_posts', 'brand')) {
            Schema::table('torus_blog_posts', function ($table) {
                $table->dropColumn('brand');
            });
        }
    }
}
