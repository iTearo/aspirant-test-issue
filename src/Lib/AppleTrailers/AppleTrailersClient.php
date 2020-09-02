<?php

declare(strict_types=1);

namespace App\Lib\AppleTrailers;

use App\Lib\AppleTrailers\Dto\Item;
use App\Lib\AppleTrailers\Dto\Rss;
use App\Lib\AppleTrailers\Dto\Trailer;
use GuzzleHttp\Psr7\Request;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Console\Exception\RuntimeException;

class AppleTrailersClient
{
    private SerializerInterface $serializer;

    private ClientInterface $client;

    private string $rssUrl;

    public function __construct(
        SerializerInterface $serializer,
        ClientInterface $client,
        string $rssUrl
    ) {
        $this->serializer = $serializer;
        $this->client = $client;
        $this->rssUrl = $rssUrl;
    }

    /**
     * @param int $itemsNum
     * @return Trailer[]
     */
    public function fetchTrailers(int $itemsNum = 10): array
    {
        try {
            $response = $this->client->sendRequest(new Request('GET', $this->rssUrl));

        } catch (ClientExceptionInterface $e) {
            throw new RuntimeException($e->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException(sprintf('Response status is %d, expected %d', $response->getStatusCode(), 200));
        }

        $data = $response->getBody()->getContents();

        $rss = $this->serializer->deserialize($data, Rss::class, 'xml');

        $rssItems = array_slice($rss->channel->items, 0, $itemsNum);

        return $this->makeTrailersFromRssItems($rssItems);
    }

    /**
     * @param Item[] $rssItems
     * @return Trailer[]
     */
    protected function makeTrailersFromRssItems(array $rssItems): array
    {
        $trailers = [];

        foreach ($rssItems as $rssItem) {
            $trailers[] = $this->makeTrailerFromRssItem($rssItem);
        }

        return $trailers;
    }

    protected function makeTrailerFromRssItem(Item $rssItem): Trailer
    {
        $trailer = new Trailer();
        $trailer->title = $rssItem->title;
        $trailer->description = $rssItem->description;
        $trailer->link = $rssItem->link;
        $trailer->pubDate = new \DateTimeImmutable($rssItem->pubDate);
        $trailer->image = $rssItem->link . '/images/poster.jpg';
        return $trailer;
    }
}
