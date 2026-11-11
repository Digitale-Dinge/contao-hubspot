<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\EventListener\DataContainer;

use Contao\DataContainer;
use DigitaleDinge\HubSpotBundle\EventListener\DataContainer\HubSpotFormIdListener;
use DigitaleDinge\HubSpotBundle\HubSpot\FormListLoader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class HubSpotFormIdListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TL_DCA']);

        parent::tearDown();
    }

    public function testTurnsTheFormIdIntoASelectMenu(): void
    {
        $this->invokeListener(
            'edit',
            [
                'dd_hubspot' => true,
                'dd_hubspot_form_id' => 'old-form',
            ],
            'pat-token',
        );

        $field = $GLOBALS['TL_DCA']['tl_form']['fields']['dd_hubspot_form_id'];

        $this->assertSame('select', $field['inputType']);
        $this->assertSame(
            [
                'a1' => 'Contact',
                'old-form' => 'old-form',
            ],
            $field['options'],
        );
        $this->assertTrue($field['eval']['includeBlankOption']);
        $this->assertTrue($field['eval']['chosen']);
    }

    public function testKeepsTheTextFieldWithoutAToken(): void
    {
        $this->invokeListener(
            'edit',
            [
                'dd_hubspot' => true,
            ],
            null,
        );

        $this->assertArrayNotHasKey('TL_DCA', $GLOBALS);
    }

    public function testKeepsTheTextFieldIfHubSpotIsDisabled(): void
    {
        $this->invokeListener(
            'edit',
            [
                'dd_hubspot' => false,
            ],
            'pat-token',
        );

        $this->assertArrayNotHasKey('TL_DCA', $GLOBALS);
    }

    public function testDoesNothingOutsideTheEditView(): void
    {
        $this->invokeListener(
            'delete',
            [
                'dd_hubspot' => true,
            ],
            'pat-token',
        );

        $this->assertArrayNotHasKey('TL_DCA', $GLOBALS);
    }

    private function invokeListener(string $act, array $record, string|null $accessToken): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{"results":[{"id":"a1","name":"Contact"}]}'));
        $formListLoader = new FormListLoader($httpClient, $this->createStub(LoggerInterface::class), $accessToken);

        $requestStack = new RequestStack([
            new Request([
                'act' => $act,
            ])]);

        $dataContainer = $this->createStub(DataContainer::class);
        $dataContainer
            ->method('getCurrentRecord')
            ->willReturn($record)
        ;

        new HubSpotFormIdListener($formListLoader, $requestStack)($dataContainer);
    }
}
