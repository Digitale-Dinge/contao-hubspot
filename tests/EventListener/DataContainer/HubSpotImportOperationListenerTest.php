<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\DataContainer;
use DigitaleDinge\HubSpotBundle\EventListener\DataContainer\HubSpotImportOperationListener;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class HubSpotImportOperationListenerTest extends TestCase
{
    public function testShowsTheFormOperationIfTheFormSendsToHubSpot(): void
    {
        $operation = $this->createMock(DataContainerOperation::class);
        $operation
            ->method('getRecord')
            ->willReturn([
                'id' => 5,
                'dd_hubspot' => 1,
            ])
        ;

        $operation
            ->expects($this->never())
            ->method('hide')
        ;

        $this->createListener()->onFormOperation($operation);
    }

    public function testHidesTheFormOperationIfTheFormDoesNotSendToHubSpot(): void
    {
        $operation = $this->createMock(DataContainerOperation::class);
        $operation
            ->method('getRecord')
            ->willReturn([
                'id' => 5,
                'dd_hubspot' => 0,
            ])
        ;

        $operation
            ->expects($this->once())
            ->method('hide')
        ;

        $this->createListener()->onFormOperation($operation);
    }

    public function testLinksTheFieldListOperationToTheImportOfTheForm(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT dd_hubspot FROM tl_form WHERE id = ?', [5])
            ->willReturn(1)
        ;

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                'dd_hubspot_import',
                [
                    'id' => 5,
                    'from' => 'tl_form_field',
                ],
            )
            ->willReturn('/contao/hubspot/import/5?from=tl_form_field')
        ;

        $operation = $this->createGlobalOperation();
        $operation
            ->expects($this->once())
            ->method('setUrl')
            ->with('/contao/hubspot/import/5?from=tl_form_field')
        ;

        $operation
            ->expects($this->never())
            ->method('hide')
        ;

        new HubSpotImportOperationListener($connection, $urlGenerator)->onFormFieldGlobalOperation($operation);
    }

    public function testHidesTheFieldListOperationIfTheFormDoesNotSendToHubSpot(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection
            ->method('fetchOne')
            ->willReturn(0)
        ;

        $operation = $this->createGlobalOperation();
        $operation
            ->expects($this->once())
            ->method('hide')
        ;

        $operation
            ->expects($this->never())
            ->method('setUrl')
        ;

        new HubSpotImportOperationListener($connection, $this->createStub(UrlGeneratorInterface::class))->onFormFieldGlobalOperation($operation);
    }

    private function createListener(): HubSpotImportOperationListener
    {
        return new HubSpotImportOperationListener($this->createStub(Connection::class), $this->createStub(UrlGeneratorInterface::class));
    }

    private function createGlobalOperation(): DataContainerOperation&MockObject
    {
        $dataContainer = $this->createStub(DataContainer::class);
        $dataContainer
            ->method('__get')
            ->willReturnMap([['id', 5]])
        ;

        $operation = $this->createMock(DataContainerOperation::class);
        $operation
            ->method('getDataContainer')
            ->willReturn($dataContainer)
        ;

        return $operation;
    }
}
