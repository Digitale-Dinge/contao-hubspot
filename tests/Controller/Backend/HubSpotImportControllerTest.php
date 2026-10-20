<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\Controller\Backend;

use DigitaleDinge\HubSpotBundle\Controller\Backend\HubSpotImportController;
use DigitaleDinge\HubSpotBundle\Import\FormDefinition;
use DigitaleDinge\HubSpotBundle\Import\FormDefinitionLoader;
use DigitaleDinge\HubSpotBundle\Import\FormFieldImporter;
use DigitaleDinge\HubSpotBundle\Import\ImportField;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class HubSpotImportControllerTest extends TestCase
{
    private const array FORM = [
        'id' => 5,
        'title' => 'Contact',
        'dd_hubspot_portal_id' => '12345',
        'dd_hubspot_form_id' => 'a1b2c3d4',
    ];

    private string $view = '';

    /**
     * @var array<mixed>
     */
    private array $parameters = [];

    public function testShowsAPreviewOfTheFields(): void
    {
        $definition = new FormDefinition([new ImportField('submit', label: 'Send')], []);

        $response = $this->createController($definition)(5, Request::create('/contao/hubspot/import/5'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('@Contao/backend/hubspot_import.html.twig', $this->view);
        $this->assertSame(self::FORM, $this->parameters['form']);
        $this->assertSame($definition, $this->parameters['definition']);
        $this->assertTrue($this->parameters['has_fields']);
        $this->assertSame('/contao?do=form&table=tl_form_field&id=5', $this->parameters['fields_url']);
    }

    public function testLinksBackToTheFormList(): void
    {
        $this->createController(new FormDefinition([], []))(5, Request::create('/contao/hubspot/import/5'));

        $this->assertSame('/contao?do=form', $this->parameters['back_url']);
    }

    public function testLinksBackToTheFieldList(): void
    {
        $this->createController(new FormDefinition([], []))(5, Request::create('/contao/hubspot/import/5?from=tl_form_field'));

        $this->assertSame('/contao?do=form&table=tl_form_field&id=5', $this->parameters['back_url']);
    }

    public function testImportsTheFieldsOnSubmit(): void
    {
        $fields = [new ImportField('submit', label: 'Send')];

        $importer = $this->createMock(FormFieldImporter::class);
        $importer
            ->expects($this->once())
            ->method('import')
            ->with(5, $fields)
        ;

        $response = $this->createController(new FormDefinition($fields, []), $importer)(5, Request::create('/contao/hubspot/import/5', 'POST'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/contao?do=form&table=tl_form_field&id=5', $response->getTargetUrl());
    }

    public function testDoesNotImportIfTheFormCannotBeLoaded(): void
    {
        $importer = $this->createMock(FormFieldImporter::class);
        $importer
            ->expects($this->never())
            ->method('import')
        ;

        $this->createController(null, $importer)(5, Request::create('/contao/hubspot/import/5', 'POST'));

        $this->assertNull($this->parameters['definition']);
    }

    public function testThrowsIfTheFormDoesNotExist(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection
            ->method('fetchAssociative')
            ->willReturn(false)
        ;

        $this->expectException(NotFoundHttpException::class);

        $this->createController(null, connection: $connection)(5, Request::create('/contao/hubspot/import/5'));
    }

    public function testDeniesAccessWithoutPermission(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->createController(null, granted: false)(5, Request::create('/contao/hubspot/import/5'));
    }

    private function createController(FormDefinition|null $definition, FormFieldImporter|null $importer = null, Connection|null $connection = null, bool $granted = true): HubSpotImportController
    {
        if (!$connection instanceof Connection) {
            $connection = $this->createStub(Connection::class);
            $connection
                ->method('fetchAssociative')
                ->willReturn(self::FORM)
            ;

            $connection
                ->method('fetchOne')
                ->willReturn(3)
            ;
        }

        $loader = $this->createStub(FormDefinitionLoader::class);
        $loader
            ->method('load')
            ->willReturn($definition)
        ;

        $authorizationChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willReturn($granted)
        ;

        $router = $this->createStub(UrlGeneratorInterface::class);
        $router
            ->method('generate')
            ->willReturnCallback(static fn (string $name, array $parameters): string => '/contao?'.http_build_query($parameters))
        ;

        $container = new Container();
        $container->set('security.authorization_checker', $authorizationChecker);
        $container->set('router', $router);

        $onRender = function (string $view, array $parameters): void {
            $this->view = $view;
            $this->parameters = $parameters;
        };

        $controller = new class($connection, $loader, $importer ?? $this->createStub(FormFieldImporter::class), $onRender) extends HubSpotImportController {
            public function __construct(
                Connection $connection,
                FormDefinitionLoader $loader,
                FormFieldImporter $importer,
                private readonly \Closure $onRender,
            ) {
                parent::__construct($connection, $loader, $importer);
            }

            protected function render(string $view, array $parameters = [], Response|null $response = null, bool|null $includeChromeContext = null): Response
            {
                ($this->onRender)($view, $parameters);

                return new Response();
            }
        };

        $controller->setContainer($container);

        return $controller;
    }
}
