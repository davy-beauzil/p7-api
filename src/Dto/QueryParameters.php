<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

class QueryParameters
{
    public const PAGE = 1;
    public const PER_PAGE = 10;

    public int $page = self::PAGE;

    #[SerializedName('perPage')]
    public int $per_page = self::PER_PAGE;
}
