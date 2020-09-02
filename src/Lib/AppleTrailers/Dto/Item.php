<?php

declare(strict_types=1);

namespace App\Lib\AppleTrailers\Dto;

use JMS\Serializer\Annotation\SerializedName;

class Item
{
    public string $title;

    public string $link;

    public string $description;

    /**
     * @SerializedName("pubDate")
     */
    public string $pubDate;
}
