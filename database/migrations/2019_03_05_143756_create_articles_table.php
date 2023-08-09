<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id')->comment('文章类别id')->default(0);
            $table->string('category_name',20)->comment('文章类别名称')->default('其他');
            $table->string('title',100)->comment('文章标题');
            $table->string('author',30)->comment('文章作者')->default('未知作者');
            $table->string('descr',255)->comment('文章简介')->nullable();
            $table->string('image',255)->comment('文章导图');
            $table->text('content')->comment('文章内容');
            $table->unsignedTinyInteger('status')->comment('文章状态,1-草稿；2-发表')->default(1);
            $table->unsignedInteger('click_count')->comment('文章点击数量')->default(0);
            $table->unsignedTinyInteger('top_status')->comment('置顶状态,0-普通 1-置顶')->default(0);
            $table->unsignedInteger('top_time')->comment('置顶时间')->default(0);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
