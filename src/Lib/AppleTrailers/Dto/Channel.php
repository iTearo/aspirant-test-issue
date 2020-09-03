<?php

declare(strict_types=1);

namespace Lib\AppleTrailers\Dto;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;

class Channel
{
    /**
     * @var Item[]
     * @XmlList(inline=true, entry="item")
     * @Type("array<Lib\AppleTrailers\Dto\Item>")
     */
    public array $items;
}
