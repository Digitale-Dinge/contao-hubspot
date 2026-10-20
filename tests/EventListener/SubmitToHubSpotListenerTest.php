<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\EventListener;

use Contao\Form;
use DigitaleDinge\HubSpotBundle\EventListener\SubmitToHubSpotListener;
use DigitaleDinge\HubSpotBundle\HubSpot\HubSpotClient;
use DigitaleDinge\HubSpotBundle\HubSpot\Submission;
use DigitaleDinge\HubSpotBundle\HubSpot\SubmissionFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class SubmitToHubSpotListenerTest extends TestCase
{
    public function testSubmitsTheFormToHubSpot(): void
    {
        $request = Request::create('https://example.com/contact');
        $submission = new Submission();

        $submissionFactory = $this->createMock(SubmissionFactory::class);
        $submissionFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                5,
                [
                    'email' => 'jane@example.com',
                ],
                $request,
            )
            ->willReturn($submission)
        ;

        $client = $this->createMock(HubSpotClient::class);
        $client
            ->expects($this->once())
            ->method('submit')
            ->with('12345', 'a1b2c3d4-e5f6', $submission)
        ;

        $listener = new SubmitToHubSpotListener($submissionFactory, $client, $this->createRequestStack($request));
        $listener([
            'email' => 'jane@example.com',
        ], $this->createFormData(true), null, [], $this->createStub(Form::class));
    }

    public function testIgnoresFormsWithoutHubSpot(): void
    {
        $client = $this->createMock(HubSpotClient::class);
        $client
            ->expects($this->never())
            ->method('submit')
        ;

        $listener = new SubmitToHubSpotListener($this->createStub(SubmissionFactory::class), $client, $this->createRequestStack(Request::create('/')));
        $listener([
            'email' => 'jane@example.com',
        ], $this->createFormData(false), null, [], $this->createStub(Form::class));
    }

    /**
     * @return array<string, mixed>
     */
    private function createFormData(bool $enabled): array
    {
        return [
            'id' => '5',
            'dd_hubspot' => $enabled,
            'dd_hubspot_portal_id' => '12345',
            'dd_hubspot_form_id' => 'a1b2c3d4-e5f6',
        ];
    }

    private function createRequestStack(Request $request): RequestStack
    {
        return new RequestStack([$request]);
    }
}
