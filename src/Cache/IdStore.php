<?php

namespace BristolSU\UnionCloud\Cache;

use Illuminate\Support\Collection;

interface IdStore
{

    public function count();

    public function pop();

    public function push($id);

    public function ids();

    public function setIds(Collection $ids);

}