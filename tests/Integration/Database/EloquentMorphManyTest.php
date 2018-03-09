<?php

namespace Illuminate\Tests\Integration\Database\EloquentMorphManyTest;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Tests\Integration\Database\DatabaseTestCase;

/**
 * @group integration
 */
class EloquentMorphManyTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        Schema::create('posts', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('comments', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('commentable_id');
            $table->string('commentable_type');
            $table->timestamps();
        });

        Carbon::setTestNow(null);
    }

    /**
     * @test
     */
    public function update_model_with_default_withCount()
    {
        $post = Post::create(['title' => str_random()]);

        $post->update(['title' => 'new name']);

        $this->assertEquals('new name', $post->title);
    }
}

class Post extends Model
{
    public $table = 'posts';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $withCount = ['comments'];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Comment extends Model
{
    public $table = 'comments';
    public $timestamps = true;
    protected $guarded = ['id'];

    public function commentable()
    {
        return $this->morphTo();
    }
}
