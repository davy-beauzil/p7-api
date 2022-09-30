<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

class QueryParameters
{
    public int $page = 1;

    #[SerializedName('perPage')]
    public int $per_page = 10;
}
