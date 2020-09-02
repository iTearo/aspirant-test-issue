<?php

declare(strict_types=1);

namespace App\Lib\AppleTrailers\Dto;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;

class Channel
{
    /**
     * @var Item[]
     * @XmlList(inline=true, entry="item")
     * @Type("array<App\Lib\AppleTrailers\Dto\Item>")
     */
    public array $items;
}
