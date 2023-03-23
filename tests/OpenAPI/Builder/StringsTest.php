<?php

declare(strict_types=1);

namespace OpenAPI\Builder;

use cebe\openapi\spec\Schema;
use Membrane\OpenAPI\Builder\APIBuilder;
use Membrane\OpenAPI\Builder\Strings;
use Membrane\OpenAPI\Processor\AnyOf;
use Membrane\OpenAPI\Specification;
use Membrane\Processor;
use Membrane\Processor\Field;
use Membrane\Validator\Collection\Contained;
use Membrane\Validator\String\DateString;
use Membrane\Validator\String\Length;
use Membrane\Validator\String\Regex;
use Membrane\Validator\Type\IsNull;
use Membrane\Validator\Type\IsString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Strings::class)]
#[CoversClass(APIBuilder::class)]
#[UsesClass(AnyOf::class)]
#[UsesClass(Specification\Strings::class)]
#[UsesClass(Field::class)]
#[UsesClass(Contained::class)]
#[UsesClass(DateString::class)]
#[UsesClass(Length::class)]
#[UsesClass(Regex::class)]
class StringsTest extends TestCase
{
    #[Test]
    public function supportsNumericSpecification(): void
    {
        $specification = self::createStub(Specification\Strings::class);
        $sut = new Strings();

        self::assertTrue($sut->supports($specification));
    }

    #[Test]
    public function doesNotSupportNonNumericSpecification(): void
    {
        $specification = self::createStub(\Membrane\Builder\Specification::class);
        $sut = new Strings();

        self::assertFalse($sut->supports($specification));
    }

    public static function specificationsToBuild(): array
    {
        return [
            'minimum input' => [
                new Specification\Strings('', new Schema(['type' => 'string'])),
                new Field('', new IsString()),
            ],
            'date input' => [
                new Specification\Strings('', new Schema(['type' => 'string', 'format' => 'date',])),
                new Field('', new IsString(), new DateString('Y-m-d')),
            ],
            'date-time input' => [
                new Specification\Strings('', new Schema(['type' => 'string', 'format' => 'date-time',])),
                new Field('', new IsString(), new DateString('Y-m-d\TH:i:sP')),
            ],
            'detailed input' => [
                new Specification\Strings(
                    '',
                    new Schema(
                        [
                            'type' => 'string',
                            'maxLength' => 100,
                            'minLength' => 0,
                            'pattern' => '#.+',
                            'format' => 'date',
                            'enum' => ['1970/01/01', null],
                            'nullable' => true,
                        ]
                    )
                ),
                new AnyOf(
                    '',
                    new Field('', new IsNull()),
                    new Field(
                        '',
                        new IsString(),
                        new Contained(['1970/01/01', null]),
                        new DateString('Y-m-d'),
                        new Length(0, 100),
                        new Regex('#\#.+#u')
                    )
                ),
            ],
        ];
    }

    #[DataProvider('specificationsToBuild')]
    #[Test]
    public function buildTest(Specification\Strings $specification, Processor $expected): void
    {
        $sut = new Strings();

        $actual = $sut->build($specification);

        self::assertEquals($expected, $actual);
    }
}
