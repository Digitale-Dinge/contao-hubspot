<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\HubSpot;

use DigitaleDinge\HubSpotBundle\HubSpot\HubSpotClient;
use DigitaleDinge\HubSpotBundle\HubSpot\Submission;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class HubSpotClientTest extends TestCase
{
    public function testPostsTheSubmissionToTheFormEndpoint(): void
    {
        $response = new MockResponse('{"inlineMessage":"Thanks"}');

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->never())
            ->method('error')
        ;

        $client = new HubSpotClient(new MockHttpClient($response), $logger);
        $client->submit('12345', 'a1b2c3d4-e5f6', new Submission()->addField('email', 'jane@example.com'));

        $this->assertSame('POST', $response->getRequestMethod());
        $this->assertSame('https://api.hsforms.com/submissions/v3/integration/submit/12345/a1b2c3d4-e5f6', $response->getRequestUrl());
        $this->assertSame(
            '{"fields":[{"objectTypeId":"0-1","name":"email","value":"jane@example.com"}]}',
            $response->getRequestOptions()['body'],
        );
    }

    public function testLogsARejectedSubmission(): void
    {
        $response = new MockResponse('{"errors":[{"errorType":"MISSING_PROCESSING_CONSENT"}]}', [
            'http_code' => 400,
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('MISSING_PROCESSING_CONSENT'))
        ;

        new HubSpotClient(new MockHttpClient($response), $logger)->submit('12345', 'a1b2c3d4-e5f6', new Submission());
    }

    public function testLogsATransportError(): void
    {
        $response = new MockResponse(info: [
            'error' => 'Connection timed out',
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Connection timed out'))
        ;

        new HubSpotClient(new MockHttpClient($response), $logger)->submit('12345', 'a1b2c3d4-e5f6', new Submission());
    }
}
