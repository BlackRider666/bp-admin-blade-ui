<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Exceptions\InvalidDefinitionException;
use BlackParadise\CoreAdmin\Domain\ValueObjects\EntityCapabilities;
use BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views\Concerns\RendersUiPages;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\FakeAuthorization;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\UiStateDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use stdClass;

/** Regression pin: unknown / non-ActionContract action classes fail loudly at render, naming the class. */
final class UnknownActionClassRenderTest extends TestCase
{
    use RendersUiPages;

    private const MISSING = 'App\\Actions\\DoesNotExistAction';

    private function definitionWith(string $class): UiStateDefinition
    {
        return new UiStateDefinition(EntityCapabilities::all(), [$class]);
    }

    public function test_index_with_nonexistent_action_class_throws_invalid_definition_naming_class(): void
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage(self::MISSING);

        $this->renderIndex($this->definitionWith(self::MISSING), new FakeAuthorization(['*']));
    }

    public function test_show_with_nonexistent_action_class_throws_invalid_definition_naming_class(): void
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage(self::MISSING);

        $this->renderShow($this->definitionWith(self::MISSING), new FakeAuthorization(['*']));
    }

    public function test_index_with_non_action_contract_class_throws_invalid_definition_naming_class(): void
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage(stdClass::class);

        $this->renderIndex($this->definitionWith(stdClass::class), new FakeAuthorization(['*']));
    }

    public function test_unresolvable_action_error_names_the_entity_definition(): void
    {
        $definition = $this->definitionWith(self::MISSING);

        try {
            $this->renderIndex($definition, new FakeAuthorization(['*']));
            self::fail('Expected InvalidDefinitionException');
        } catch (InvalidDefinitionException $e) {
            self::assertStringContainsString(self::MISSING, $e->getMessage());
            self::assertStringContainsString($definition->name(), $e->getMessage());
        }
    }

    public function test_rethrown_error_preserves_code_and_previous(): void
    {
        $original = new InvalidDefinitionException('boom', 7);
        $resolver = new class ($original) implements \BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionResolverContract {
            public function __construct(private readonly InvalidDefinitionException $e) {}

            public function resolve(string $actionClass): \BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionContract
            {
                throw $this->e;
            }
        };

        try {
            \BlackParadise\LaravelAdminBladeUI\Support\EntityUiState::for($this->definitionWith(self::MISSING), new FakeAuthorization(['*']), $resolver);
            self::fail('Expected InvalidDefinitionException');
        } catch (InvalidDefinitionException $e) {
            self::assertSame(7, $e->getCode());
            self::assertSame($original, $e->getPrevious());
        }
    }
}
