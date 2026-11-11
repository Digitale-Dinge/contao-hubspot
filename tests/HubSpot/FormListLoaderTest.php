<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\HubSpot;

use DigitaleDinge\HubSpotBundle\HubSpot\FormListLoader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class FormListLoaderTest extends TestCase
{
    public function testLoadsTheFormsOfAllPagesSortedByName(): void
    {
        $firstPage = new MockResponse('{"results":[{"id":"b2","name":"Newsletter"},{"id":"broken"}],"paging":{"next":{"after":"abc"}}}');
        $secondPage = new MockResponse('{"results":[{"id":"a1","name":"contact"}]}');

        $loader = new FormListLoader(new MockHttpClient([$firstPage, $secondPage]), $this->createStub(LoggerInterface::class), 'pat-token');

        $this->assertSame(
            [
                'a1' => 'contact',
                'b2' => 'Newsletter',
            ],
            $loader->load(),
        );
        $this->assertSame('https://api.hubapi.com/marketing/v3/forms/?limit=100&archived=false', $firstPage->getRequestUrl());
        $this->assertSame('https://api.hubapi.com/marketing/v3/forms/?limit=100&archived=false&after=abc', $secondPage->getRequestUrl());
        $this->assertSame(['Accept: */*', 'Authorization: Bearer pat-token'], $firstPage->getRequestOptions()['headers']);
    }

    public function testDoesNotRequestAnythingWithoutAToken(): void
    {
        $httpClient = new MockHttpClient(static fn () => throw new \LogicException('No request expected'));

        $this->assertSame([], new FormListLoader($httpClient, $this->createStub(LoggerInterface::class), null)->load());
    }

    public function testLogsAFailedRequest(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('HTTP 401'))
        ;

        $loader = new FormListLoader(new MockHttpClient(new MockResponse('{"status":"error"}', [
            'http_code' => 401,
        ])), $logger, 'pat-token');

        $this->assertSame([], $loader->load());
    }
}
