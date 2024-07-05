<?php

namespace App\Repositories\Interfaces;

interface BadwordRepositoryInterface{
  public function isBadWord(string $word): bool;
  public function create(string $word): bool;
}
