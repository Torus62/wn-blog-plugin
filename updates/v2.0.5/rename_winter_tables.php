<?php namespace Torus\Blog\Updates;

use Db;
use Schema;
use Winter\Storm\Database\Updates\Migration;

class RenameWinterTables extends Migration
{
    const TABLES = [
        'categories',
        'posts',
        'posts_categories'
    ];

    public function up()
    {
        foreach (self::TABLES as $table) {
            $from = 'winter_blog_' . $table;
            $to = 'torus_blog_' . $table;

            if (Schema::hasTable($from) && !Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }

        Db::table('system_files')->where('attachment_type', 'Winter\Blog\Models\Post')->update(['attachment_type' => 'Torus\Blog\Models\Post']);
        Db::table('system_settings')->where('item', 'winter_blog_settings')->update(['item' => 'torus_blog_settings']);
    }

    public function down()
    {
        foreach (self::TABLES as $table) {
            $from = 'torus_blog_' . $table;
            $to = 'winter_blog_' . $table;

            if (Schema::hasTable($from) && !Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }

        Db::table('system_files')->where('attachment_type', 'Torus\Blog\Models\Post')->update(['attachment_type' => 'Winter\Blog\Models\Post']);
        Db::table('system_settings')->where('item', 'torus_blog_settings')->update(['item' => 'winter_blog_settings']);
    }
}
