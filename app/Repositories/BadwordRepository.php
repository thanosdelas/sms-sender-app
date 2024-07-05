<?php

namespace App\Repositories;

use App\Models\Badword;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Repositories\Interfaces\BadwordRepositoryInterface;

class BadwordRepository implements BadwordRepositoryInterface{
  private $redis;

  public function __construct(){
    $this->redis = Redis::connection('cache');

    // Check if badwords are cached from the database,
    // and if not cache them only once.
    if($this->cacheBadwords() === false){
      throw new \Exception('Caching of badwords failed.');
    }
  }

  public function isBadWord(string $word): bool{
    return $this->redis->sismember('badwords', $word) === 1;
  }

  /**
   * Create a new word in the database, and append it to the Redis cache.
   */
  public function create(string $word): bool{
    $badword = new Badword();

    // We should probably move the strtolower and other
    // validation/sanitization inside the model or an entity class,
    // and then reload the mutated version here.

    $word = strtolower($word);

    $badword->badword = $word;

    if($badword->save()){
      $this->redis->sadd('badwords',$word);
      return true;
    }

    return false;
  }

  /**
   * Load data either from the Cache, or from the database and cache them forever.
   */
  private function cacheBadwords(): bool{
    // NOTE: We could use the Laravel provided facade for caching here, in order to be driver
    //       agnostic and easilly switch to another caching mechanism, however doing that way
    //       the data isstored in a serialized format, and we have to fetch it from the cache,
    //       deserialize it and loop over it, which is not efficient. Ofcourse this is subject
    //       to amount of data, but we can presume that it's a long list, so we are tightly coupled
    //       to Redis here, and we should re-implement this method shall we need to swicth to another driver.
    //
    //       TO be driver agnostic use:
    //       ```
    //       return Cache::rememberForever('badwords', function () {
    //         return Badword::pluck('badword')->toArray();
    //       });
    //       ```
    $badwordsCardinality = $this->redis->scard('badwords');

    if($badwordsCardinality === 0){
      $badwordsFromDatabase = Badword::pluck('badword')->toArray();

      if(count($badwordsFromDatabase) === 0){
        throw new \Exception('No badwords found in database to update cache');
      }

      // Cache them as a Redis SET
      $result = $this->redis->sadd('badwords', $badwordsFromDatabase);

      if($result > 0){
        return true;
      }

      return false;
    }

    return true;
  }
}
