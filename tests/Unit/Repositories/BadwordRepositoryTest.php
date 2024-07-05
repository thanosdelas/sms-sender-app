<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use Illuminate\Support\Facades\Redis;
use App\Repositories\BadwordRepository;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BadwordRepositoryTest extends TestCase{
  use RefreshDatabase;

  private $redis;
  private $badwordRepository;

  public function setUp(): void{
    parent::setUp();

    $this->redis = Redis::connection('cache');
    $this->redis->flushall();

    // Ensure database is setup with default badword entries
    $this->seed(TestDatabaseSeeder::class);

    // TODO: Move this in a dedicated test, and ensure that,
    //       when initializing repository, Redis cache update should be called.
    $this->badwordRepository = new BadwordRepository();
  }

  /**
   * @test
   */
  public function is_bad_word(): void{
    $word = 'facebook';

    $result = $this->badwordRepository->isBadWord($word);

    $this->assertEquals($result, true);
  }

  /**
   * @test
   */
  public function create_and_cache_bad_word(): void{
    $word = 'Snapchat';

    $before_badwords_redis_count = $this->redis->scard('badwords');
    $this->assertEquals($before_badwords_redis_count, 4);

    $result = $this->badwordRepository->create($word);

    // Ensure created badword is in the database.
    $this->assertDatabaseHas('badwords', [
      'badword' => strtolower($word),
    ]);

    // Ensure created badword is appended in the Redis cache.
    $redisResult = Redis::connection('cache')->sismember('badwords', strtolower($word));
    $this->assertEquals($redisResult, 1);

    $after_badwords_redis_count = $this->redis->scard('badwords');
    $this->assertEquals($after_badwords_redis_count, 5);
  }
}
